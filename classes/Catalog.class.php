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
 * A catalog holds product definitions.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

use \StdClass;

require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');

/**
 * User object is provided for direct Object Mapping of the _user database model
 */
class Catalog extends ShopObject {

    protected static $table = 'local_shop_catalog';

    public $categories;

    public $ismaster;

    public $isslave;

    public function __construct($idorrecord, $light = false) {

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {

            if (!empty($this->record->groupid)) {
                if ($this->record->id == $this->record->groupid) {
                    $this->ismaster = 1;
                } else {
                    $this->isslave = 1;
                }
            } else {
                $this->ismaster = 0;
                $this->isslave = 0;
            }

            if ($light) {
                return; // This builds a lightweight proxy of the catalogue.
            }

            $this->categories = $this->get_categories();

            // These are fake fields to drive the editors in form.
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->billfooterformat = FORMAT_HTML;
            $this->record->salesconditionsformat = FORMAT_HTML;

        } else {
            $this->record->name = get_string('newcatalog', 'local_shop');
            $this->record->description = '';
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->isslave = 0;
            $this->record->ismaster = 0;
            $this->record->salesconditions = '';
            $this->record->salesconditionsformat = FORMAT_HTML;
            $this->record->billfooter = '';
            $this->record->billfooterformat = FORMAT_HTML;
            $this->record->groupid = 0;
            $this->record->countryrestrictions = '';
        }
    }

    /**
     * Get all catalog ids that reside in the same catalog dependency group
     * @param int $catalogid
     * @return an array of ids that are linked to this catalog
     */
    public function get_group_members() {
        global $DB;

        $members = array();
        $sql = "
            SELECT
                id,
                id = groupid as ismaster
            FROM
                {".self::$table."}
            WHERE
                groupid IS NOT NULL AND
                groupid = ?
            ORDER BY
                ismaster DESC
        ";
        $members = array_keys($DB->get_records_sql($sql, array($this->id)));
        if (count($members) == 0) {
            $members[] = $this->id;
        }
        return $members;
    }

    /**
     * Get catalog known categories
     */
    public function get_categories($local = false, $visible = 1) {
        global $DB;

        // Get true fetch if local are required.
        if (!empty($this->categories) && !$local) {
            return $this->categories;
        }

        // Get local categories.
        $select = " catalogid = ? AND visible = ? ";
        $params = array($this->id, $visible);
        $fields = '*,0 as masterrecord';
        if (!$localcats = $DB->get_records_select('local_shop_catalogcategory', $select, $params, 'parentid,sortorder', $fields)) {
            $localcats = array();
        }
        if ($local) {
            return $localcats;
        }

        // Get all master categories.
        $mastercats = array();
        if ($this->isslave) {
            $select = " catalogid = ? AND visible = ? ";
            $params = array($this->groupid, $visible);
            $fields = '*,1 as masterrecord';
            if (!$mastercats = $DB->get_records_select('local_shop_catalogcategory', $select, $params, 'sortorder', $fields)) {
                $mastercats = array();
            }
        }

        $this->categories = $mastercats + $localcats;

        return $this->categories;
    }

    /**
     * Get eventual slaves catalogs attached to this catalogue
     */
    public function get_slaves() {
        global $DB;

        if (!$this->ismaster) {
            return array();
        }

        $select = ' id != groupid AND groupid = ? ';
        $slaverecs = $DB->get_records_select('local_shop_catalog', $select, array($this->groupid), 'id,id');

        $slaves = array();
        if (!empty($slaverecs)) {
            foreach ($slaverecs as $s) {
                $slaves[$s->id] = new Catalog($s->id);
            }
        }

        return $slaves;
    }

