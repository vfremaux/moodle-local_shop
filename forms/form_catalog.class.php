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
 * Defines form to add or edit a catalog
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Security.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * A form to edit catalog instances
 */
class Catalog_Form extends moodleform {

    /** @var options for text editors */
    public $editoroptions;

    /**
     * Standard definition
     */
    public function definition() {
        global $OUTPUT, $DB, $COURSE;

        $context = context_system::instance();
        $config = get_config('local_shop');

        $maxfiles = 99;                // TODO: add some setting.
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting.
        $this->editoroptions = [
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes,
            'context' => $context,
        ];

        $mform =& $this->_form;

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'catalog', 'local_shop')));

        // Adding fieldset.
        $attributes = 'size="50" maxlength="255"';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'catalogid');
        $mform->setType('catalogid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $attributes);
        $mform->addRule('name', null, 'required');
        $mform->setType('name', PARAM_TEXT);

        $label = get_string('description', 'local_shop');
        $mform->addElement('editor', 'description_editor', $label, '', $this->editoroptions);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');
        $mform->addRule('description_editor', null, 'required');

        $label = get_string('countrycodelist', 'local_shop');
        $mform->addElement('text', 'countryrestrictions', $label, $attributes);
        $mform->addHelpButton('countryrestrictions', 'countryrestrictions', 'local_shop');
        $mform->setType('countryrestrictions', PARAM_TEXT);

        $label = get_string('billfooter', 'local_shop');
        $mform->addElement('editor', 'billfooter_editor', $label, '', $this->editoroptions);

        // Add catalog mode settings.

        if ($config->useslavecatalogs && local_shop_supports_feature('catalog/instances')) {
            $linkedarray = [];
            $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('standalone', 'local_shop'), 'free');

            $sql = "
               SELECT DISTINCT
                  ci.*
               FROM
                  {local_shop_catalog} as ci
               WHERE
                 ci.id = ci.groupid
            ";
            $mastercatalogoptions = [];
            if ($mastercatalogs = $DB->get_records_sql($sql)) {
                foreach ($mastercatalogs as $acat) {
                    $mastercatalogoptions[$acat->id] = $acat->name;
                }
            }

            $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('master', 'local_shop'), 'master');
            if (!empty($mastercatalogoptions)) {
                $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('slaveto', 'local_shop'), 'slave');
                $linkedarray[] = &$mform->createElement('select', 'groupid', '', $mastercatalogoptions);
            }
            $mform->addGroup($linkedarray, 'linkedarray', '', [' '], false);
            $mform->setDefault('linked', 'free');
        } else {
            $mform->addElement('hidden', 'linked', 'free');
            $mform->setType('linked', PARAM_TEXT);
            $mform->addElement('hidden', 'groupid', 0);
            $mform->setType('groupid', PARAM_BOOL);
        }

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    /**
     * Feeds the form with existing data
     * @param array $defaults
     */
    public function set_data($defaults) {

        $context = context_system::instance();

        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'catalogdescription', $defaults->catalogid);

        $defaults = file_prepare_standard_editor($defaults, 'billfooter', $this->editoroptions, $context, 'local_shop',
                                                 'catalogbillfooter', $defaults->catalogid);

        parent::set_data($defaults);
    }
}
