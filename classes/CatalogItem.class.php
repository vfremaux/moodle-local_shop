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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @categroy    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');

/*
CatalogItem object is provided for direct Object Mapping of the _catalogitem database model
*/
class CatalogItem extends ShopObject {

    static $table = 'local_shop_catalogitem';

    // If a set or bundle, can have elements.
    var $elements;

    // Fasten a 'by code' reference.
    var $elementsbycode;

    protected $tax;

    public $available;

    protected $handlerparams;

    /**
     * True until not having been explicitely identified as a slave
     * view.
     */
    var $masterrecord = 1;

    function __construct($idorrecord, $light = false) {
        $this->elements = array();
        parent::__construct($idorrecord, self::$table);

        $this->available = true;

        if ($idorrecord) {

            if ($light) return; // this builds a lightweight proxy of the Shop, without catalogue

            if ($this->isset) {
                $this->elements = CatalogItem::get_instances(array('setid' => $this->id), 'code');
                $catalog = new Catalog($this->catalogid);
                if (!empty($this->elements)) {
                    foreach ($this->elements as $elm) {
                        $this->elements[$elm->id]->catalog = $catalog;
                        $this->elementsbycode[$elm->code] = $elm;
                    }
                }
            }

            // Decode and expand handler params if any.
            if (!empty($this->record->handlerparams)) {
                $pairs = explode('&', $this->record->handlerparams);
                if (!empty($pairs)) {
                    foreach ($pairs as $p) {
                        list($param,$value) = explode('=', $p);
                        $this->handlerparams[$param] = $value;
                    }
                }
            }

        } else {
            $this->record->name = get_string('newitem', 'local_shop');
            $this->record->description = '';
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->notes = '';
            $this->record->notesformat = FORMAT_HTML;
            $this->record->eula = '';
            $this->record->eulaformat = FORMAT_HTML;
        }
    }

    // get the accurate price against quantity ranges
    function get_price($q) {
        if (@$this->record->range1) {
            if ($q <= $this->record->range1) {
                return $this->record->price1;
            }
            if ($this->record->range2) {
                if ($q <= $this->record->range2) {
                    return $this->record->price2;
                }
                if ($this->record->range3) {
                    if ($q <= $this->record->range3) {
                        return $this->record->price3;
                    }
                    if ($this->record->range4) {
                        if ($q <= $this->record->range4) {
                            return $this->record->price4;
                        }
                        if ($this->record->range4) {
                            if ($q <= $this->record->range4) {
                                return $this->record->price4;
                            } else {
                                return $this->record->price5;
                            }
                        } else {
                            return $this->record->price4;
                        }
                    } else {
                        return $this->record->price4;
                    }
                } else {
                    return $this->record->price3;
                }
            } else {
                return $this->record->price2;
            }
        } else {
            return @$this->record->price1;
        }
    }

    function get_serialized_handlerparams() {
        return $this->record->handlerparams;
    }

    function get_tax($q) {
        if ($this->taxcode && $this->tax) {
            return $this->tax;
        }
        $this->get_taxed_price($q); // forces tax to calculate
        return $this->tax;
    }

    function get_printable_prices($taxed = false) {
        global $DB;

        $str = '';

        $prices = array();
        $key = (!@$this->record->range1) ? '0-' : "0-{$this->record->range1}" ;
        if ($taxed) {
            $prices[$key] = sprintf('%.2f', $this->get_taxed_price(0, $this->record->taxcode));
        } else {
            $prices[$key] = sprintf('%.2f', $this->record->price1);
        }

        for ($i = 1 ; $i < 5 ; $i++) {
            $j= $i+1;
            $r1 = "range$i";
            $r2 = "range$j";
            $p = "price$j";
            if ($taxed) {
                $pr = sprintf("%.2f", round($this->get_taxed_price($this->record->$r1 + 1, $this->record->taxcode), 2));
            } else {
                $pr = sprintf("%.2f", round($this->record->$p, 2));
            }
            if ($this->record->$r1) {
                $rangestart = $this->record->$r1 + 1;
                if (@$this->record->$r2) {
                    $prices["{$rangestart}-{$this->record->$r2}"] = $pr;
                } else {
                    $prices["{$rangestart}-"] = $pr;
                }
            }
        }
        
        return $prices;
    }
    