    /**
     * get the full productline from categories
     * @param arrayref &$shopproducts an array to be filled
     */
    public function get_all_products(&$shopproducts) {
        global $SESSION, $DB, $USER;

        $categories = $this->get_categories();

        if (empty($categories)) {
            return array();
        }

        $isloggedinclause = self::get_isloggedin_sql();
        $modes = array();
        if (empty($SESSION->shopseeall)) {
            if (isloggedin() && !isguestuser()) {
                $modes[] = PROVIDING_BOTH;
                $modes[] = PROVIDING_LOGGEDIN_ONLY;
                if ($DB->record_exists('local_shop_customer', array('hasaccount' => $USER->id))) {
                    $modes[] = PROVIDING_CUSTOMER_ONLY;
                }
            } else {
                $modes[] = PROVIDING_BOTH;
                $modes[] = PROVIDING_LOGGEDOUT_ONLY;
            }
            $isloggedinclause = ' AND ci.onlyforloggedin IN ('.implode(',', $modes).') ';
        }

        $shopproducts = array();
        foreach ($categories as $key => $cat) {
            /*
             * product might be standalone product or set or bundle
             */
            if ($this->isslave) {
                // First get master definitions.
                $sql = "
                   SELECT
                      ci.*
                   FROM
                      {local_shop_catalogitem} as ci
                   WHERE
                      ci.catalogid = ? AND
                      ci.categoryid = ? AND
                      (ci.status = 'AVAILABLE' OR ci.status = 'PROVIDING') AND
                      ci.setid = 0
                      $isloggedinclause
                   ORDER BY
                      ci.shortname
                ";
                $params = array($this->groupid, $cat->id);
                $catalogitems = $DB->get_records_sql($sql, $params);

                // Build the master catalog structure.
                foreach ($catalogitems as $cirec) {
                    $ci = new CatalogItem($cirec);
                    $ci->thumb = $ci->get_thumb_url();
                    $ci->image = $ci->get_image_url();
                    $ci->masterrecord = 1;
                    $shopproducts[$ci->code] = $ci;
                    $categories[$key]->products[$ci->code] = $ci;
                }
                $categoryclause = '';
            } else {
                $categoryclause = " ci.categoryid = ? AND ";

            }

            // Override with slave versions.
            $sql = "
               SELECT
                  ci.*
               FROM
                  {local_shop_catalogitem} as ci
               WHERE
                  catalogid = ? AND
                  $categoryclause
                  (ci.status = 'AVAILABLE' OR ci.status = 'PROVIDING') AND
                  setid = 0
                  $isloggedinclause
               ORDER BY
                  ci.shortname
            ";
            $params = array($this->id);
            if (!$this->isslave) {
                $params[] = $cat->id;
            }
            if ($catalogitems = $DB->get_records_sql($sql, $params)) {
                foreach ($catalogitems as $cirec) {
                    $ci = new CatalogItem($cirec);
                    $ci->thumb = $ci->get_thumb_url();
                    $ci->image = $ci->get_image_url();
                    $ci->masterrecord = 0;
                    if ($this->isslave) {
                        $original = $shopproducts[$ci->code];
                        $shopproducts[$ci->code] = $ci;
                        $categories[$original->categoryid]->products[$ci->code] = $ci;
                    } else {
                        $categories[$key]->products[$ci->code] = $ci;
                    }
                }
            }
        }

        // Complementary processing for sets : fetch set elements and eventual overrides.
        if (!empty($shopproducts)) {
            foreach (array_values($shopproducts) as $ci) {
                if ($ci->isset) {

                    // Get set elements in master catalog (same set code).
                    if ($this->isslave) {
                        $sql = "
                          SELECT
                            ci.*
                          FROM
                            {local_shop_catalogitem} as ci,
                            {local_shop_catalogitem} as cis
                          WHERE
                            ci.setid = cis.id AND
                            cis.code = ? AND
                            (ci.status = 'AVAILABLE' OR ci.status = 'PROVIDING') AND
                            ci.catalogid = ?
                            $isloggedinclause
                          ORDER BY
                            ci.shortname
                        ";
                        $catalogitems = $DB->get_records_sql($sql, array($ci->code, $this->groupid));
                        foreach ($catalogitems as $cirec) {
                            $ci1 = new CatalogItem($cirec);
                            $ci1->thumb = $ci1->get_thumb_url();
                            $ci1->image = $ci1->get_image_url();
                            $ci1->masterrecord = 1;
                            $ci->set_element($ci1);
                        }
                    }

                    // Override with local versions.
                    $sql = "
                      SELECT
                        ci.*
                      FROM
                        {local_shop_catalogitem} as ci
                      WHERE
                        ci.setid = ? AND
                        (ci.status = 'AVAILABLE' OR ci.status = 'PROVIDING') AND
                        ci.catalogid = ?
                        $isloggedinclause
                         ORDER BY
                        ci.shortname
                    ";

                    if ($catalogitems = $DB->get_records_sql($sql, array($ci->id, $this->id))) {
                        foreach ($catalogitems as $cirec) {
                            $ci1 = new CatalogItem($cirec);
                            $ci1->thumb = $ci1->get_thumb_url();
                            $ci1->image = $ci1->get_image_url();
                            $ci1->masterrecord = 0;
                            $ci->set_element($ci1);
                        }
                    }
                    $shopproducts[$ci->code]->set = $ci;
                }
            }
        }

        return $categories;
    }

