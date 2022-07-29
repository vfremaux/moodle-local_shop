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
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use \local_shop\CatalogItem;
use \local_shop\Catalog;

class product_controller {

    protected $data;

    protected $thecatalog;

    protected $received = false;

    protected $mform;

    public function __construct($thecatalog) {
        $this->thecatalog = $thecatalog;
    }

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function shoudl get them from request
     */
    public function receive($cmd, $data = null, $mform = null) {

        $this->mform = $mform;
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
            $this->data->categoryid = optional_param('categoryid', 0, PARAM_INT);
        }

        switch ($cmd) {
            case 'edit':
                // Rely on feeding directly with the data argument.
                break;

            case 'delete' :
                $this->data->itemid = required_param('itemid', PARAM_INT);
                break;

            case 'deleteselection' :
                $this->data->itemids = required_param_array('itemids', PARAM_INT);
                break;

            case 'unlink' :
                $this->data->itemid = required_param('itemid', PARAM_INT);
                break;

            case 'toset':
            case 'tobundle':
            case 'clone':
                // Item id will be given as the remote master id (no local override).
                $this->data->itemid = required_param('itemid', PARAM_INT);
                break;

            case 'makecopy':
                // Item id will be given as the remote master id (no local override).
                $this->data->masteritemid = required_param('itemid', PARAM_INT);
                break;

            case 'freecopy' :
                $this->data->localitemid = required_param('itemid', PARAM_INT);
                break;

            case 'search' :
                $this->data->by = required_param('by', PARAM_TEXT);
                $this->data->code = optional_param('code', '', PARAM_TEXT);
                $this->data->shortname = optional_param('shortname', '', PARAM_TEXT);
                $this->data->name = optional_param('name', '', PARAM_TEXT);
        }

        // Get the shop id by 'id' backuped by 'shopid';
        $this->data->shopid = optional_param('id', optional_param('shopid', 0, PARAM_INT), PARAM_INT);

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB, $USER, $CFG;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        switch ($cmd) {

            case 'edit': {

                $this->data->id = @$this->data->itemid;
                $this->data->catalogid = $this->thecatalog->id;

                $this->data->description = $this->data->description_editor['text'];
                $this->data->descriptionformat = $this->data->description_editor['format'];

                $this->data->notes = $this->data->notes_editor['text'];
                $this->data->notesformat = $this->data->notes_editor['format'];

                $this->data->eula = $this->data->eula_editor['text'];
                $this->data->eulaformat = $this->data->eula_editor['format'];

                if (empty($this->data->renewable)) {
                    $this->data->renewable = 0;
                }

                // Check product handler related  constraints
                if (!empty($this->data->enablehandler)) {
                    $thehandler = $this->data->enablehandler;
                    if (!empty($thehandler) &&
                            file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php')) {
                        include_once($CFG->dirroot.'/local/shop/datahandling/handlers/'.$thehandler.'/'.$thehandler.'.class.php');
                        $classtype = "shop_handler_{$thehandler}";
                        $handler = new $classtype(null);

                        if ($max = $handler->get_max_quantity()) {
                            // force max quantity on the product, due to associated handler.
                            $this->data->maxdeliveryquant = $max;
                        }
                    }
                }

                if (empty($this->data->itemid)) {

                    $this->data->shortname = CatalogItem::compute_item_shortname($this->data);

                    $this->data->id = $DB->insert_record('local_shop_catalogitem', $this->data);

                    // We have items in the set. update relevant products.
                    if (!empty($this->data->productsinset) && is_array($this->data->productsinset)) {
                        foreach ($this->productsinset as $productid) {
                            $record = new \StdClass;
                            $record->id = $productid;
                            $record->setid = $this->data->id;
                            $DB->update_record('local_shop_catalogitem', $record);
                        }
                    }
                    unset($this->data->productsinset); // Clean the record.
                    // If slave catalogue must insert a master copy.
                    if ($this->thecatalog->isslave) {
                        $this->data->catalogid = $this->thecatalog->groupid;
                        $this->data->id = $DB->insert_record('local_shop_catalogitem', $this->data);
                    }
                } else {
                    unset($this->data->itemid);

                    // Recompute an updated shortname.
                    $this->data->shortname = CatalogItem::compute_item_shortname($this->data);

                    $DB->update_record('local_shop_catalogitem', $this->data);
                }

                $context = \context_system::instance();

                // Process text fields from editors.
                if ($this->mform) {
                    // We do not have form in unit tests.
                    $draftideditor = file_get_submitted_draft_itemid('description_editor');
                    $this->data->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop',
                                                                          'catalogitemdescription', $this->data->id,
                                                                          array('subdirs' => true), $this->data->description);
                    $this->data = file_postupdate_standard_editor($this->data, 'description', $this->mform->editoroptions, $context, 'local_shop',
                                                            'catalogitemdescription', $this->data->id);

                    $draftideditor = file_get_submitted_draft_itemid('notes_editor');
                    $this->data->notes = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemnotes',
                                                              $this->data->id, array('subdirs' => true), $this->data->notes);
                    $this->data = file_postupdate_standard_editor($this->data, 'notes', $this->mform->editoroptions, $context, 'local_shop',
                                                            'catalogitemnotes', $this->data->id);

                    $draftideditor = file_get_submitted_draft_itemid('eula_editor');
                    $this->data->eula = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemeula',
                                                             $this->data->id, array('subdirs' => true), $this->data->eula);
                    $this->data = file_postupdate_standard_editor($this->data, 'eula', $this->mform->editoroptions, $context, 'local_shop',
                                                            'catalogitemeula', $this->data->id);

