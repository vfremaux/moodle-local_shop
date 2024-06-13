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
 * A catalogitem is an element of a catalog.
 *
 * @package     local_shop
 * @categroy    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

use \StdClass;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/extlib/extralib.php');

/**
 * CatalogItem object is provided for direct Object Mapping of the _catalogitem database model
 */
class CatalogItem extends ShopObject {

    protected static $table = 'local_shop_catalogitem';

    /**
     * The reference catalog for this item.
     */
    protected $thecatalog;

    /**
     * If a set or bundle, can have elements.
     */
    public $elements;

    // Fasten a 'by code' reference.
    public $elementsbycode;

    // Tax object representing the associated tax.
    protected $tax;

    public $available;

    protected $handlerparams;

    /**
     * True until not having been explicitely identified as a slave
     * view.
     */
    public $masterrecord = 1;

    public function __construct($idorrecord, $light = false) {

        $this->elements = array();
        parent::__construct($idorrecord, self::$table);

        $this->available = true;

        if ($idorrecord) {

            if ($light) {
                // This builds a lightweight proxy of the Shop, without catalogue.
                return;
            }

            $this->thecatalog = new Catalog($this->catalogid, false);
            if ($this->isset) {
                $this->elements = self::get_instances(['catalogid' => $this->catalogid, 'setid' => $this->id], 'code');
                if (!empty($this->elements)) {
                    foreach ($this->elements as $elmid => $elm) {
                        $this->elements[$elmid]->catalog = $this->thecatalog;
                        $this->elementsbycode[$elm->code] = $elm;
                    }
                }
            }

            // Decode and expand handler params if any.
            if (!empty($this->record->handlerparams)) {
                $pairs = explode('&', $this->record->handlerparams);
                if (!empty($pairs)) {
                    foreach ($pairs as $p) {
                        if (strpos($p, '=') !== false) {
                            list($param, $value) = explode('=', $p);
                            $this->handlerparams[$param] = $value;
                        }
                    }
                }
            }

        } else {
            $this->record->name = get_string('newitem', 'local_shop');
            $this->record->description = '';
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->notes = '';
            $this->record->notesformat = FORMAT_HTML;
            $this->record->seoalias = '';
            $this->record->seokeywords = '';
            $this->record->seodescription = '';
            $this->record->seotitle = '';
            $this->record->eula = '';
            $this->record->eulaformat = FORMAT_HTML;
        }
    }

    /**
     * Returns the reference catalog for this item.
     */
    public function get_catalog() {
        return $this->thecatalog;
    }