    /**
     * get the full productline from categories
     */
    public function get_all_products_for_admin(&$shopproducts) {
        global $SESSION, $DB;

        $categories = $this->get_categories();

        if (empty($categories)) {
            return array();
        }

        // Restrict to explicit category.
        if (!empty($SESSION->shop->categoryid)) {
            $categories = array($SESSION->shop->categoryid => $categories[$SESSION->shop->categoryid]);
        }

        $shopproducts = array();
        foreach ($categories as $key => $cat) {
            // Get master catalog items.
            /*
             * product might be standalone product or set or bundle
             */
            if ($this->isslave) {
                $sql = "
                   SELECT
                      ci.*
                   FROM
                      {local_shop_catalogitem} as ci
                   WHERE
                      ci.catalogid = ? AND
                      ci.categoryid = ? AND
                      ci.setid = 0
                   ORDER BY
                      ci.shortname
                ";
                $catalogitems = $DB->get_records_sql($sql, array($this->groupid, $cat->id));
                foreach ($catalogitems as $cirec) {
                    $ci = new CatalogItem($cirec);
                    $ci->thumb = $ci->get_thumb_url();
                    $ci->image = $ci->get_image_url();
                    $ci->masterrecord = 1;
                    $shopproducts[$ci->code] = $ci;
                    $categories[$key]->products[$ci->code] = $ci;
                }
            }
            // Override with slave versions.
            $sql = "
               SELECT
                  ci.*
               FROM
                  {local_shop_catalogitem} as ci
               WHERE
                  catalogid = ? AND
                  categoryid = ? AND
                  setid = 0
               ORDER BY
                  ci.shortname
            ";
            if ($catalogitems = $DB->get_records_sql($sql, array($this->id, $cat->id))) {
                foreach ($catalogitems as $cirec) {
                    $ci = new CatalogItem($cirec);
                    $ci->thumb = $ci->get_thumb_url();
                    $ci->image = $ci->get_image_url();
                    $ci->masterrecord = 0;
                    $shopproducts[$ci->code] = $ci;
                    $categories[$key]->products[$ci->code] = $ci;
                }
            }
        }

        // Complementary processing for sets : fetch set elements and eventual overrides.
        if (!empty($shopproducts)) {
            $elementcodes = array();
            foreach (array_values($shopproducts) as $ci) {
                if ($ci->isset) {

                    // Get set elements in master catalog (same set code).
                    if ($this->isslave) {
                        $sql = "
                          SELECT
                            ci.*
                          FROM
                            {local_shop_catalogitem} as ci,
                            {local_shop_catalogitem} as cis
                          WHERE
                            ci.setid = cis.id AND
                            cis.code = ? AND
                            ci.catalogid = ?
                          ORDER BY
                            ci.shortname
                        ";
                        $catalogitems = $DB->get_records_sql($sql, array($ci->code, $this->groupid));
                        foreach ($catalogitems as $cirec) {
                            $ci1 = new CatalogItem($cirec);
                            $ci1->thumb = $ci1->get_thumb_url();
                            $ci1->image = $ci1->get_image_url();
                            $ci1->masterrecord = 1;
                            $ci->set_element($ci1);
                            $elementcodes[$cirec->code] = $cirec->id;
                        }
                    }
                    // Override with local versions.
                    $sql = "
                      SELECT
                        ci.*
                      FROM
                        {local_shop_catalogitem} as ci
                      WHERE
                        ci.setid = ? AND
                        ci.catalogid = ?
                      ORDER BY
                        ci.shortname
                    ";

                    if ($catalogitems = $DB->get_records_sql($sql, array($ci->id, $this->id))) {
                        foreach ($catalogitems as $cirec) {
                            $ci1 = new CatalogItem($cirec);
                            $ci1->thumb = $ci1->get_thumb_url();
                            $ci1->image = $ci1->get_image_url();
                            $ci1->masterrecord = 0;
                            $ci->set_element($ci1);
                            // Remove master version of this product.
                            if ($this->isslave) {
                                $ci->delete_element($elementcodes[$cirec->code]);
                            }
                        }
                    }
                    $shopproducts[$ci->code]->set = $ci;
                }
            }
        }

        return $categories;
    }

