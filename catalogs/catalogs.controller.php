<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Controller for catalogs.
 *
 * @package     local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;
use StdClass;
use moodle_url;
use context_system;
use coding_exception;

// Note that other use cases are handled by the edit_catalogue.php script.

/**
 * An MVC controller for catalogs
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class catalog_controller {

    /**
     * @var object Action data
     */
    protected $data;

    /**
     * @var bool Marks data has been received
     */
    protected $received;

    /**
     * @var object May be unused.
     */
    protected $mform;

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     * @param object $mform optionnaly the form that is receiving data.
     * @todo : revise the $mform presence.
     */
    public function receive($cmd, $data = [], $mform = null) {

        $this->mform = $mform;

        if (!empty($data)) {
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new StdClass;
        }

        switch ($cmd) {
            case 'deletecatalog':
                $this->data->catalogid = required_param('catalogid', PARAM_INT);
                break;
            case 'edit':
                // Get all data from $data attribute.
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        if ($cmd == 'deletecatalog') {
            // If master catalog, must delete all slaves.
            $thecatalog = new Catalog($this->data->catalogid);
            if ($thecatalog->ismaster) {
                $slaves = $thecatalog->get_slaves();
                if (!empty($slaves)) {
                    foreach ($slaves as $s) {
                        $s->delete();
                    }
                }
            }
            $thecatalog->delete();

            return new moodle_url('/local/shop/index.php');
        }

        if ($cmd == 'edit') {
            $catalog = $this->data;

            $context = context_system::instance();

            unset($catalog->id); // Shop reference cannot be record id.

            $catalog->descriptionformat = $catalog->description_editor['format'];
            $catalog->description = $catalog->description_editor['text'];
            $catalog->salesconditionsformat = $catalog->salesconditions_editor['format'];
            $catalog->salesconditions = $catalog->salesconditions_editor['text'];
            $catalog->billfooterformat = $catalog->billfooter_editor['format'];
            $catalog->billfooter = $catalog->billfooter_editor['text'];

            if (empty($catalog->catalogid)) {
                // Creating new.
                $catalog->groupid = $this->data->groupid ?? 0; // Ensure we have a value.
                $catalog->id = $DB->insert_record('local_shop_catalog', $catalog);
                if ($catalog->linked == 'master') {
                    // Set reference to ourself. We are the leader of a catalog group.
                    $DB->set_field('local_shop_catalog', 'groupid', $catalog->id, ['id' => $catalog->id]);
                } else if ($catalog->linked == 'slave') {
                    // Set reference to the master in group.
                    $DB->set_field('local_shop_catalog', 'groupid', $catalog->groupid, ['id' => $catalog->id]);
                } else {
                    $DB->set_field('local_shop_catalog', 'groupid', 0, ['id' => $catalog->id]);
                }
            } else {
                // Updating.
                $catalog->id = $catalog->catalogid;

                // We need to release all old slaves if this catalog changes from master to standalone.
                if ($oldcatalog = $DB->get_record('local_shop_catalog', ['id' => $catalog->id])) {
                    if (($oldcatalog->id == $oldcatalog->groupid) && $catalog->linked != 'master') {
                        /*
                         * We are dismitting as master catalog. All slaves should be released.
                         * get all slaves but not me
                         * TODO : may have further side effects, but we'll see later.
                         */
                        $select = "
                            groupid = ? AND
                            groupid != id
                        ";
                        if ($oldslaves = $DB->get_records_select('local_shop_catalog', $select, [$oldcatalog->id])) {
                            foreach ($oldslaves as $oldslave) {
                                $oldslave->groupid = 0;
                                $DB->update_record('local_shop_catalog', $oldslave);
                            }
                        }
                    }
                }
                $DB->update_record('local_shop_catalog', $catalog);

                if ($catalog->linked == 'master') {
                    // Deslave the catalog giving it its own groupid.
                    // TODO : check what happens to product clones in there.
                    $DB->set_field('local_shop_catalog', 'groupid', $catalog->id, ['id' => $catalog->id]);
                    $catalog->groupid = $catalog->id; //Need it for text_editor stage.
                } else if ($catalog->linked == 'free') {
                    $DB->set_field('local_shop_catalog', 'groupid', 0, ['id' => $catalog->id]);
                    $catalog->groupid = 0; //Need it for text_editor stage.
                }
            }

            // Process text fields from editors.
            if ($this->mform) {
                // When playing tests we do not have form.
                $draftideditor = file_get_submitted_draft_itemid('description_editor');
                $catalog->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogdescription',
                                                                $catalog->id, ['subdirs' => true], $catalog->description);
                $catalog = file_postupdate_standard_editor($catalog, 'description', $this->mform->editoroptions,
                                $context, 'local_shop', 'catalogdescription', $catalog->id);

                $draftideditor = file_get_submitted_draft_itemid('salesconditions_editor');
                $catalog->salesconditions = file_save_draft_area_files($draftideditor, $context->id, 'local_shop',
                                                                       'catalogsalesconditions', $catalog->id, ['subdirs' => true],
                                                                       $catalog->salesconditions);

                $draftideditor = file_get_submitted_draft_itemid('billfooter_editor');
                $catalog = file_postupdate_standard_editor($catalog, 'billfooter', $this->mform->editoroptions,
                                $context, 'local_shop', 'catalogbillfooter', $catalog->id);

                $DB->update_record('local_shop_catalog', $catalog);
            }

            return new Catalog($catalog);
        }
    }

    /**
     * Info about use cases. Experimental
     */
    public static function info() {
        return [
            'deletecatalog' => ['catalogid' => 'ID of catalog to delete'],
            'edit' => [
                'catalogid' => 'Numeric ID for update',
                'name' => 'String',
                'description_editor' => 'Array of text,format,itemid',
                'billfooter_editor' => 'Array of text,format,itemid',
                'countryrestrictions' => 'Comma separated list of lowercase country codes',
                'linked' => 'One of \'master|slave|free\'',
                'groupid' => 'Integer ID of another catalog',
            ],
        ];
    }
}
