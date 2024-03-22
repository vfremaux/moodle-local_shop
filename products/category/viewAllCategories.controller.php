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

require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');

use local_shop\Category;
use moodle_exception;

class category_controller {

    protected $data;

    protected $received;

    protected $mform;

    protected $thecatalog;

    public function __construct($thecatalog) {
        $this->thecatalog = $thecatalog;
    }

    public function receive($cmd, $data = null, $mform = null) {

        $this->mform = $mform;

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'delete':
                $this->data->categoryids = required_param_array('categoryids', PARAM_INT);
                break;

            case 'up':
            case 'down':
            case 'show':
            case 'hide':
                $this->data->cid = required_param('categoryid', PARAM_INT);
                break;

            case 'edit':
                // Get data from $data attribute.
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        // Delete a category.
        if ($cmd == 'delete') {
            foreach ($this->data->categoryids as $cid) {
                $deleted = $DB->get_record('local_shop_catalogcategory', array('id' => $cid));
                $DB->delete_records('local_shop_catalogcategory', array('id' => $cid));
                $selectcontext = array('catalogid' => $deleted->catalogid, 'parentid' => $deleted->parentid);
                shop_list_reorder($selectcontext, 'local_shop_catalogcategory');
            }
        } else if ($cmd == 'up') {
            // Raises a question in the list ***************.
            $parentid = $DB->get_field('local_shop_catalogcategory', 'parentid', array('id' => $this->data->cid));
            $selectcontext = array('catalogid' => $this->thecatalog->id, 'parentid' => $parentid);
            shop_list_up($selectcontext, $this->data->cid, 'local_shop_catalogcategory');

        } else if ($cmd == 'down') {
            // Lowers a question in the list ****************.
            $parentid = $DB->get_field('local_shop_catalogcategory', 'parentid', array('id' => $this->data->cid));
            $selectcontext = array('catalogid' => $this->thecatalog->id, 'parentid' => $parentid);
            shop_list_down($selectcontext, $this->data->cid, 'local_shop_catalogcategory');

        } else if ($cmd == 'show') {
            // Show a category ******************************.
            $DB->set_field('local_shop_catalogcategory', 'visible', 1, array('id' => $this->data->cid));

        } else if ($cmd == 'hide') {
            // Hide a category ******************************.
            $DB->set_field('local_shop_catalogcategory', 'visible', 0, array('id' => $this->data->cid));

        } else if ($cmd == 'edit') {
            $category = $this->data;

            if (!isset($category->visible)) {
                $category->visible = 0;
            }

            $category->catalogid = $this->thecatalog->id;

            $category->description = $category->description_editor['text'];
            $category->descriptionformat = 0 + $category->description_editor['format'];

            if (empty($category->categoryid)) {
                $params = array('catalogid' => $this->thecatalog->id, 'parentid' => @$category->parentid);
                $maxorder = $DB->get_field('local_shop_catalogcategory', 'MAX(sortorder)', $params);
                $category->sortorder = $maxorder + 1;
                if (!$category->id = $DB->insert_record('local_shop_catalogcategory', $category)) {
                    throw new moodle_exception(get_string('erroraddcategory', 'local_shop'));
                }
                // We have items in the set. update relevant products.
                $productsinset = optional_param('productsinset', array(), PARAM_INT);
                if (is_array($productsinset)) {
                    foreach ($productsinset as $productid) {
                        $record = new \StdClass;
                        $record->id = $productid;
                        $record->setid = $category->id;
                        $DB->update_record('local_shop_catalogitem', $record);
                    }
                }

                // If slave catalogue must insert a master copy.
                if ($this->thecatalog->isslave) {
                    $category->catalogid = $thecatalog->groupid;
                    $DB->insert_record('local_shop_catalogcategory', $category);
                }
            } else {
                $category->id = $category->categoryid;
                unset($category->categoryid);
                $DB->update_record('local_shop_catalogcategory', $category);
            }

            $context = \context_system::instance();

            // Process text fields from editors.
            if ($this->mform) {
                // $options = array('subdirs' => true);

                // We do not have form runnig tests.
                $draftideditor = file_get_submitted_draft_itemid('description_editor');
                $category->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'categorydescription',
                                                                $category->id, $this->mform->editoroptions, $category->description);
                $category = file_postupdate_standard_editor($category, 'description', $this->mform->editoroptions, $context,
                                                            'local_shop', 'categorydescription', $category->id);

                $DB->update_record('local_shop_catalogcategory', $category);
            }

            return new Category($category->id);
        }
    }

    public static function info() {
        return array(
            'delete' => array('categoryids' => 'Array of numeric IDs'),
            'up' => array('categoryid' => 'Numeric ID pointing a Category'),
            'down' => array('categoryid' => 'Numeric ID pointing a Category'),
            'show' => array('categoryid' => 'Numeric ID pointing a Category'),
            'hide' => array('categoryid' => 'Numeric ID pointing a Category'),
            'edit' => array(
                'catalogid' => 'ID of product catalog as Integer',
                'name' => 'String',
                'parentid' => 'Numeric ID pointing another category',
                'description_editor' => 'Array of text|format|itemid',
                'visible' => 'Boolean'),
        );
    }
}