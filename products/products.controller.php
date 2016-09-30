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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use \local_shop\CatalogItem;
use \local_shop\Catalog;

class product_controller {

    protected $data;

    protected $thecatalog;

    protected $received = false;

    public function __construct($thecatalog) {
        $this->thecatalog = $thecatalog;
    }

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the function shoudl get them from request
     */
    public function receive($cmd, $data = array()) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new StdClass;
        }

        switch ($cmd) {
            case 'delete' :
                $this->data->productids = required_param_array('items', PARAM_INT);
                break;
            case 'deleteset' :
                $this->data->setid = required_param('setid', PARAM_INT);
                break;
            case 'unlink' :
                $this->data->itemid = required_param('itemid', PARAM_INT);
                break;
            case 'clone':
                $this->data->itemid = required_param('itemid', PARAM_INT); // Item id will be given as the remote master id (no local override).
                break;
            case 'makecopy':
                $this->data->masteritemid = required_param('itemid', PARAM_INT); // Item id will be given as the remote master id (no local override).
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

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        if ($cmd == 'delete') {
            foreach ($this->data->productids as $ciid) {
                $theitem = new CatalogItem($ciid);

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
            }
        }

        /** ***** We unlink a linked product ***** **/
        if ($cmd == 'unlink') {
            $item = new CatalogItem($this->data->itemid);
            $item->unlink();
        }

        /** ****** Clone a product or a set/bundle element as a product ***** **/
        if ($cmd == 'clone') {
            $original = new CatalogItem($this->data->itemid);
            $original->clone_instance();
            redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
        }

        /** ***** make a local physical clone of the master product in this slave catalog ***** **/
        if ($cmd == 'makecopy') {
            // Get source item in master catalog.
            $masterCatalog = new Catalog($this->thecatalog->groupid);
            $item = new CatalogItem($this->data->masteritemid);
            $result = CatalogItem::get_instances(array('code' => $item->code, 'catalogid' => $this->thecatalog->id));
            if (empty($result)) {
                $item->catalogid = $this->thecatalog->id; // Binding to local catalog
                $item->id = 0; // Ensure new record.
                $item->save();
            }
            /*
             * Note about documents handling : when cloning a slave copy, no documents are cloned. Image and thumb will be
             * reused from the master pieace, while a new leaflet should be uploaded for the clone. f.e. translated leaflet.
             */
        }

        /** **** Delete the local copy **** **/
        if ($cmd == 'freecopy') {
            $localitem = new CatalogItem($this->data->localitemid);
            $localitem->delete();
        }

        /** ***** searches and filters the product list ***** **/
        if ($cmd == 'search') {
            $error = false;

            $results = CatalogItem::search($this->data->by, $this->data->code, $this->data->shortname, $this->data->name);
        }
    }
}