    /**
     * get the accurate unit price (untaxed) against quantity ranges
     * @param int $q the quantity
     * @return number (untaxed price)
     */
    public function get_price($q) {
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

    /**
     * Searches a catalogitem instance that matches a idnumber 
     * @param string $idnumber The catalogitem idnumber, should be unique if defined.
     */
    public static function instance_by_idnumber($idnumber) {
        global $DB;

        if (empty($idnumber)) {
            return null;
        }

        $intanceid = $DB->get_field('local_shop_catalogitem', 'id', ['idnumber' => $idnumber]);
        if (!$intanceid) {
            return null;
        }

        return new CatalogItem($intanceid);
    }

    /**
     * Searches a catalogitem instance that matches a seoalias 
     * @param string $alias The catalogitem seoalias, should be unique if defined.
     */
    public static function instance_by_seoalias($alias) {
        global $DB;

        if (empty($alias)) {
            return null;
        }

        $intanceid = $DB->get_field('local_shop_catalogitem', 'id', ['seoalias' => $alias]);
        if (!$intanceid) {
            return null;
        }

        return new CatalogItem($intanceid);
    }

    /**
     * Searches a catalogitem instance that matches a idnumber 
     * @param string $idnumber The catalogiutem idnumber, should be unique if defined.
     * @param bool $equals If true, idnumber must equal the input, elsewhere, admits containing the input.
     */
    public static function instance_by_code($code) {
        global $DB;

        if (empty($code)) {
            return null;
        }

        $intanceid = $DB->get_field('local_shop_catalogitem', 'id', ['code' => $code]);
        if (!$intanceid) {
            return null;
        }

        return new CatalogItem($intanceid);
    }

    /**
     * Searches a catalogitem instance that matches a idnumber 
     * @param string $idnumber The catalogitem idnumber, should be unique if defined. Matches as prefix.
     * @param bool $equals If true, idnumber must equal the input, elsewhere, admits containing the input.
     * @return an array of CatalogItems objects.
     */
    public static function instances_by_idnumber($idnumberpattern) {
        global $DB;

        if (empty($idnumberpattern)) {
            return null;
        }

        $select = $DB->sql_like('idnumber', ':idnumberpattern');
        $params = ['idnumberpattern' => $idnumberpattern.'%'];
        $instancerecs = $DB->get_records_select('local_shop_catalogitem', $select, $params, 'shortname', 'id,code');
        if (!$instancerecs) {
            return [];
        }

        $items = [];
        foreach ($instancerecs as $instancerecid => $instance) {
            $item = new \local_shop\CatalogItem($instancerecid);
            $items[$instance->code] = $item;
        }

        return $items;
    }

    public function get_serialized_handlerparams() {
        return json_encode(@$this->handlerparams);
    }

    /**
     * The unit tax amount for this quantity of products.
     * @param int $q
     * @return number (tax amount)
     */
    public function get_tax($q = 1) {
        if ($this->taxcode && $this->tax) {
            return $this->tax;
        }
        $this->get_taxed_price($q); // Forces tax to calculate.
        return $this->tax;
    }

    public function get_printable_prices($taxed = false) {

        $prices = array();
        $key = (!@$this->record->range1) ? '0-' : "0-{$this->record->range1}";
        if ($taxed) {
            $prices[$key] = sprintf('%.2f', $this->get_taxed_price(0, $this->record->taxcode));
        } else {
            $prices[$key] = sprintf('%.2f', $this->record->price1);
        }

        for ($i = 1; $i < 5; $i++) {
            $j = $i + 1;
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

    /**
     * Gives the unit taxed price (may depend on quantity, if ranged princing)
     * @param int $q the ordered quantity
     * @param int $taxid taxid, if not set evaluates with the internal taxcode value.
     */
    public function get_taxed_price($q = 1, $taxid = 0) {
        global $DB;
        static $taxcache;

        if (empty($taxid)) {
            $taxid = $this->taxcode;
        }

        if ($taxid == 0) {
            return $this->get_price($q);
        }

        if (!isset($taxcache)) {
            $taxcache = array();
        }

        if (!array_key_exists($taxid, $taxcache)) {
            if ($taxcache[$taxid] = $DB->get_record('local_shop_tax', array('id' => $taxid))) {
                if (empty($taxcache[$taxid]->formula)) {
                    $taxcache[$taxid]->formula = '$ttc = $ht';
                }
            } else {
                return $this->get_price($q);
            }
        }

        $in['ht'] = $this->get_price($q);
        $in['tr'] = $taxcache[$taxid]->ratio;
        $result = evaluate(\core_text::strtolower($taxcache[$taxid]->formula).';', $in, 'ttc');
        $this->tax = $result['ttc'] - $in['ht'];
        return $result['ttc'];
    }

    // This will override existing elements.
    public function set_element($elm) {
        $this->elements[$elm->id] = $elm;
    }

    // This will fetch an element by code.
    public function get_element($code) {
        if (array_key_exists($code, $this->elements)) {
            return $this->elements[$code];
        } else {
            throw Exception('nosuchelement');
        }
    }

    /**
     * This will override existing elements
     */
    public function delete_element($elmid) {
        unset($this->elements[$elmid]);
    }

    /**
     *
     *
     */
    public function get_sales_unit_url() {
        global $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemunit', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemunit', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(),
                                                    $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
        } else {
            $url = $OUTPUT->image_url(current_language().'/one_unit', 'local_shop');
        }
        return $url;
    }

    /**
     *
     *
     */
    public function get_sales_ten_units_url() {
        global $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemtenunits', $this->id, $ignoredirs = true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemtenunits', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(),
                                                    $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
        } else {
            $url = $OUTPUT->image_url(current_language().'/ten_units', 'local_shop');
        }
        return $url;
    }

    /**
     *
     *
     */
    public function get_image_url() {

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemimage', $this->id, /* ignoredirs */ true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemimage', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(),
                                                    $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
            return $url;
        }
        return false;
    }