    /**
     * Get a single catalogitem using short code as key
     * @param string $code the product shortcode
     * @return a CatalogItem object
     */
    public function get_product_by_code($code) {
        global $DB;

        $params = array('catalogid' => $this->id, 'code' => $code);
        return new CatalogItem($DB->get_record('local_shop_catalogitem', $params));
    }

    /**
     * Queries a catalog to find a complete catalog item instance
     * @param string $shortname the shortname of the product
     * @param boolean $mustexist if false, the function returns a "new item"
     * empty element.
     * @return a CatalogItem object
     */
    public function get_product_by_shortname($shortname, $mustexist = false) {
        global $DB;

        $params = array('catalogid' => $this->id, 'shortname' => $shortname);
        $record = $DB->get_record('local_shop_catalogitem', $params);
        if (!$mustexist || $record) {
            $catalogitem = new CatalogItem($record);
            return $catalogitem;
        }
        return null;
    }

    /**
     * Get all true products in this catalog.
     * True products are independant products, or master records
     * for a set or a bundle.
     * @return an array of products/items keyed by item shortcode.
     */
    public function get_products($order = 'code', $dir = 'ASC', $categoryid = '') {
        global $DB;

        $products = array();

        if ($categoryid) {
            $select = '
                catalogid = :catalogid AND
                categoryid = :categoryid AND
                setid = 0 OR
                (setid = id)
            ';
            $params = array('catalogid' => $this->id, 'categoryid' => $categoryid);
            $items = $DB->get_records_select('local_shop_catalogitem', $select, $params, " $order $dir");
        } else {
            $select = ' catalogid = :catalogid AND setid = 0 or (setid = id) ';
            $params = array('catalogid' => $this->id);
            $items = $DB->get_records_select('local_shop_catalogitem', $select, $params, " $order $dir");
        }

        if ($items) {
            foreach ($items as $item) {
                $products[$item->code] = new CatalogItem($item);
            }
        }

        return $products;
    }

