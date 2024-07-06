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
 * Local renderer for catalogs management.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/renderer.php');

class shop_catalogs_renderer extends local_shop_base_renderer {

    /**
     * Product admin line in catalog editing
     * @param object $catalog
     */
    public function catalog_admin_line($catalog) {
        global $DB;

        if (!is_object($catalog)) {
            return;
        }

        $template = new StdClass;

        $template->class = (empty($catalog->items)) ? 'empty' : '';

        if ($catalog->isslave) {
            $mastercatalogname = $DB->get_field('local_shop_catalog', 'name', ['id' => $catalog->groupid]);
            $template->isslave = true;
            $template->linkicon = $this->output->pix_icon('link', $mastercatalogname, 'local_shop');
        }
        $params = ['catalogid' => $catalog->id, 'view' => 'viewallProducts'];
        $template->catalogurl = new moodle_url('/local/shop/products/view.php', $params);
        $template->name = format_string($catalog->name);
        $template->description = $catalog->description;
        if (!$catalog->isslave) {
            $template->categories = $catalog->categories;
        }
        $template->items = $catalog->items;
        $template->editurl = new moodle_url('/local/shop/catalogs/edit_catalogue.php', ['catalogid' => $catalog->id]);
        $template->isnotused = $catalog->is_not_used();
        if ($template->isnotused) {
            $params = ['catalogid' => $catalog->id, 'what' => 'deletecatalog'];
            $template->deleteurl = new moodle_url('/local/shop/index.php', $params);
        }

        return $this->output->render_from_template('local_shop/catalog_admin_line', $template);
    }

    /**
     * Prints an admin list of catalogs
     * @param array $catalogs
     */
    public function catalogs($catalogs) {

        $config = get_config('local_shop');

        $template = new StdClass();

        if ($catalogs) {
            foreach ($catalogs as $c) {
                $template->catalogs[] = $this->catalog_admin_line($c);
            }
        }

        return $this->output->render_from_template('local_shop/catalogs', $template);
    }
}
