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

abstract class shop_handler{

    public $productlabel;

    public function __construct($label) {
        $this->productlabel = $label;
    }

    public function get_name() {
        $name = str_replace('shop_handler_', '', get_class($this));
        return $name;
    }

    public function supports() {
        return PROVIDING_BOTH;
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public abstract function produce_prepay(&$data);

    /**
     * What is happening after it has been actually paied out, interactively
     * or as result of a delayed sales administration action.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public abstract function produce_postpay(&$data);

    /**
     * Check wether the product is available for a particular context, user.
     * Some product handlers may know if the current user has already purchased
     * this product and cannot purchase it twice, or is NOT in condition to purchase it.
     * @param object $catalogitem a Catalog item object with all parameters thzt is using
     * this handler.
     * @return boolean true or false
     */
    public function is_available(&$catalogitem) {
        return true;
    }

    /**
     * Check wether the product has a max quantity per transaction if this handler is used.
     * Some product handlers may not be able to proceed a multiple quantity purchase. this
     * will override any product definition.
     * @return integer. 0 if no limit.
     */
    public function get_max_quantity() {
        return 0;
    }

    /*
     * When implemented, the cron task for this handler will be run on shop cron
     * cron can be used to notify users for end of product life, user role unassigns etc.
     */

    /**
     * What should be done to validate customer required data. Validation is addressing same rules
     * for whatever fieldname required by the handler.
     * @param string $itemname the product class (catalogitem) to validate for. Needed to key error output.
     * @param string $field the fieldname to validate for
     * @param int $instance the instance to validate for. Needed to key the error output.
     * @param string $value the value to validate
     * @param arrayref $errors, an error array to be field with all encountered errors. Keys of the
     * array are [catalogitem][fieldname][instanceix].
     * @return false if not validated
     */
    public function validate_required_data($itemname, $field, $instance, $value, &$errors) {
        return true;
    }

    /**
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     */
    public function display_product_infos($pid, $pinfo) {
        // Do nothing.
        return;
    }

    public function display_product_acions() {
        // Do nothing.
        return;
    }

    protected function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        if (!isset($data->actionparams['customersupport'])) {
            $warnings[$data->code][] = get_string('warningcustomersupportcoursedefaultstosettings', 'local_shop');
            $data->actionparams['customersupport'] = $data->defaultcustomersupportcourse;
        }

        if (empty($data->actionparams['customersupport'])) {
            $warnings[$data->code][] = get_string('warningnocustomersupportcourse', 'local_shop');
            $data->actionparams['customersupport'] = $data->defaultcustomersupportcourse;
        } else {
            if (is_numeric($data->actionparams['customersupport'])) {
                if (!$course = $DB->get_record('course', array('id' => $data->actionparams['customersupport']))) {
                    $errors[$data->code][] = get_string('errornocustomersupportcourse', 'local_shop');
                }
            } else {
                if (!$course = $DB->get_record('course', array('shortname' => $data->actionparams['customersupport']))) {
                    $errors[$data->code][] = get_string('errornocustomersupportcourse', 'local_shop');
                }
            }
        }
    }
}