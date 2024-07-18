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
 * Base shop handler implementation
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_shop\Product;
use local_shop\CatalogItem;

/**
 * Shop handler abstract class.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
abstract class shop_handler {

    /**
     * @var string
     */
    public $productlabel;

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->productlabel = $label;
    }

    /**
     * Get the handler name
     */
    public function get_name() {
        $name = str_replace('shop_handler_', '', get_class($this));
        return $name;
    }

    /**
     * Which users it can serve
     */
    public function supports() {
        return PROVIDING_BOTH;
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    abstract public function produce_prepay(&$data, &$errorstatus);

    /**
     * What is happening after it has been actually paied out, interactively
     * or as result of a delayed sales administration action.
     * @param objectref &$data a bill item (real or simulated).
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    abstract public function produce_postpay(&$data);

    /**
     * Check wether the product is available for a particular context, user.
     * Some product handlers may know if the current user has already purchased
     * this product and cannot purchase it twice, or is NOT in condition to purchase it.
     * @param CatalogItem $catalogitem a Catalog item object with all parameters thzt is using
     * this handler.
     * @return boolean true or false
     */
    public function is_available(CatalogItem $catalogitem) {
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return false if not validated
     */
    public function validate_required_data($itemname, $field, $instance, $value, &$errors) {
        return true;
    }

    /**
     * Shows product info
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function display_product_infos($pid, $pinfo) {
        // Do nothing.
        return;
    }

    /**
     * Shows product possible actions
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function display_product_actions($pid, $params) {
        // Do nothing.
        return;
    }

    /**
     * Deletes the product instance
     * @param Product $product the product instance
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function delete(Product $product) {
        // Do nothing.
        return;
    }

    /**
     * Inhibits the product instance in a way it can be reactivated
     * @param Product $product the product instance
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function soft_delete(Product $product) {
        // Do nothing.
        return;
    }

    /**
     * Restores the product instance to its normal effect
     * @param Product $product the product instance
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function soft_restore(Product $product) {
        // Do nothing.
        return;
    }

    /**
     * what should happen when product instance record is updated.
     * @param Product $product the product instance
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function update(Product $product) {
        // Do nothing.
        return;
    }

    /**
     * Tests a product handler
     * @param object $data
     * @param arrayref &$errors
     * @param arrayref &$warnings
     * @param arrayref &$messages
     */
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
                if (!$course = $DB->get_record('course', ['id' => $data->actionparams['customersupport']])) {
                    $errors[$data->code][] = get_string('errornocustomersupportcourse', 'local_shop');
                }
            } else {
                if (!$course = $DB->get_record('course', ['shortname' => $data->actionparams['customersupport']])) {
                    $errors[$data->code][] = get_string('errornocustomersupportcourse', 'local_shop');
                }
            }
        }
    }
}
