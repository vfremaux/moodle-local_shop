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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/renderer.php');

class local_shop_renderer_extended extends local_shop_renderer {

    public function set_output($output) {
        $this->output = $output;
    }

    /**
     * Link to the edit catalogue form.
     */
    public function new_catalogue_form() {
        $template = new StdClass;
        $template->editurl = new moodle_url('/local/shop/catalogs/edit_catalogue.php');
        return $this->output->render_from_template('local_shop/catalogs_new_instance', $template);
    }

    public function partner_choice($current, $url) {
        global $SESSION, $CFG;
        include_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');

        $str = '';
        $partners = local_shop\Partner::get_instances();
        $partnermenu = array();

        $partnermenu[0] = get_string('chooseall', 'local_shop');
        $partnermenu[-1] = get_string('allbutpartners', 'local_shop');

        foreach ($partners as $p) {
            $partnermenu[$p->id] = format_string($p->name);
        }
        $attrs['label'] = get_string('partners', 'local_shop').': ';
        $str .= $this->output->single_select($url, 'p', $partnermenu, $current, null, null, $attrs);

        return $str;
    }

    public function customer_choice($current, $url) {
        global $OUTPUT;

        $config = get_config('local_shop');

        if (empty($config->userdelegation)) {
            return parent::customer_choice($current, $url);
        }

        $customers = Customer::get_delegated_instances_menu(array(), 'CONCAT(lastname, \' \', firstname)', 'lastname, firstname');

        $str = '';
        $str .= $OUTPUT->single_select($url, 'customerid', $customers, $current);

        return $str;
    }
}