    /**
     * @param text $country Country code
     * @param text $zipcode Customer zipcode
     * @param array $order array of ordered elements (quantity keyed by catalogitem label)
     * @return an object providing entries for a billitem setup as shipping additional
     * pseudo product
     */
    public function calculate_shipping($shoppingcart = null) {
        global $DB, $SESSION, $CFG;

        if (!$shoppingcart) {
            $shoppingcart = $SESSION->shoppingcart;
        }

        $c = $shoppingcart->customerinfo->country;

        $message = "[{$shoppingcart->transid}] shop Shipping Calculation for ";
        $message .= "[{$c}][$shoppingcart->customerinfo->zipcode]";
        shop_trace($message);

        if (!$shipzones = $DB->get_records('local_shop_catalogshipzone', array('catalogid' => $this->id))) {
            shop_trace('No shipzones');
            $return = new StdClass;
            $return->value = 0;
            return $return;
        }

        // Determinating shipping zone.
        function reduce_and($v, $w) {
            return $v && $w;
        }
        function reduce_or($v, $w) {
            return $v || $w;
        }
        $applicable = null;
        $zip = $shoppingcart->customerinfo->zipcode;
        foreach ($shipzones as $z) {
            if ($z->zonecode == '00') {
                $defaultzone = $z;
                continue; // Optional '00' special default zone is considered 'in fine'.
            }
            $ands = preg_split('/&\|/', $z->applicability); // Detokenize &.
            for ($i = 0; $i < count($ands); $i++) {
                if (strstr('|', $ands[$i])) {
                    $ors = preg_split('/\|/', $ands[$i]); // Detokenize |.
                    for ($j = 0; $j < count($ors); $j++) {
                        $ors[$j] = shop_resolve_zone_rule($c, $zip, $ors[$j]);
                    }
                    $ands[$i] = array_reduce($ors, 'reduce_or', false);
                } else {
                    $ands[$i] = shop_resolve_zone_rule($c, $zip, $ands[$i]);
                }
            }
            if (array_reduce($ands, 'reduce_and', true)) {
                $applicable = $z;
                break;
            } else {
                if (isset($defaultzone)) {
                    $applicable = $defaultzone;
                    break;
                }
                // In spite of shipzones found in the way, none applicable.
                shop_trace("[{$transactionid}] No shipzone applicable for [$c][$zip]");
                $return->value = 0;
                return $return;
            }
        }
        shop_trace("[{$transactionid}] shop Shipping : Found applicable zone $applicable->zonecode ");
        // Checking bill scope shipping for zone.
        if ($applicable->billscopeamount != 0) {
            shop_trace("[{$transactionid}] shop Shipping : Using bill scope amount ");
            $return->value = $applicable->billscopeamount;
            $return->code = 'SHIP_';
            $return->taxcode = $applicable->taxid;
            // Calculate tax amounts.
            $return->taxedvalue = shop_calculate_taxed($return->value, $applicable->taxid);
            return $return;
        }
        shop_trace("[{$transactionid}] shop Shipping : Examinating shippings");
        // Examinating products.
        if ($shippings = $DB->get_records('local_shop_catalogshipping', array('zoneid' => $applicable->id))) {
            $return->code = 'SHIP_';
            $return->taxcode = $applicable->taxid;
            $return->value = 0;
            require_once($CFG->dirroot.'/local/shop/extlib/extralib.php');
            foreach ($shippings as $sh) {
                $shippedproduct = $DB->get_record('local_shop_catalogitem', array('code' => $sh->productcode));
                // Must be a valid product in order AND have some items required.
                if (array_key_exists($shippedproduct->shortname, $order) && $order[$shippedproduct->shortname] > 0) {
                    if ($sh->value > 0) {
                        $return->value += $sh->value;
                    } else {
                        if (!empty($sh->formula)) {
                            $in['a'] = $sh->a;
                            $in['b'] = $sh->b;
                            $in['c'] = $sh->c;
                            $in['ht'] = $shippedproduct->price1;
                            $in['ttc'] = shop_calculate_taxed($shippedproduct->price1, $shippedproduct->taxcode);
                            $in['q'] = $order[$shippedproduct->shortname];
                            $result = evaluate(\core_text::strtolower($sh->formula).';', $in, 'shp');
                            $return->value += 0 + @$result['shp'];
                        } else {
                            $return->value += 0;
                        }
                    }
                }
            }
            if ($return->value > 0) {
                $return->taxedvalue = shop_calculate_taxed($return->value, $applicable->taxid);
            } else {
                $return->taxedvalue = 0;
            }
            return $return;
        }
        // Void return if no shipping solution.
        shop_trace("[{$transactionid}] shop Shipping : No shipping solution");
        $return->value = 0;
        return $return;
    }

    public function is_not_used() {
        global $DB;

        return 0 == $DB->count_records('local_shop', array('catalogid' => 0 + $this->id));
    }

