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
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

use StdClass;

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

/**
 * A shop instance has one or several Catalogs and operates one associated front.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Shop extends ShopObject {

    /** @var DB table (for ShopObject) */
    protected static $table = 'local_shop';

    /** @var An ref to the catalogue */
    public $thecatalogue;

    /** @var An array of navigation paths */
    private $navorder;

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light lightweight object (without categories) if true.
     */
    public function __construct($idorrecord, $light = false) {
        global $DB;

        $config = get_config('local_shop');

        parent::__construct($idorrecord, self::$table);

        self::expand_paymodes($this->record);

        if ($idorrecord) {

            if ($light) {
                // This builds a lightweight proxy of the Shop, without catalogue.
                return;
            }

            if (!empty($this->catalogid)) {
                $this->thecatalogue = new Catalog($this->catalogid);
            }

            if (empty($this->record->navsteps)) {
                 $this->record->navsteps = $config->defaultnavsteps;
            }

            $this->_build_nav_order();
        } else {
            $lastordering = $DB->get_field(self::$table, 'MAX(sortorder)', []);
            $lastordering++;

            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->sortorder = $lastordering;
            $this->record->name = get_string('newshopinstance', 'local_shop');
            $this->record->description = '';
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->currency = $config->defaultcurrency;
            $this->record->catalogid = 0;
            $this->record->customerorganisationrequired = 0;
            $this->record->enduserorganisationrequired = 0;
            $this->record->endusermobilephonerequired = 0;
            $this->record->printtabbedcategories = 1;
            $this->record->defaultcustomersupportcourse = 1;
            $this->record->forcedownloadleaflet = 1;
            $this->record->allowtax = 1;
            $this->record->discountthreshold = $config->discountthreshold;
            $this->record->discountrate = $config->discountrate;
            $this->record->discountrate2 = $config->discountrate2;
            $this->record->discountrate3 = $config->discountrate3;
            $this->record->eula = '';
            $this->record->eulaformat = FORMAT_HTML;
            $this->record->navsteps = $config->defaultnavsteps;
            $this->_build_nav_order();
        }
    }

    /**
     * Get the starting step of the purchase process.
     */
    public function get_starting_step() {
        return array_keys($this->navorder['nextstep'])[0];
    }

    /**
     * Get the next step.
     * @param string $step
     */
    public function get_next_step($step) {
        return $this->navorder['nextstep'][$step];
    }

    /**
     * Get the previous step.
     * @param string $step
     */
    public function get_prev_step($step) {
        return $this->navorder['prevstep'][$step];
    }

    /**
     * Get the catalogue
     */
    public function get_catalogue() {
        if (empty($this->thecatalogue)) {
            // Lazy loading if not preloaded.
            if (!empty($this->record->catalogid)) {
                $this->thecatalogue = new Catalog($this->record->catalogid);
            }
        }
        return $this->thecatalogue;
    }

    /**
     * Get the current currency of the shop instance
     * @param bool $long
     */
    public function get_currency($long = false) {

        if ($long !== false) {
            if ($long == 'symbol') {
                return get_string($this->currency.'symb', 'local_shop');
            }
            // Any other real value.
            return get_string($this->currency, 'local_shop');
        }
        return $this->currency;
    }

    /**
     * Receives a form reponse and compactall paymodes setup into one single
     * field.
     * @param stringref &$shoprec
     */
    public static function compact_paymodes(&$shoprec) {

        $shoparr = (array)$shoprec;
        $keys = array_keys($shoparr);

        $paymodeenablekeys = preg_grep('/^enable/', $keys);

        $paymodeenable = [];
        foreach ($paymodeenablekeys as $k) {
            $paymodekey = str_replace('enable', '', $k);
            $paymodeenable[$paymodekey] = 0 + @$shoparr[$k];
            unset($shoprec->$k);
        }

        $shoprec->paymodes = base64_encode(serialize([$paymodeenable]));
    }

    /**
     * Expands back compacted params for paymodes into separate fields
     * @param objectref &$shoprec expansion is performed directly in input object.
     * @return void
     */
    public static function expand_paymodes(&$shoprec) {

        if (!empty($shoprec->paymodes)) {
            $expanded = unserialize(base64_decode($shoprec->paymodes));;
            $paymodeenable = array_shift($expanded);
        } else {
            $paymodeenable = [];
        }

        foreach ($paymodeenable as $k => $v) {
            $key = 'enable'.$k;
            $shoprec->$key = $v;
        }
    }

    /**
     * Get the access block to this shop instance.
     */
    public function get_blocks() {
        global $DB;

        $bis = $DB->get_records('block_instances', ['blockname' => 'shop_access']);
        $count = 0;
        if ($bis) {
            foreach ($bis as $bi) {
                $config = unserialize(base64_decode($bi->configdata));
                if (@$config->shopinstance == $this->id) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Get all bill records for the shop instance.
     */
    public function get_bills() {
        return Bill::get_instances(['shopid' => $this->id]);
    }

    /**
     * Deleting shop will not necesarily delete the attached catalogue
     * as some other shop instances may use it.
     */
    public function delete(): void {
        global $DB;

        // Delete all attached bills.
        if ($bills = $this->get_bills()) {
            foreach ($bills as $b) {
                $b->delete();
            }
        }

        // Finally delete master record.
        $DB->delete_records(self::$table, ['id' => $this->id]);
    }

    public function url() {
        $shopurl = new moodle_url('/local/shop/front/view.php', ['view' => 'shop', 'id' => $this->id]);
        return $shopurl;
    }

    /**
     * Exports the shop into a YML string.
     * @param int $level 
     */
    public function export($level = 0) {

        $yml = '';

        $yml .= "shop:\n";

        $yml .= parent::export(1);

        $yml = "\n";

        if (!empty($this->thecatalogue)) {
            $yml .= $this->thecatalogue->export();
        }
        return $yml;
    }

    /**
     * Export for Web Services
     */
    public function export_to_ws() {

        $export = new StdClass();

        $export->id = $this->record->id;
        $export->name = format_string($this->record->name);
        $export->catalogid = $this->record->catalogid;
        $export->description = format_text($this->record->description, $this->record->descriptionformat);
        $export->allowtax = $this->record->allowtax;
        $export->eulas = format_text($this->record->eulas, $this->record->eulaformat);
        $export->paymodes = $this->record->paymodes;
        $export->defaultpaymode = $this->record->defaultpaymode;

        return $export;
    }

    /**
     * ShopObject wrapper
     * @see local_shop\ShopObject
     * @param array $filter
     */
    public static function count($filter) {
        return parent::_count(self::$table, $filter);
    }

    /**
     * ShopObject wrapper
     * @see local_shop\ShopObject
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * Builds the navorder from config
     */
    private function _build_nav_order() {
        $this->navorder = [];
        $navsteps = explode(',', $this->navsteps);
        $navnext = explode(',', $this->navsteps);
        $navprev = explode(',', $this->navsteps);
        $first = array_shift($navnext);
        array_push($navnext, $first);
        $last = array_pop($navprev);
        array_unshift($navprev, $last);
        $this->navorder['nextstep'] = array_combine($navsteps, $navnext);
        $this->navorder['prevstep'] = array_combine($navsteps, $navprev);
    }
}
