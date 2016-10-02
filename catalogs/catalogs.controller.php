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
 * Controller for catalogs.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\catalogs;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
use local_shop\Catalog;

// Note that other use cases are handled by the edit_catalogue.php script.

class catalog_controller {

    public function receive($cmd, $data = array()) {

        if (!empty($data)) {
            $this->data = (object)$data;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'deletecatalog':
                $this->data->catalogid = required_param('catalogid', PARAM_INT);
                break;
        }
    }

    public function process($cmd) {
        global $DB;

        if ($cmd == 'deletecatalog') {
            $catalogidlist = $this->data->catalogid;
            // If master catalog, must delete all slaves.
            $thecatalog = new Catalog($this->data->catalogid);
            if ($thecatalog->ismaster) {
                $catalogids = $DB->get_records_select_menu('local_shop_catalog', " groupid = '{$this->data->catalogid}' ", 'id', 'id,name');
                $catalogidlist = implode("','", array_keys($catalogids));
            }
            // Deletes products entries in candidate catalogs.
            $DB->delete_records_select('local_shop_catalogitem', " catalogid IN ('$catalogidlist') ");
            $DB->delete_records_select('local_shop_catalogcategory', " catalogid IN ('$catalogidlist') ");
            $DB->delete_records_select('local_shop_catalog', " id IN ('$catalogidlist') ");

            redirect(new \moodle_url('/local/shop/index.php'));
        }
    }
}