    /**
     * Get all catalog items for a catalog and for given user.
     * @param int $catalogid the catalog ID
     * @param string $order the column for ordering list
     * @param string $dir the sort direction, ASC or DESC
     * @param bool $nosets if set, ignore product sets
     * @param int $userid the product owner. 0 means site owned products, null will display all products.
     */
    public function get_products_by_code($order = 'code', $dir = 'ASC', $masterrecords = 0,
                                         $nosets = false, $userid = null) {
        global $DB;

        $nosetsql = ($nosets) ? " NOT (setid != 0 AND isset = 0) AND " : '';
        $useridsql = (is_null($userid)) ? '' : ' AND ci.userid = ? ';

        $sql = "
            SELECT
               ci.code as code,
               ci.*,
               CASE WHEN t.id IS NULL THEN 0 ELSE t.ratio END as tax,
               $masterrecords as masterrecord
            FROM
               {local_shop_catalogitem} as ci
            LEFT JOIN
               {local_shop_tax} as t
            ON
               ci.taxcode = t.id
            WHERE
               $nosetsql
               catalogid = ?
               $useridsql
            ORDER BY
               $order $dir
        ";

        $params = array($this->id);
        if (!empty($userid)) {
            $params[] = $userid;
        }

        $allproducts = array();
        if ($catalogitems = $DB->get_records_sql($sql, $params)) {
            foreach ($catalogitems as $cirec) {
                $ci = new CatalogItem($cirec);
                $allproducts[$ci->code] = $ci;
            }
        }
        return $allproducts;
    }

    /**
     * checks in purchased products the role equipement requirement
     * @TODO : scan shoping cart and get role req info from products
     */
    public function check_required_roles() {
        global $SESSION;

        $requiredroles = array('student' => true);

        if (!empty($SESSION->shoppingcart->order)) {
            foreach ($SESSION->shoppingcart->order as $shortname => $quantity) {
                $product = $this->get_product_by_shortname($shortname);
                $handlerparams = $product->get_serialized_handlerparams();
                $params = json_decode($handlerparams);
                if (!empty($params->requiredroles)) {
                    $roles = explode(',', $params->requiredroles);
                    foreach ($roles as $r) {
                        // Make it unique.
                        if ($r == 'supervisor') {
                            // Special case.
                            $r = '_supervisor';
                        }
                        $requiredroles[$r] = true;
                    }
                }
            }
        }

        return array_keys($requiredroles);
    }

    /**
     * checks purchased products and quantities and calculates the neaded amount of seats.
     * We need check in catalog definition id product is seat driven or not. If seat driven
     * the quantity adds to seat couts. If not, 1 seat is added to the seat count.
     */
    public function check_required_seats() {
        global $SESSION;

        $seats = 0;

        if (empty($SESSION->shoppingcart->order)) {
            return 0;
        }

        foreach ($SESSION->shoppingcart->order as $shortname => $quantity) {
            $product = $this->get_product_by_shortname($shortname);
            if ($product->quantaddressesusers == SHOP_QUANT_AS_SEATS) {
                $seats += $quantity;
            } else if ($product->quantaddressesusers == SHOP_QUANT_ONE_SEAT) {
                $seats += 1;
            }
        }

        $SESSION->shoppingcart->seats = $seats;

        return $seats;
    }

    /**
     * Restricts list of available countries per catalog.
     */
    public function process_country_restrictions(&$choices) {
        $restricted = array();

        if (!empty($this->record->countryrestrictions)) {
            $restrictedcountries = explode(',', \core_text::strtoupper($this->record->countryrestrictions));

            foreach ($restrictedcountries as $rc) {
                // Blind ignore unkown codes...
                if (array_key_exists($rc, $choices)) {
                    $restricted[$rc] = $choices[$rc];
                }
            }
            $choices = $restricted;
        }

    }

    public function delete() {
        global $DB;

        // Deletes all our direct dependencies.
        $DB->delete_records('local_shop_catalogitem', array('catalogid' => $this->id));
        $DB->delete_records('local_shop_catalogcategory', array('catalogid' => $this->id));

        // Clear all fileareas linked with products.
        $fs = get_file_storage();

        $contextid = \context_system::instance()->id;

        $fs->delete_area_files($contextid, 'local_shop', 'catalogdescription', $this->id);

        parent::delete();
    }

