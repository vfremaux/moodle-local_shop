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

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

class Shop extends ShopObject {

    public $thecatalogue;

    /*
     * An array of navigation paths
     */
    private $navorder;

    static $table = 'local_shop';

    // build a full shop instance
    function __construct($idorrecord, $light = false) {
        global $DB;

        $config = get_config('local_shop');

        parent::__construct($idorrecord, self::$table);

        self::expand_paymodes($this->record);

        if ($idorrecord) {

            if ($light) return; // this builds a lightweight proxy of the Shop, without catalogue

            if (!empty($this->catalogid)) {
                $this->thecatalogue = new Catalog($this->catalogid);
            }

            if (empty($this->record->navsteps)) {
                 $this->record->navsteps = $config->defaultnavsteps;
            }

            $this->_build_nav_order();
        } else {
            $lastordering = $DB->get_field(self::$table, 'MAX(sortorder)', array());
            $lastordering++;

            // initiate empty fields
            $this->record->id = 0;
            $this->record->sortorder = $lastordering;
            $this->record->name = get_string('newshopinstance', 'local_shop');
            $this->record->description = '';
            $this->record->descriptionformat = FORMAT_MOODLE;
            $this->record->currency = $config->defaultcurrency;
            $this->record->catalogid = 0;
            $this->record->customerorganisationrequired = 0;
            $this->record->enduserorganisationrequired = 0;
            $this->record->endusermobilephonerequired = 0;
            $this->record->printtabbedcategories = 1;
            $this->record->defaultcustomersupportcourse = 1;
            $this->record->forcedownloadleaflet = 1;
            $this->record->allowtax = 1;
            $this->record->eula = '';
            $this->record->navsteps = $config->defaultnavsteps;
            $this->_build_nav_order();
        }
    }

    function get_starting_step() {
        return array_keys($this->navorder['nextstep'])[0];
    }

    function get_next_step($step) {
        return $this->navorder['nextstep'][$step];
    }

    function get_prev_step($step) {
        return $this->navorder['prevstep'][$step];
    }

    /**
     *
     */
     function get_catalogue() {
        return $this->thecatalogue;
    }

    /**
     * Get the current currency of the shop instance
     */
    function get_currency($long = false) {
        global $CFG;

        if ($long !== false) {
            if ($long == 'symbol') {
                return get_string($this->currency.'symb', 'local_shop');
            }
            // any other real value
            return get_string($this->currency, 'local_shop');
        }
        return $this->currency;
    }

    /**
     * Receives a form reponse and compactall paymodes setup into one single 
     * field.
     */
    static function compact_paymodes(&$shoprec) {

        $shoparr = (array)$shoprec;
        $keys = array_keys($shoparr);

        $paymodeenablekeys = preg_grep('/^enable/', $keys);

        $paymodeenable = array();
        foreach ($paymodeenablekeys as $k) {
            $paymodekey = str_replace('enable', '', $k);
            $paymodeenable[$paymodekey] = 0 + @$shoparr[$k];
            unset($shoprec->$k);
        }

        $shoprec->paymodes = base64_encode(serialize(array($paymodeenable)));
    }

    /**
     * Expands back compacted params for paymodes into separate fields
     */
    static function expand_paymodes(&$shoprec) {
        if (!empty($shoprec->paymodes)) {
            $expanded = unserialize(base64_decode($shoprec->paymodes));;
            $paymodeenable = array_shift($expanded);
        } else {
            $paymodeenable = array();
            $defaultpaymode = '';
        }

        foreach($paymodeenable as $k => $v) {
            $key = 'enable'.$k;
            $shoprec->$key = $v;
        }
    }

    function get_blocks() {
        global $DB;

        $bis = $DB->get_records('block_instances', array('blockname' => 'shop_access'));
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

    function get_bills() {
        return Bill::get_instances(array('shopid' => $this->id));
    }

    function delete() {
        global $DB;

        if ($bills = $this->get_bills()) {
            foreach ($bills as $b) {
                $b->delete();
            }
        }

        // Finally delete master record
        $DB->delete_records(self::$table, array('id' => $this->id));
    }

    function url() {
        $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $this->id));
        return $shopurl;
    }

    static function count($filter) {
        parent::_count(self::$table, $filter);
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    private function _build_nav_order() {
        $this->navorder = array();
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