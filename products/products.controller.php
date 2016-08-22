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

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class product_controller {

    protected $data;

    protected $thecatalogue;

    function __construct($theCatalogue) {
        $this->thecatalogue = $theCatalogue;
    }

    public function receive($cmd, $data = array()) {

        if (!empty($data)) {
            $this->data = (object)$data;
        }

        $data = new StdClass();

        switch ($cmd) {
            case 'delete' :
                $this->data->productid = required_param('items', PARAM_INT);
                break;
            case 'deleteset' :
                $this->data->setid = required_param('setid', PARAM_INT);
                break;
            case 'unlinkproduct' :
                $this->data->itemid = required_param('itemid', PARAM_INT);
                break;
            case 'makecopy':
                $this->data->masteritemid = required_param('itemid'); // Item id will be given as the remote master id (no local override).
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
    }

    public function process($cmd) {

        $this->prepare($cmd);

        if ($cmd == 'delete') {
            $productidlist = $this->data->productid; // for unity operations
            $DB->delete_records_select('local_shop_catalogitem', " id IN ('$productidlist') ");
            // catalog is not independant, delete in all group (by getting product code)
            if ($this->thecatalog->groupid != '') {
                // get product code so that all clones Id can be found
                $theCode = $DB->get_field('local_shop_catalogitem', 'code', array('id' => $this->data->productid));

                $groupid = $this->thecatalog->groupid;

                $sql = "
                   SELECT
                      ci.id,
                      ci.id
                   FROM
                      {local_shop_catalogitem} as ci,
                      {local_shop_catalog} as c
                   WHERE
                      c.id = ci.catalogid AND
                      ci.code = '$theCode' AND
                      c.groupid = '$groupid'
                ";
                if ($products = $DB->get_records_sql_menu($sql)) {
                    $productidlist = implode("','", array_values($products));
                } else {
                    $productidlist = '';
                }
            }
            $relatedids = implode("','", array_keys($theCatalog->getGroupMembers($this->thecatalog->groupid))); // this is as a security
            $DB->delete_records_select('local_shop_catalogitem', " id IN ('$productidlist') AND catalogid IN ('$relatedids') ");
        };

        if ($cmd == 'deleteset') {
            $setid = required_param('setid', PARAM_INT);

            // If catalog is not independant, all copies should be removed.
            $setidlist = '';
            if ($this->thecatalog->groupid != '') {

                // get setcode by Id
                $item = new CatalogItem($setid);
                $item->fulldelete();
            }
        }

        if ($cmd == 'unlinkproduct') {
            $itemid = required_param('itemid', PARAM_INT);
            $item = new CatalogItem($itemid);
            $item->unlink();
        }

        /** ***** make a local physical clone of the master product in this slave catalog **** **/
        if ($cmd == 'makecopy') {
            $masteritemid = required_param('itemid'); // Item id will be given as the remote master id (no local override).

            // get source item in master catalog
            $masterCatalog = new Catalog($this->thecatalog->groupid);
            $item = new CatalogItem($masteritemid);
            $item->catalogid = $this->thecatalog->id; // Binding to local catalog
            $item->id = 0; // Ensure new record
            $item->save();
            // Note about documents handling : when cloning a slave copy, no documents are cloned. Image and thumb will be
            // reused from the master pieace, while a new leaflet should be uploaded for the clone. f.e. translated leaflet.
        }

        /** **** Delete the local copy **** **/
        if ($cmd == 'freecopy') {
            $localitemid = required_param('itemid', PARAM_INT);
            $localitem = new CatalogItem($localitemid);
            $localitem->delete();
        }

        /** ***** searches and filters the product list ***** **/
        if ($cmd == 'search') {
            $error = false;
            $by = required_param('by', PARAM_TEXT);
            $code = optional_param('code', '', PARAM_TEXT);
            $shortname = optional_param('shortname', '', PARAM_TEXT);
            $name = optional_param('name', '', PARAM_TEXT);

            $results = CatalogItem::search($by, $code, $shortname, $name);
        }
    }
}