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
 * Controller for managing shop instances.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use \local_shop\Shop;

class shop_controller {

    protected $data;

    protected $received;

    protected $mform;

    public function receive($cmd, $data = null, $mform = null) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->mform = $mform;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'delete':
                $this->data->shopid = required_param('shopid', PARAM_INT);
                break;
            case 'edit':
                // Edit is fed by the $data attribute.
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $context = \context_system::instance();

        if ($cmd == 'delete') {
            $shop = new Shop($this->data->shopid);
            $shop->delete();
        }

        if ($cmd == 'edit') {
            $shoprec = $this->data;

            Shop::compact_paymodes($shoprec);

            $shoprec->descriptionformat = $shoprec->description_editor['format'];
            $shoprec->description = $shoprec->description_editor['text'];

            $shoprec->eulaformat = $shoprec->eula_editor['format'];
            $shoprec->eula = $shoprec->eula_editor['text'];

            if (empty($shoprec->shopid)) {
                unset($shoprec->id);
                $shoprec->id = $DB->insert_record('local_shop', $shoprec);
            } else {
                $shoprec->id = $shoprec->shopid;
                $DB->update_record('local_shop', $shoprec);
            }

            // Process text fields from editors.
            if ($this->mform) {
                // We do not have form when runing tests.
                $draftideditor = file_get_submitted_draft_itemid('description_editor');
                $shoprec->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'description',
                                                                   $shoprec->id, array('subdirs' => true), $shoprec->description);
                $shoprec = file_postupdate_standard_editor($shoprec, 'description', $this->mform->editoroptions, $context, 'local_shop',
                                                           'description', $shoprec->id);

                $draftideditor = file_get_submitted_draft_itemid('eula_editor');
                $shoprec->eula = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'eula',
                                                            $shoprec->id, array('subdirs' => true), $shoprec->eula);
                $shoprec = file_postupdate_standard_editor($shoprec, 'eula', $this->mform->editoroptions, $context,
                                                           'local_shop', 'eula', $shoprec->id);

                $DB->update_record('local_shop', $shoprec);
            }
            return new Shop($shoprec);
        }
    }

    public static function info() {
        return array('delete' => array('shopid' => 'ID of shop record'),
                     'edit' => array('shopid' => 'ID of the shop when updates',
                                     'name' => 'String',
                                     'description_editor' => 'Array of text,format',
                                     'catalogid' => 'ID of assigned catalog',
                                     'navsteps' => 'String list of steps',
                                     'allowtax' => 'Boolean',
                                     'discountthreshold' => 'Number',
                                     'discountrate' => 'Integer from 0 to 100',
                                     'discountrate2' => 'Integer from 0 to 100',
                                     'discountrate3' => 'Integer from 0 to 100',
                                     'currency' => 'Currency code',
                                     'defaultpaymode' => 'Plugin name as string',
                                     'paymode<paymodename>' => 'Boolean, one per enabled paymode',
                                     'forcedownloadleaflet' => 'Boolean',
                                     'customerorganisationrequired' => 'Boolean',
                                     'enduserorganisationrequired' => 'Boolean',
                                     'endusermobilephonerequired' => 'Boolean',
                                     'printtabbedcategories' => 'Boolean',
                                     'defaultcustomersupportcourse' => 'Numeric ID of customer support course',
                                     'eula_editor' => 'Array of text,format'));
    }
}