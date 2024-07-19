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
 * Defines form to add a new product in catalog
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Tax;

/**
 * an abstrace class for common cataogitems features to edit
 */
abstract class CatalogItem_Form extends moodleform {

    /** @var array of options for file managers */
    public $editoroptions;

    /** @var Attributes for several widgets. */
    protected $defaultattributes;

    /** @var Attributes for several widgets with short content. */
    protected $attributesshort;

    /** @var Attributes for several widgets with long content. */
    protected $attributeslong;

    /** @var Attributes for filepickers. */
    protected $fpickerattributes;

    /**
     * Constructor
     * @param string $action
     * @param array $data
     */
    public function __construct($action, $data) {
        global $COURSE;

        $maxfiles = 99;                // TODO: add some settings.
        $maxbytes = $COURSE->maxbytes; // TODO: add some settings.
        $context = context_system::instance();
        $this->editoroptions = [
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes,
            'context' => $context,
        ];

        $this->defaultattributes = 'size="50" maxlength="200"';
        $this->attributesshort = 'size="24" maxlength="32"';
        $this->attributeslong = 'size="80" maxlength="255"';
        $this->fpickerattributes = ['maxbytes' => $COURSE->maxbytes, 'accepted_types' => ['.jpg', '.gif', '.png']];
        $this->attributesdescription = 'cols="50" rows="8"';
        parent::__construct($action, $data);
    }