    function get_taxed_price($q, $taxid = 0) {
        static $TAXCACHE;
        global $DB;

        if (empty($taxid)) $taxid = $this->taxcode;
        
        unset($TAXCACHE);
        if (!isset($TAXCACHE)) {
            $TAXCACHE = array();
        }

        if (!array_key_exists($taxid, $TAXCACHE)) {
            if ($TAXCACHE[$taxid] = $DB->get_record('local_shop_tax', array('id' => $taxid))) {
                if (empty($TAXCACHE[$taxid]->formula)) $TAXCACHE[$taxid]->formula = '$TTC = $HT';
            } else {
                return $this->get_price($q);
            }
        }

        $HT = $this->get_price($q);
        $TR = $TAXCACHE[$taxid]->ratio;
        eval($TAXCACHE[$taxid]->formula.';');
        $this->tax = $TTC - $HT;
        return $TTC;
    }

    // this will override existing elements
    function setElement($elm) {
        $this->elements[$elm->code] = $elm;
    }

    // this will override existing elements
    function getElement($code) {
        if (array_key_exists($code, $this->elements)) {
            return $this->elements[$elm->code];
        } else {
            throw Exception('nosuchelement');
        }
    }

    /**
    *
    *
    */
    function get_sales_unit_url() {
        global $CFG, $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemunit', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemunit', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(), $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
        } else {
            $url = $OUTPUT->pix_url(current_language().'/one_unit', 'local_shop');
        }
        return $url;
    }

    /**
    *
    *
    */
    function get_image_url() {
        global $CFG, $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemimage', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemimage', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(), $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
            return $url;
        }
        return false;
    }

    /**
    *
    *
    */
    function get_thumb_url() {
        global $CFG, $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemthumb', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemthumb', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(), $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
        } else {
            $url = $OUTPUT->pix_url('defaultproduct', 'local_shop');
        }
        return $url;
    }

    /**
     * Gets a suitable handler object for this catalog item
     */
    function get_handler() {
        global $CFG;

        $enablehandler = $this->enablehandler;
        $handlerlabel = $this->shortname;

        $handler = null;

        if (empty($enablehandler)) {
            return false;
        } elseif ($enablehandler == SPECIFIC_HANDLER) {
            $thehandler = $anItem->itemcode;
        } else {
            $thehandler = $enablehandler;
        }

        if (!empty($thehandler) && file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php')) {
            include_once($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php');
            $classtype = "shop_handler_{$thehandler}";
            $handler = new $classtype($handlerlabel);
        }

        return $handler;
    }

    function get_shippings() {
        global $DB;

        $sql = "
            SELECT
                cs.*
            FROM
                {local_shop_catalogshipping} cs,
                {local_shop_catalogitem} ci
            WHERE
                ci.isset = 0 AND
                ci.code = cs.productcode AND
                ci.id = ?
        ";

        return $DB->get_records($sql, array($this->id));
    }

    function get_shipping_zones() {
        global $DB;

        $sql = "
            SELECT
                csz.*,
                count cs.id as entries
            FROM
                {local_shop_catalogshipping} cs,
                {local_shop_catalogshippingzone} csz,
                {local_shop_catalogitem} ci
            WHERE
                ci.isset = 0 AND
                ci.code = cs.productcode AND
                csz.id = cs.zoneid AND
                ci.id = ?
            GROUP BY
                csz.id
        ";

        return $DB->get_records($sql, array($this->id));
    }

    function unlink() {
        global $DB;

        $DB->set_field('local_shop_catalogitem', 'setid', 0, array('id' => $this->id));
    }

    function has_leaflet() {
        $context = \context_system::instance();

        $fs = get_file_storage();

        return $fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $this->id);
    }

    function check_availability() {

        $config = get_config('local_shop');

        // Check if product has handler and is available
        if ($this->enablehandler) {
            $handler = $this->get_handler();
            if ($handler && !$handler->is_available($this)) {
                // TODO : defer this check at a shop instance level, using
                // global config key as default setting.
                if ($config->hideproductswhennotavailable) {
                    continue;
                } else {
                    $this->available = false;
                }
            }
        }
    }

    function get_leaflet_url() {
        global $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemleaflet', $this->id);
            $leafletfile = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($leafletfile->get_contextid(), $leafletfile->get_component(), $leafletfile->get_filearea(), $leafletfile->get_itemid(), $leafletfile->get_filepath(), $leafletfile->get_filename());
        } else {
            $url = $OUTPUT->pix_url('defaultproduct', 'local_shop');
        }
        return $url;
    }

    /**
     * Apply() overrides the current instance with the elements of the override.
     * Overriden attributes are shoosen to address the master question : Why using
     * slave catalogs: master usecase is to internationalize or change some commercial
     * values for a special country/region. 
     * Variant should not alter the effective nature of the product, not technical definition.
     */
    function apply(CatalogItem $override) {

        // Override some attributes (textual)
        $this->name = $override->name;
        $this->description = $override->description;
        $this->descriptionformat = $override->descriptionformat;

        $this->notes = $override->notes;
        $this->notesformat = $override->notesformat;

        $this->eula = $override->eula;
        $this->eulaformat = $override->eulaformat;

        // Override prices.
        $this->price1 = $override->price1;
        $this->range1 = $override->range1;
        $this->price2 = $override->price2;
        $this->range2 = $override->range2;
        $this->price3 = $override->price3;
        $this->range3 = $override->range3;
        $this->price4 = $override->price4;
        $this->range4 = $override->range4;
        $this->price5 = $override->price5;
        $this->range5 = $override->range5;

        $this->masterrecord = 0;

        // A different tax code may be applied for this variant.
        $this->taxcode = $override->taxcode;

        // Suboverride elements if any.
        if ($this->elements) {
            foreach($override->elements as $ovelm) {
                if (in_array($this->elementsbycode($ovelm->code))) {
                    $this->elements[$this->elementsbycode[$ovelm->code]]->apply($ovelm);
                } else {
                    // This should usually not happen as overrides should always be local copies of master records.
                    // This might accidentally happen when trying to apply the wrong way (f.e. master on local)
                    throw new coding_exception('Unexpected unmatching override in CatalogItem '.$this->id);
                }
            }
        }

        return $this;
    }

    function remove_content() {
        if (!$this->elements) {
            $this->elements = CatalogItem::get_instances(array('setid' => $setid));
        }

        foreach ($this->elements as $elm) {
            $elm->unlink();
        }
    }

    /**
     * Deletes the catalogitem releasing elements as standard products.
     */
    function delete() {
        $setid = $this->setid;

        $this->remove_content();

        parent::delete();
    }

    function fulldelete() {
        $setid = $this->setid;

        if (!$this->elements) {
            $this->elements = CatalogItem::get_instances(array('setid' => $setid));
        }

        foreach ($this->elements as $elm) {
            $elm->delete();
        }

        parent::delete();
    }

    static function count($filter) {
        parent::_count(self::$table, $filter);
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    static function search($by, $arg, $searchscope = null) {
        global $DB;

        $whereclause = '';
        switch ($by) {
            case "code":
               $whereclause = "   code = ? ";
               $params[] = $arg;
               break;
            case "shortname":
               $whereclause = "   shortname LIKE ? ";
               $params[] = $arg.'%';
               break;
            case "name":
               $whereclause = "   UPPER(name) LIKE UPPER(?) ";
               $params[] = $arg;
               break;
            default:
               $error = true;
        }

        $scopeclause = '';
        if (!empty($searchscope)) {
            if (is_array()) {
                list($scopesql, $scopeparams) = $DB->get_in_or_equals($searchscope);
                $scopeclause = 'catalogid IN '.$scopesql;
                $params = $params + $scopeparams;
            } else {
                $scopeclause = 'catalogid = ? ';
                $params[] = $searchscope;
            }
        }

        if (!empty($whereclause) && !empty($scopeclause)) {
            $whereclause = 'AND '.$whereclause;
        }

        $results = array();
        if (!$error) {
            $sql = "
               SELECT
                  ci.*
               FROM
                  {local_shop_catalogitem} as ci
               WHERE
                  $scopeclause
                  $whereclause
            ";

            $results = $DB->get_records_sql($sql, array($catalogid));
        }

        return $results;
    }

    /**
     * computes a shortname for javascript purpose, ensuring it is unique
     * and has no harmfull chars for token application.
     * @param object $formdata data received from edition forms.
     */
    static function compute_item_shortname(&$formdata) {
        global $DB;

        $shortname = $formdata->code;
        $shortname = strtolower(str_replace(' ', '_', $shortname));
        $shortnamebase = $shortname;

        $index = 1;

        while ($DB->record_exists('local_shop_catalogitem', array('shortname' => $shortname))) {
            $shortname = $shortnamebase.$index;
            $index++;
        }

        return $shortname;
    }
}