                    $usercontext = \context_user::instance($USER->id);
                    shop_products_process_files($this->data, $context, $usercontext);

                    // Post update record in DB.
                    $DB->update_record('local_shop_catalogitem', $this->data);
                }

                return new CatalogItem($this->data->id);
            }

            case 'delete': {

                try {
                    $theitem = new CatalogItem($this->data->itemid);

                    // If catalog is not independant, all copies should be removed.
                    if ($this->thecatalog->ismaster) {
                        $slaves = $this->thecatalog->get_slaves();
                        foreach ($slaves as $s) {
                            if ($clone = $s->get_product_by_code($theitem->code)) {
                                $clone->fulldelete();
                            }
                        }
                    }
                    $theitem->fulldelete();
                } catch (Exception $e) {
                    // Silently do nothing.
                    assert(1);
                }
                break;
            }

            /* ***** We delete a set of product ***** */
            case 'deleteselection': {

                foreach ($this->data->itemids as $itemid) {
                    try {
                        $theitem = new CatalogItem($itemid);

                        // If catalog is not independant, all copies should be removed.
                        if ($this->thecatalog->ismaster) {
                            $slaves = $this->thecatalog->get_slaves();
                            foreach ($slaves as $s) {
                                if ($clone = $s->get_product_by_code($theitem->code)) {
                                    $clone->fulldelete();
                                }
                            }
                        }
                        $theitem->fulldelete();
                    } catch (Exception $e) {
                        // Silently do nothing.
                        assert(1);
                    }
                }
                break;
            }

            /* ***** We unlink a linked product ***** */
            case 'unlink': {
                /*
                 * Action depends on product type : for real products, unklinks from bundle or set.
                 * for bundles or sets, unlink all internal elements
                 */
                try {
                    $item = new CatalogItem($this->data->itemid);
                    $item->unlink();
                } catch (Exception $ex) {
                    assert(1);
                }
                break;
            }

            /* ****** Clone a product or a set/bundle element as a product ***** */
            case 'clone': {
                $original = new CatalogItem($this->data->itemid);
                $original->clone_instance();
                $params = ['view' => 'viewAllProducts', 'categoryid' => $this->data->categoryid, 'shopid' => $this->data->shopid];
                redirect(new \moodle_url('/local/shop/products/view.php', $params));
            }

            /* ****** converts a product into a set ***** */
            case 'toset': {
                $original = new CatalogItem($this->data->itemid);
                $original->setid = 0;
                $original->enablehandler = 0;
                $original->handlerparams = '';
                $original->isset = PRODUCT_SET;
                $original->save();
                $params = ['view' => 'viewAllProducts', 'categoryid' => $this->data->categoryid, 'shopid' => $this->data->shopid];
                redirect(new \moodle_url('/local/shop/products/view.php', $params));
            }

            /* ****** converts a product into a bundle ***** */
            case 'tobundle': {
                $original = new CatalogItem($this->data->itemid);
                $original->setid = 0;
                $original->enablehandler = 0;
                $original->handlerparams = '';
                $original->isset = PRODUCT_BUNDLE;
                $original->save();
                $params = ['view' => 'viewAllProducts', 'categoryid' => $this->data->categoryid, 'shopid' => $this->data->shopid];
                redirect(new \moodle_url('/local/shop/products/view.php', $params));
            }

            /* ****** converts a product into a set ***** */
            case 'toproduct': {
                $original = new CatalogItem($this->data->itemid);
                $params = ['view' => 'viewAllProducts', 'categoryid' => $this->data->categoryid, 'shopid' => $this->data->shopid];
                redirect(new \moodle_url('/local/shop/products/view.php', $params));
            }

            /* ***** make a local physical clone of the master product in this slave catalog ***** */
            case 'makecopy': {
                // Get source item in master catalog.
                $item = new CatalogItem($this->data->masteritemid);
                $result = CatalogItem::get_instances(array('code' => $item->code, 'catalogid' => $this->thecatalog->id));
                if (empty($result)) {
                    $item->catalogid = $this->thecatalog->id; // Binding to local catalog.
                    $item->id = 0; // Ensure new record.
                    $item->save();
                }
                /*
                 * Note about documents handling : when cloning a slave copy, no documents are cloned. Image and thumb will be
                 * reused from the master pieace, while a new leaflet should be uploaded for the clone. f.e. translated leaflet.
                 */
                 break;
            }

            /* **** Delete the local copy **** */
            case 'freecopy': {
                $localitem = new CatalogItem($this->data->localitemid);
                $localitem->delete();
                break;
            }

            /* ***** searches and filters the product list ***** */
            case 'search': {
                return CatalogItem::search($this->data->by, $this->data->code, $this->data->shortname, $this->data->name);
            }
        }
    }

    public static function info() {
        return array(
            'delete' => array('items' => 'Array of numeric IDs'),
            'deleteset' => array('setid' => 'Numeric ID'),
            'edit' => array(
                'code' => 'token as String',
                'name' => 'String',
                'description_editor' => 'Array of text|format|itemid',
                'userid' => 'numeric ID',
                'status' => 'One of AVAILABLE',
                'price1' => 'Number',
                'from1' => 'Integer',
                'range1' => 'Integer',
                'price2' => 'Number',
                'range2' => 'Integer',
                'price3' => 'Number',
                'range3' => 'Integer',
                'price4' => 'Number',
                'range4' => 'Integer',
                'price5' => 'Number',
                'taxcode' => 'Numeric ID of an existing tax code',
                'stock' => 'Integer',
                'sold' => 'Integer',
                'maxdeliveryquant' => 'Integer',
                'onlyforloggedin' => '-1, 0 (indifferent), 1 (loggedin), or 2 (referenced customers)',
                'password' => 'String or empty',
                'categoryid' => 'Numeric ID of a category',
                'setid' => 'Numeric ID of a set',
                'showsnameinset' => 'Boolean as 0 or 1',
                'showsdescriptioninset' => 'Boolean as 0 or 1',
                'eula_editor' => 'Array of text|format|itemid',
                'notes_editor' => 'Array of text|format|itemid',
                'requireddata' => 'JSONinifed structure',
                'enablehandler' => 'String, handler name',
                'handlerparams' => 'param,value pairs string',
                'quantaddressesusers' => '0 (no), 1 (one seat per trans), 2 (yes)',
                'renewable' => 'Boolean as 0,2'
            ),
            'unlink' => array('itemid' => 'Numeric ID pointing a catalog item ID'),
            'clone' => array('itemid' => 'Numeric ID pointing a catalog item ID'),
            'makecopy' => array('itemid' => 'Numeric ID pointing a catalog item ID'),
            'freecopy' => array('itemid' => 'Numeric ID pointing a catalog item ID'),
            'search' => array(
                'by' => 'field name as  code, shortname or name',
                'code' => 'String',
                'name' => 'String',
                'shortname' => 'String')
        );
    }
}