    public function export($level = 0) {

        $level++;
        $indent = str_repeat('    ', $level);

        $yml = '';

        $yml .= "catalog:\n";

        $yml .= parent::export($level);

        $yml = "\n";

        if (!empty($this->categories)) {
            $yml .= $indent.'categories:'."\n";
            $level++;
            $indent = str_repeat('    ', $level);
            foreach ($this->categories as $acategory) {
                $yml .= $indent.'- '.$acategory->export($level);
            }
            $yml .= "\n";
            $level--;
            $indent = str_repeat('    ', $level);
        }

        $this->get_all_products_for_admin($shopproducts);
        if (!empty($shoppproducts)) {
            $yml .= $indent.'items:'."\n";
            $level++;
            $indent = str_repeat('    ', $level);
            foreach ($shopproducts as $ci) {
                $yml .= $indent.$ci->export($level);
            }
            $yml .= "\n";
            $level--;
            $indent = str_repeat('    ', $level);
        }

        $level--;

        return $yml;
    }

    /**
     * Restricts list of available countries per catalog.
     */
    public static function process_merged_country_restrictions(&$choices) {
        global $DB;

        if ($DB->count_records_select('local_shop_catalog', " countryrestrictions = '' ")) {
            // Quick pass through.
            return;
        }

        $allcatalogs = $DB->get_records('local_shop_catalog', array(), 'id', 'id,countryrestrictions');

        $restrictedcountries = array();
        foreach ($allcatalogs as $c) {
            $restrictedcountries = $restrictedcountries + explode(',', $c->countryrestrictions);
        }

        $restricted = array();
        if (!empty($restrictedcountries)) {
            foreach ($restrictedcountries as $rc) {
                // Blind ignore unkown codes...
                $cc = strtoupper($rc);
                if (array_key_exists($cc, $choices)) {
                    $restricted[$rc] = $choices[$cc];
                }
            }
            $choices = $restricted;
        }
    }

    public function export_to_ws() {
        $export = new StdClass;

        $export->id = $this->record->id;
        $export->name = format_string($this->record->name);
        $export->description = format_text($this->record->description, $this->record->descriptionformat);
        $export->salesconditions = $this->record->salesconditions;
        $export->countryrestrictions = $this->record->countryrestrictions;

        $categories = $this->get_categories();
        $export->categories = [];
        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $exportcat = new StdClass;
                $exportcat->id = $cat->id;
                $exportcat->name = format_string($cat->name);
                $export->categories[] = $exportcat;
            }
        }

        return $export;
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*',
                                         $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances_for_admin() {

        if ($instances = self::get_instances(array(), 'groupid,id')) {
            foreach ($instances as $c) {
                $instances[$c->id]->categories = Category::count(array('catalogid' => $c->id));
                $instances[$c->id]->items = CatalogItem::count(array('catalogid' => $c->id));
            }
        }

        return $instances;
    }

    public static function get_instances_menu($filter = array(), $order = '') {
        return parent::_get_instances_menu(self::$table, $filter, $order);
    }

    public static function get_isloggedin_sql($tableprefix = '') {
        global $SESSION, $DB, $USER;

        $isloggedinclause = '';

        $modes = array();
        if (empty($SESSION->shopseeall)) {
            if (isloggedin() && !isguestuser()) {
                $modes[] = PROVIDING_BOTH;
                $modes[] = PROVIDING_LOGGEDIN_ONLY;
                if ($DB->record_exists('local_shop_customer', array('hasaccount' => $USER->id))) {
                    $modes[] = PROVIDING_CUSTOMER_ONLY;
                }
            } else {
                $modes[] = PROVIDING_BOTH;
                $modes[] = PROVIDING_LOGGEDOUT_ONLY;
            }
            if ($tableprefix) {
                $isloggedinclause = ' AND '.$tableprefix.'.onlyforloggedin IN ('.implode(',', $modes).') ';
            } else {
                $isloggedinclause = ' AND onlyforloggedin IN ('.implode(',', $modes).') ';
            }
        }
        return $isloggedinclause;
    }
}