    /**
     *
     */
    public function get_thumb_url($nodefault = false) {
        global $OUTPUT;

        $context = \context_system::instance();

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemthumb', $this->id, /* ignoredirs */ true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemthumb', $this->id);
            $unitpix = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($unitpix->get_contextid(), $unitpix->get_component(), $unitpix->get_filearea(),
                                                    $unitpix->get_itemid(), $unitpix->get_filepath(), $unitpix->get_filename());
        } else {
            if ($nodefault) {
                // Use for bundles or sets.
                return false;
            }
            $url = $OUTPUT->image_url('defaultproduct', 'local_shop');
        }
        return $url;
    }

    /**
     * Gets back some information about handler and callable method for post prod operations
     * @param string $method the product method name
     * @return an array with an handler object instance and a callable method name
     */
    public function get_handler_info($method, $type = 'postprod') {
        global $CFG;

        if (!empty($this->isset)) {
            // Bundle or set.
            shop_debug_trace('Catalog item get handler info : Is a set or a bundle. No handler', SHOP_TRACE_DEBUG);
            return [null,null];
        }

        $handler = null;
        $methodname = null;

        // TODO : Rationalize .... there is probably only one case to process.
        if ($type == 'postprod') {

            $hashandler = $this->enablehandler;
            if (empty($hashandler)) {
                shop_debug_trace('Catalog item get handler info : Has no handler declared', SHOP_TRACE_DEBUG);
                return [null, null];
            }

            $h = $this->enablehandler;

            if (!file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$h.'/'.$h.'.class.php')) {
                throw new moodle_exception(get_string('errorbadhandler', 'local_shop', $h));
            }

            include_once($CFG->dirroot.'/local/shop/datahandling/handlers/'.$h.'/'.$h.'.class.php');

            $classname = 'shop_handler_'.$h;
            $handler = new $classname('');
        } else {
            $handler = $this->get_handler();
            if (is_object($handler)) {
                $classname = get_class($handler);
            } else {
                shop_debug_trace('Catalog item get handler info : No handler found', SHOP_TRACE_DEBUG);
                return [null, null];
            }
        }

        if (!empty($method)) {
            $methodname = $method;
            if (!empty($type)) {
                // Extend possible queries, Keep the alternative for older compatibility.
                $methodname = $type.'_'.$method;
            }
            if (!method_exists($classname, $methodname)) {
                if ($type == 'postprod') {
                    throw new moodle_exception(get_string('errorunimplementedhandlermethod', 'local_shop', $methodname));
                } else {
                    shop_debug_trace('Catalog item get handler info : Info type not yet supported', SHOP_TRACE_DEBUG);
                    return [null, null];
                }
            }
        }

        return array($handler, $methodname);
    }

    /**
     * get info out of production data (in product)
     * @return an object
     */
    public function extract_production_data() {

        $info = new \StdClass();

        $productiondata = $this->productiondata;

        if (!empty($productiondata)) {
            if ($pairs = explode('&', $this->productiondata)) {
                foreach ($pairs as $pair) {
                    // Affectation may be empty.
                    $pair = explode('=', $pair);
                    $info->{$pair[0]} = @$pair[1];
                }
            }
        }

        return $info;
    }

    /**
     * Gets a suitable handler object for this catalog item
     */
    public function get_handler() {
        global $CFG;

        $enablehandler = $this->enablehandler;
        $handlerlabel = $this->shortname;

        $handler = null;

        if (empty($enablehandler)) {
            return false;
        } else if ($enablehandler == SPECIFIC_HANDLER) {
            $thehandler = $this->itemcode;
        } else {
            $thehandler = $enablehandler;
        }

        if (!empty($thehandler) &&
                file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php')) {
            include_once($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php');
            $classtype = "shop_handler_{$thehandler}";
            $handler = new $classtype($handlerlabel);
        }

        return $handler;
    }

    public function get_handler_params() {
        return $this->handlerparams;
    }

    public function get_shippings() {
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

    public function get_shipping_zones() {
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

    public function unlink() {
        global $DB;

        if (!$this->isset) {
            // Unlink self from set or bundle.
            $DB->set_field('local_shop_catalogitem', 'setid', 0, ['id' => $this->id]);
        } else {
            // Unlink all linked elements.
            if (!empty($this->elements)) {
                foreach ($this->elements as $ci) {
                    $DB->set_field('local_shop_catalogitem', 'setid', 0, ['id' => $ci->id]);
                }
            }
        }
    }

    public function has_leaflet() {
        $context = \context_system::instance();

        $fs = get_file_storage();

        return !$fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $this->id);
    }

    /**
     * Checks availability against handler rules.
     */
    public function check_availability() {

        $config = get_config('local_shop');

        // Check if product has handler and is available.
        if ($this->enablehandler) {
            $handler = $this->get_handler();
            if ($handler && !$handler->is_available($this)) {
                /*
                 * TODO : defer this check at a shop instance level, using
                 * global config key as default setting.
                 */
                if ($config->hideproductswhennotavailable) {
                    return;
                } else {
                    $this->available = false;
                }
            }
        }
        return true;
    }

    public function get_leaflet_url() {
        global $OUTPUT;

        $context = \context_system::instance();

        $url = null;

        $fs = get_file_storage();
        if (!$fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $this->id, /* $ignoredirs */ true)) {
            $files = $fs->get_area_files($context->id, 'local_shop', 'catalogitemleaflet', $this->id);
            $leafletfile = array_pop($files);
            $url = \moodle_url::make_pluginfile_url($leafletfile->get_contextid(), $leafletfile->get_component(),
                                                    $leafletfile->get_filearea(), $leafletfile->get_itemid(),
                                                    $leafletfile->get_filepath(), $leafletfile->get_filename());
        }
        return $url;
    }

    /**
     * Apply() overrides the current instance with the elements of the override.
     * Overriden attributes are choosen to address the master question : Why using
     * slave catalogs: master usecase is to internationalize or change of some commercial
     * values for a special country/region.
     * Variant should not alter the effective nature of the product, nor technical definition.
     *
     * TODO : Check if still usefull
     */
    public function apply(CatalogItem $override) {

        // Override some attributes (textual).
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
            foreach ($override->elements as $ovelm) {
                if (array_key_exists($ovelm->code, $this->elementsbycode)) {
                    $this->elements[$this->elementsbycode[$ovelm->code]->id]->apply($ovelm);
                } else {
                    /*
                     * This should usually not happen as overrides should always be local copies of master records.
                     * This might accidentally happen when trying to apply the wrong way (f.e. master on local)
                     */
                    throw new \coding_exception('Unexpected unmatching override '.$ovelm->code.' in CatalogItem '.$this->id);
                }
            }
        }

        return $this;
    }

    public function remove_content() {
        if (!$this->elements) {
            $this->elements = self::get_instances(array('setid' => $this->id));
        }

        foreach ($this->elements as $elm) {
            $elm->unlink();
        }
    }

    /**
     * Deletes the catalogitem releasing elements as standard products.
     */
    public function delete() {
        $this->remove_content();

        parent::delete();
    }

    /**
     * Delete complete structure including elements
     */
    public function fulldelete() {
        if ($this->id && !$this->elements) {
            $this->elements = self::get_instances(array('setid' => $this->id));
        }

        foreach ($this->elements as $elm) {
            $elm->delete();
        }

        parent::delete();
    }

    public function clone_instance($inset = false) {
        global $DB;

        $oldid = $this->id;

        $this->record->id = 0;
        $this->record->code .= '1';
        if (!$inset) {
            $this->record->setid = 0; // Cloned is a real product.
        } else {
            $this->record->setid = $inset; // Should give the new set.
        }

        $params = ['catalogid' => $this->catalogid, 'code' => $this->record->code];
        while ($DB->record_exists('local_shop_catalogitem', $params)) {
            $this->record->code .= '1';
            $params = ['catalogid' => $this->catalogid, 'code' => $this->record->code];
        }

        $this->record->shortname = strtolower($this->record->code);
        $this->record->shortname = str_replace(' ', '', $this->record->shortname);

        $this->save();

        // Clone all attached files.
        $fs = get_file_storage();
        $context = \context_system::instance();
        $files = $DB->get_records('files', ['contextid' => $context->id, 'component' => 'local_shop', 'itemid' => $oldid]);
        foreach ($files as $f) {
            if ($f->filename == '.') {
                continue; // Discard directories.
            }
            $oldfile = $fs->get_file_instance($f);
            $newfile = new StdClass;
            $newfile->contextid = $context->id;
            $newfile->component = 'local_shop';
            $newfile->filearea = $f->filearea;
            $newfile->itemid = $this->id;
            $newfile->filepath = $f->filepath;
            $newfile->filename = $f->filename;
            $fs->create_file_from_storedfile($newfile, $oldfile);
        }
    }

    public function export_to_ws($q, $withsubs) {
        $export = new StdClass;

        $export->id = $this->record->id;
        $export->catalogid = $this->record->catalogid;
        $export->categoryid = $this->record->categoryid;
        $export->code = $this->record->code;
        $export->shortname = $this->record->shortname;
        $export->name = format_string($this->record->name);
        $export->description = format_text($this->record->description, $this->record->descriptionformat);
        $export->eulas = format_text($this->record->eulas, $this->record->eulasformat);
        $export->notes = format_text($this->record->notes, $this->record->notesformat);
        switch ($this->record->isset) {
            case PRODUCT_STANDALONE:
                $export->type = 'plain';
                break;

            case PRODUCT_SET:
                $export->type = 'set';
                break;

            case PRODUCT_BUNDLE:
                $export->type = 'bundle';
                break;
        }

        $export->status = $this->status;
        $export->unitcost = $this->get_price($q);
        $export->tax = $this->get_tax($q);
        $export->requireddata = $this->record->requireddata;
        $export->leafleturl = ''.$this->get_leaflet_url();
        $export->thumburl = ''.$this->get_thumb_url();
<<<<<<< HEAD
        $export->imageurl =  ''.$this->get_image_url();

        if ($withsubs) {
            
        }
=======
        $export->imageurl = ''.$this->get_image_url();
>>>>>>> MOODLE_40_STABLE

        return $export;
    }

    public static function count($filter) {
        return parent::_count(self::$table, $filter);
    }

    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances_menu($filter = [], $order = '') {
        return parent::_get_instances_menu(self::$table, $filter, $order, "CONCAT(code, ' ', name)");
    }

    public static function search($by, $arg, $searchscope = null) {
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

        $params = array();
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

            $results = $DB->get_records_sql($sql, $params);
        }

        return $results;
    }

    /**
     * computes a shortname for javascript purpose, ensuring it is unique
     * and has no harmfull chars for token application.
     * @param object $formdata data received from edition forms.
     */
    public static function compute_item_shortname(&$formdata) {
        global $DB;

        $shortname = $formdata->code;
        $shortname = strtolower(str_replace(' ', '_', $shortname));

        return $shortname;
    }
}