    /**
     * Add all name elements
     */
    protected function add_standard_name_elements() {
        global $DB;

        $config = get_config('local_shop');

        $mform = $this->_form;

        $mform->addElement('hidden', 'catalogid');
        $mform->setType('catalogid', PARAM_INT);

        if (!$this->is_slave()) {
            $mform->addElement('text', 'code', get_string('code', 'local_shop'), $this->attributesshort);
            $mform->setType('code', PARAM_ALPHANUMEXT);
            $mform->addRule('code', null, 'required');
        } else {
            $mform->addElement('static', 'codeshadow', get_string('code', 'local_shop'));
            $mform->addElement('hidden', 'code');
            $mform->setType('code', PARAM_ALPHANUMEXT);
        }

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_shop'), $this->attributeslong);
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $this->attributeslong);
        $mform->setType('name', PARAM_CLEANHTML);
        $mform->addRule('name', null, 'required');

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

        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->editoroptions);
        $mform->setType('description_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');

        if (!empty($config->multipleowners)) {
            $fields = 'hasaccount,firstname,lastname';
            $potentialowners = $DB->get_records_select('local_shop_customer', " hasaccount > 0 ", [], $fields);

            $ownersmenu = ['' => get_string('sitelevel', 'local_shop')];
            if ($potentialowners) {
                foreach ($potentialowners as $accountid => $owner) {
                    $ownersmenu[$accountid] = $owner->lastname.' '.$owner->firstname;
                }
            }

            $mform->addElement('select', 'userid', get_string('productowner', 'local_shop'), $ownersmenu);
            $mform->setType('userid', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'userid', 0);
            $mform->setType('userid', PARAM_INT);
        }

        $statusopts = shop_get_status();
        $mform->addElement('select', 'status', get_string('status', 'local_shop'), $statusopts);
        $mform->setType('status', PARAM_TEXT);
    }

    /**
     * Add pricing elements
     */
    protected function add_price_group() {

        $attributesprice1 = ['size' => 7, 'maxlength' => 10, 'onchange' => 'updatetiprice(1)'];

        $mform = $this->_form;

        $pricegroup = [];
        $price1 = &$mform->createElement('text', 'price1', '', $attributesprice1);
        $price1->updateAttributes(['onchange' => 'updatetiprice(1)']);
        $pricegroup[] = $price1;
        $mform->setType('price1', PARAM_NUMBER);
        $from1 = &$mform->createElement('text', 'from1', '');
        $from1->updateAttributes(['disabled' => 'disabled', 'value' => 0, 'size' => 7]);
        $mform->setType('from1', PARAM_INT);
        $pricegroup[] = $from1;
        $to1 = &$mform->createElement('text', 'range1', '', $attributesprice1);
        $to1->updateAttributes(['onchange' => 'checkprices(1)']);
        $pricegroup[] = $to1;
        $mform->setType('range1', PARAM_INT);
        $ttc1 = &$mform->createElement('static', 'ti1', '', '<span id="id_price1ti">TTC : </span>');
        $pricegroup[] = $ttc1;
        $mform->addGroup($pricegroup, 'priceset1', get_string('unitprice1', 'local_shop'), ' ', false);

        $pricegroup = [];
        $price2 = &$mform->createElement('text', 'price2', '', $attributesprice1);
        $price2->updateAttributes(['onchange' => 'updatetiprice(2)']);
        $pricegroup[] = $price2;
        $mform->setType('price2', PARAM_NUMBER);
        $from2 = $mform->createElement('text', 'from2', '');
        $mform->setType('from2', PARAM_INT);
        $from2->updateAttributes(['disabled' => 'disabled', 'size' => 7]);
        $pricegroup[] = $from2;
        $to2 = &$mform->createElement('text', 'range2', '', $attributesprice1);
        $to2->updateAttributes(['onchange' => 'checkprices(2)']);
        $pricegroup[] = $to2;
        $mform->setType('range2', PARAM_INT);
        $ttc2 = &$mform->createElement('static', 'ti2', '', '<span id="id_price2ti">TTC : </span>');
        $pricegroup[] = $ttc2;
        $mform->addGroup($pricegroup, 'priceset2', get_string('unitprice2', 'local_shop'), ' ', false);

        $pricegroup = [];
        $price3 = &$mform->createElement('text', 'price3', '', $attributesprice1);
        $price3->updateAttributes(['onchange' => 'updatetiprice(3)']);
        $pricegroup[] = $price3;
        $mform->setType('price3', PARAM_NUMBER);
        $from3 = &$mform->createElement('text', 'from3', '');
        $mform->setType('from3', PARAM_INT);
        $from3->updateAttributes(['disabled' => 'disabled', 'size' => 7]);
        $pricegroup[] = $from3;
        $to3 = &$mform->createElement('text', 'range3', '', $attributesprice1);
        $to3->updateAttributes(['onchange' => 'checkprices(3)']);
        $pricegroup[] = $to3;
        $mform->setType('range3', PARAM_INT);
        $ttc3 = &$mform->createElement('static', 'ti3', '', '<span id="id_price3ti">TTC : </span>');
        $pricegroup[] = $ttc3;
        $mform->addGroup($pricegroup, 'priceset3', get_string('unitprice3', 'local_shop'), ' ', false);

        $pricegroup = [];
        $price4 = &$mform->createElement('text', 'price4', '', $attributesprice1);
        $price4->updateAttributes(['onchange' => 'updatetiprice(4)']);
        $pricegroup[] = $price4;
        $mform->setType('price4', PARAM_NUMBER);
        $from4 = &$mform->createElement('text', 'from4', '');
        $mform->setType('from4', PARAM_INT);
        $from4->updateAttributes(['disabled' => 'disabled', 'size' => 7]);
        $pricegroup[] = $from4;
        $to4 = &$mform->createElement('text', 'range4', '', $attributesprice1);
        $to4->updateAttributes(['onchange' => 'checkprices(4)']);
        $pricegroup[] = $to4;
        $mform->setType('range4', PARAM_INT);
        $ttc4 = &$mform->createElement('static', 'ti4', '', '<span id="id_price4ti">TTC : </span>');
        $pricegroup[] = $ttc4;
        $mform->addGroup($pricegroup, 'priceset4', get_string('unitprice4', 'local_shop'), ' ', false);

        $pricegroup = [];
        $price5 = &$mform->createElement('text', 'price5', '', $attributesprice1);
        $price5->updateAttributes(['onchange' => 'updatetiprice(5)']);
        $pricegroup[] = $price5;
        $mform->setType('price5', PARAM_NUMBER);
        $from5 = &$mform->createElement('text', 'from5', '');
        $from5->updateAttributes(['disabled' => 'disabled', 'size' => 7]);
        $mform->setType('from5', PARAM_INT);
        $pricegroup[] = $from5;
        $to5 = &$mform->createElement('static', 'range5', '', '');
        $pricegroup[] = $to5;
        $mform->setType('range5', PARAM_INT);
        $ttc5 = &$mform->createElement('static', 'ti5', '', '<span id="id_price5ti">TTC : </span>');
        $pricegroup[] = $ttc5;
        $mform->addGroup($pricegroup, 'priceset5', get_string('unitprice5', 'local_shop'), ' ', false);
    }

    /**
     * Add Tax selector
     */
    protected function add_tax_select() {

        $mform = $this->_form;

        $taxcodeopts = Tax::get_instances_menu([], 'title');
        $label = get_string('taxcode', 'local_shop');
        $mform->addElement('select', 'taxcode', $label, $taxcodeopts, ['onchange' => 'updatetiprice(1)']);
        $mform->setDefault('taxcode', null);
        $mform->setType('taxcode', PARAM_INT);
        $mform->addHelpButton('taxcode', 'taxhelp', 'local_shop');
        $mform->addRule('taxcode', null, 'required');
    }

    /**
     * ADD sales conditions
     */
    protected function add_sales_params() {

        $mform = $this->_form;

        if (!$this->is_slave()) {
            $mform->addElement('text', 'stock', get_string('stock', 'local_shop'), $this->attributesshort);
            $mform->setType('stock', PARAM_NUMBER);

            $mform->addElement('text', 'sold', get_string('sold', 'local_shop'), $this->attributesshort);
            $mform->setType('sold', PARAM_NUMBER);

            $maxquantopts = [
                '0' => get_string('unlimited'),
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '10' => '10',
                '20' => '20',
                '50' => '50',
            ];
            $label = get_string('maxdeliveryquant', 'local_shop');
            $mform->addElement('select', 'maxdeliveryquant', $label, $maxquantopts);
            $mform->setType('maxdeliveryquant', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'stock');
            $mform->setType('stock', PARAM_NUMBER);
            $mform->addElement('hidden', 'sold');
            $mform->setType('sold', PARAM_NUMBER);
            $mform->addElement('hidden', 'maxdeliveryquant');
            $mform->setType('maxdeliveryquant', PARAM_INT);
        }
    }

    /**
     * Add target market
     */
    protected function add_target_market() {

        $mform = $this->_form;

        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('customer', 'local_shop'), PROVIDING_CUSTOMER_ONLY);
        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedin', 'local_shop'), PROVIDING_LOGGEDIN_ONLY);
        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('both', 'local_shop'), PROVIDING_BOTH);
        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedout', 'local_shop'), PROVIDING_LOGGEDOUT_ONLY);
        $mform->addGroup($radiogroup, 'loggedingroup', get_string('onlyfor', 'local_shop'), [' '], false);
        $mform->setDefault('onlyforloggedin', 0);

        $label = get_string('productpassword', 'local_shop').':';
        $mform->addelement('text', 'password', $label, '', ['size' => 8, 'maxlength' => 8]);
        $mform->setType('password', PARAM_TEXT);
    }

    /**
     * Add common documents
     */
    protected function add_document_assets() {
        global $COURSE;

        $imgfpickerattributes = ['maxbytes' => $COURSE->maxbytes, 'accepted_types' => ['.jpg', '.gif', '.png']];
        $docfpickerattributes = ['maxbytes' => $COURSE->maxbytes, 'accepted_types' => ['.pdf']];

        $mform = $this->_form;

        $group = [];
        $label = get_string('leaflet', 'local_shop');
        $group[0] = & $mform->createElement('filepicker', 'leaflet', $label, $docfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearleaflet', get_string('clear', 'local_shop'));

        $label = get_string('leaflet', 'local_shop');
        $mform->addGroup($group, 'grleaflet', $label, [get_string('clear', 'local_shop').'&nbsp;:&nbsp;'], ' ', false);

        $group = [];
        $label = get_string('image', 'local_shop');
        $group[0] = & $mform->createElement('filepicker', 'image', $label, $imgfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearimage', get_string('clear', 'local_shop'));

        $label = get_string('image', 'local_shop');
        $mform->addGroup($group, 'grimage', $label, [get_string('clear', 'local_shop').'&nbsp;:&nbsp;'], ' ', false);

        $group = [];
        $label = get_string('thumbnail', 'local_shop');
        $group[0] = & $mform->createElement('filepicker', 'thumb', $label, $imgfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearthumb', get_string('clear', 'local_shop'));

        $label = get_string('thumbnail', 'local_shop');
        $mform->addGroup($group, 'grthumb', $label, [get_string('clear', 'local_shop').'&nbsp;:&nbsp;'], ' ', false);

        $group = [];
        $label = get_string('unitpix', 'local_shop');
        $group[0] = & $mform->createElement('filepicker', 'unit', $label, $imgfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearunit', get_string('clear', 'local_shop'));

        $label = get_string('unitpix', 'local_shop');
        $mform->addGroup($group, 'grunit', $label, [get_string('clear', 'local_shop').'&nbsp;:&nbsp;'], ' ', false);

        $group = [];
        $label = get_string('tenunitspix', 'local_shop');
        $group[0] = & $mform->createElement('filepicker', 'tenunits', $label, $imgfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'cleartenunits', get_string('clear', 'local_shop'));

        $label = get_string('tenunitspix', 'local_shop');
        $mform->addGroup($group, 'grtenunits', $label, [get_string('clear', 'local_shop').'&nbsp;:&nbsp;'], ' ', false);

        $label = get_string('eula', 'local_shop').':';
        $mform->addElement('editor', 'eula_editor', $label, null, $this->editoroptions);
        $mform->setType('eula', PARAM_URL);
        $mform->addHelpButton('eula_editor', 'producteulas', 'local_shop');

        $label = get_string('notes', 'local_shop').':';
        $mform->addElement('editor', 'notes_editor', $label, null, $this->editoroptions);
        $mform->setType('notes_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('notes_editor', 'description', 'local_shop');
    }

    /**
     * Part of set data for name elements
     * @param arrayref &$defaults
     * @param context $context
     */
    protected function set_name_data(&$defaults, $context) {
        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'catalogitemdescription', @$defaults->itemid);
    }

    /**
     * Part of set data for documents
     * @param arrayref &$defaults
     * @param context $context
     * @todo better factorize the options.
     */
    protected function set_document_asset_data(&$defaults, $context) {
        global $COURSE;

        $fileoptions = ['subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1];

        $draftitemid = file_get_submitted_draft_itemid('grleaflet[leaflet]');
        file_prepare_draft_area($draftitemid, $context->id, 'local_shop', 'catalogitemleaflet', @$defaults->itemid,
                                $fileoptions);
        $defaults->grleaflet = ['leaflet' => $draftitemid ];

        $draftitemid = file_get_submitted_draft_itemid('grimage[image]');
        file_prepare_draft_area($draftitemid, $context->id, 'local_shop', 'catalogitemimage', @$defaults->itemid,
                                $fileoptions);
        $defaults->grimage = ['image' => $draftitemid];

        $draftitemid = file_get_submitted_draft_itemid('grthumb[thumb]');
        file_prepare_draft_area($draftitemid, $context->id, 'local_shop', 'catalogitemthumb', @$defaults->itemid,
                                $fileoptions);
        $defaults->grthumb = ['thumb' => $draftitemid];
        $draftitemid = file_get_submitted_draft_itemid('grunit[unit]');
        file_prepare_draft_area($draftitemid, $context->id, 'local_shop', 'catalogitemunit', @$defaults->itemid,
                                $fileoptions);
        $defaults->grunit = ['unit' => $draftitemid];

        $draftitemid = file_get_submitted_draft_itemid('grtenunits[tenunits]');
        file_prepare_draft_area($draftitemid, $context->id, 'local_shop', 'catalogitemtenunits', @$defaults->itemid,
                                $fileoptions);
        $defaults->grtenunits = ['tenunits' => $draftitemid];

        $defaults = file_prepare_standard_editor($defaults, 'eula', $this->editoroptions, $context, 'local_shop',
                                                 'catalogitemeula', @$defaults->itemid);

        $defaults = file_prepare_standard_editor($defaults, 'notes', $this->editoroptions, $context, 'local_shop',
                                                 'catalogitemnotes', @$defaults->itemid);
    }

    /**
     * Common feeder.
     * @param array $defaults
     */
    public function set_data($defaults) {
        parent::set_data($defaults);
    }

    /**
     * Is cancelled
     */
    public function is_cancelled() {
        parent::is_cancelled();
    }

    /**
     * Add category selector element
     */
    public function add_category() {

        $mform = $this->_form;

        if (!$this->is_slave()) {
            if ($cats = $this->_customdata['catalog']->get_categories(true, false)) {
                foreach ($cats as $cat) {
                    $sectionopts[$cat->id] = format_string($cat->name);
                }
                $mform->addElement('select', 'categoryid', get_string('section', 'local_shop'), $sectionopts);
                $mform->setType('categoryid', PARAM_INT);
                $mform->addRule('categoryid', null, 'required');
            } else {
                $mform->addElement('static', 'nocats', get_string('nocats', 'local_shop'));
            }
        } else {
            $mform->addElement('static', 'categoryidshadow', get_string('section', 'local_shop'));
            $mform->addElement('hidden', 'categoryid');
            $mform->setType('categoryid', PARAM_INT);
        }
    }

    /**
     * Check if we are in a slave catalog (pro)
     */
    protected function is_slave() {
        return $this->_customdata['catalog']->isslave;
    }

    /**
     * Standard validation
     * @param array $data
     * @param array $files attached files
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validation($data, $files = []) {
        global $DB;

        $errors = [];

        if (!empty($data['id'])) {
            // Same idnumber somewhere.
            if (!empty($data['idenumber'])) {
                $select = ' idnumnber = ? AND id != ? ';
                if ($DB->record_exists_select('local_shop_catalogitem', $select, [$data['idnumber'], $data['id']])) {
                    // Some idnumber in the way. Should be unique in all moodle.
                    $errors['idnumber'] = get_string('erroridnumberexists', 'local_shop');
                }
            }

            // Same product code in the same catalog.
            $select = ' code = ? AND id != ? AND catalogid = ? ';
            if ($DB->record_exists_select('local_shop_catalogitem', $select, [$data['code'], $data['id']])) {
                // Some product code in the way. Should be unique in the same catalog.
                $errors['code'] = get_string('errorcodeexists', 'local_shop');
            }
        }
    }
}
