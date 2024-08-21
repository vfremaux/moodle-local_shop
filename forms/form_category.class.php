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
 * A form to edit catalog categories
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * A form to edit categories
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
class Category_Form extends moodleform {

    /** @var array of options for file managers */
    public $editoroptions;

    /** @var array of default attributes */
    protected $defaultattributes;

    /** @var array of default attributes for short data fields */
    protected $attributesshort;

    /** @var array of default attributes for long data fields */
    protected $attributeslong;

    /**
     * Constructor
     * @param string $action
     * @param array $data
     */
    public function __construct($action, $data) {
        $this->defaultattributes = 'size="50" maxlength="200"';
        $this->attributesshort = 'size="24" maxlength="32"';
        $this->attributeslong = 'size="80" maxlength="255"';
        parent::__construct($action, $data);
    }

    /**
     * Standard Definition
     */
    public function definition() {
        global $COURSE, $OUTPUT;

        $attributes = ['size' => 47,  'maxlength' => 200];

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some settings.
        $maxbytes = $COURSE->maxbytes; // TODO: add some settings.
        $this->editoroptions = [
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes,
            'context' => $context,
        ];

        $mform =& $this->_form;

        $mform->addElement('hidden', 'catalogid');
        $mform->setType('catalogid', PARAM_INT);

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'category', 'local_shop')));

        $mform->addElement('text', 'name', get_string('name'), '', $attributes);
        $mform->setType('name', PARAM_TEXT);

        if (!empty($this->_customdata['parents'])) {
            $mform->addElement('select', 'parentid', get_string('parentcategory', 'local_shop'), $this->_customdata['parents']);
        }

        // Title and description.
        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->editoroptions);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');

        if (local_shop_supports_feature('products/smarturls')) {
            $mform->addElement('text', 'seoalias', get_string('seoalias', 'local_shop'), $this->attributeslong);
            $mform->setType('seoalias', PARAM_TEXT);
            $mform->setAdvanced('seoalias', true);
            $mform->addHelpButton('seoalias', 'seoalias', 'local_shop');

            $mform->addElement('text', 'seokeywords', get_string('seokeywords', 'local_shop'), $this->attributeslong);
            $mform->setType('seokeywords', PARAM_TEXT);
            $mform->setAdvanced('seokeywords', true);
            $mform->addHelpButton('seokeywords', 'seokeywords', 'local_shop');

            $mform->addElement('text', 'seotitle', get_string('seotitle', 'local_shop'), $this->attributeslong);
            $mform->setType('seotitle', PARAM_TEXT);
            $mform->setAdvanced('seotitle', true);
            $mform->addHelpButton('seotitle', 'seotitle', 'local_shop');

            $mform->addElement('text', 'seodescription', get_string('seodescription', 'local_shop'), $this->attributeslong);
            $mform->setType('seodescription', PARAM_TEXT);
            $mform->setAdvanced('seodescription', true);
            $mform->addHelpButton('seodescription', 'seodescription', 'local_shop');
        }

        $yesnooptions = ['0' => get_string('no'), '1' => get_string('yes')];
        $mform->addElement('select', 'visible', get_string('visible'), $yesnooptions);
        $mform->setDefault('visible', 1);

        // Informations required.
        $mform->addRule('name', null, 'required');

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    /**
     * Feed form with previous data
     */
    public function set_data($defaults) {

        $context = context_system::instance();

        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'categorydescription', @$defaults->categoryid);

        parent::set_data($defaults);
    }
}
