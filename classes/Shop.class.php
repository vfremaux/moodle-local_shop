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
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

class Shop extends ShopObject {

    /**
     * An ref to the catalogue
     */
    public $thecatalogue;

    /**
     * An array of navigation paths
     */
    private $navorder;

    protected static $table = 'local_shop';

    /**
     * Build a full shop instance.
     */
    public function __construct($idorrecord, $light = false) {
        global $DB;

        $config = get_config('local_shop');

        parent::__construct($idorrecord, self::$table);

        self::expand_paymodes($this->record);

        if ($idorrecord) {

            if ($light) {
                return; // This builds a lightweight proxy of the Shop, without catalogue.
            }

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

            // Initiate empty fields.
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
            $this->record->discountthreshold = $config->discountthreshold;
            $this->record->discountrate = $config->discountrate;
            $this->record->discountrate2 = $config->discountrate2;
            $this->record->discountrate3 = $config->discountrate3;
            $this->record->eula = '';
            $this->record->navsteps = $config->defaultnavsteps;
            $this->_build_nav_order();
        }
    }

    public function get_starting_step() {
        return array_keys($this->navorder['nextstep'])[0];
    }

    public function get_next_step($step) {
        return $this->navorder['nextstep'][$step];
    }

    public function get_prev_step($step) {
        return $this->navorder['prevstep'][$step];
    }

    /**
     *
     */
    public function get_catalogue() {
        return $this->thecatalogue;
    }

    /**
     * Get the current currency of the shop instance
     */
    public function get_currency($long = false) {
        global $CFG;

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
     */
    public static function compact_paymodes(&$shoprec) {

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
    public static function expand_paymodes(&$shoprec) {
        if (!empty($shoprec->paymodes)) {
            $expanded = unserialize(base64_decode($shoprec->paymodes));;
            $paymodeenable = array_shift($expanded);
        } else {
            $paymodeenable = array();
            $defaultpaymode = '';
        }

        foreach ($paymodeenable as $k => $v) {
            $key = 'enable'.$k;
            $shoprec->$key = $v;
        }
    }

    public function get_blocks() {
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

    public function get_bills() {
        return Bill::get_instances(array('shopid' => $this->id));
    }

    public function delete() {
        global $DB;

        if ($bills = $this->get_bills()) {
            foreach ($bills as $b) {
                $b->delete();
            }
        }

        // Finally delete master record.
        $DB->delete_records(self::$table, array('id' => $this->id));
    }

    public function url() {
        $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $this->id));
        return $shopurl;
    }

    public function calculate_discountrate_for_user($amount, &$context, &$reason, $user = null) {
        global $CFG, $USER;

        if (is_null($user)) {
            $user = $USER;
        }

        $discountrate = 0; // No discount as default.

        if ($thresholdcond = $this->record->discountthreshold &&
                $this->record->discountrate &&
                        ($amount > $this->record->discountthreshold)) {
            $discountrate = $this->record->discountrate;
            $reason = get_string('ismorethan', 'local_shop');
            $reason .= '<b>'.$this->record->discountthreshold.'&nbsp;</b><b>'.$this->get_currency('symbol').'</b>';
        }

        if (isloggedin()) {
            if ($usercond1 = has_capability('local/shop:discountagreed', $context, $USER->id)) {
                $discountrate = $this->record->discountrate;
                $reason = get_string('userdiscountagreed', 'local_shop');
            }
            if ($usercond2 = has_capability('local/shop:seconddiscountagreed', $context, $USER->id)) {
                $discountrate = $this->record->discountrate2;
                $reason = get_string('userdiscountagreed2', 'local_shop');
            }
            if ($usercond3 = has_capability('local/shop:thirddiscountagreed', $context, $USER->id)) {
                $discountrate = $this->record->discountrate3;
                $reason = get_string('userdiscountagreed3', 'local_shop');
            }
        }

        return $discountrate;
    }

    public static function count($filter) {
        return parent::_count(self::$table, $filter);
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
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