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
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/renderer.php');

class shop_catalogs_renderer extends local_shop_base_renderer {

    /**
     * @param object $catalog
     */
    public function catalog_admin_line($catalog) {
        global $DB;

        if (!is_object($catalog)) {
            return;
        }

        $str = '';

        $class = (empty($catalog->items)) ? 'empty' : '';
        $str .= '<tr class="'.$class.'" valign="top">';
        $str .= '<td>';

        if ($catalog->isslave) {
            $mastercatalogname = $DB->get_field('local_shop_catalog', 'name', array('id' => $catalog->groupid));
            $str .= $this->output->pix_icon('link', $mastercatalogname, 'local_shop');
        }
        $params = array('view' => 'viewAllProducts', 'catalogid' => $catalog->id);
        $catalogurl = new moodle_url('/local/shop/products/view.php', $params);
        $str .= '<a href="'.$catalogurl.'">'.format_string($catalog->name).'</a>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= $catalog->description;
        $str .= '</td>';
        $str .= '<td>';
        if (!$catalog->isslave) {
            $str .= $catalog->categories;
        }
        $str .= '</td>';
        $str .= '<td>';
        $str .= $catalog->items;
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<div class="shop-line-commands">';
        $editurl = new moodle_url('/local/shop/catalogs/edit_catalogue.php', array('catalogid' => $catalog->id));
        $str .= '<a href="'.$editurl.'">'.$this->output->pix_icon('t/edit', get_string('edit')).'</a>';
        if ($catalog->is_not_used()) {
            $params = array('catalogid' => $catalog->id, 'what' => 'deletecatalog');
            $deleteurl = new moodle_url('/local/shop/index.php', $params);
            $str .= '&nbsp;<a href="'.$deleteurl.'">'.$this->output->pix_icon('/t/delete', get_string('delete')).'</a>';
        }
        $str .= '</div>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * Prints an admin list of catalogs
     * @param array $catalogs
     */
    public function catalogs($catalogs) {

        $config = get_config('local_shop');

        $str = '<center>';
        $str .= '<table width="100%" cellspacing="10" class="generaltable">';

        $str .= $this->catalog_admin_line(null);
        if ($catalogs) {
            // Take the first and unique one.
            $c = array_shift($catalogs);
            if (empty($config->useslavecatalogs)) {
                if ($c->ismaster || $c->isslave) {
                    return;
                }
            }
            $str .= $this->catalog_admin_line($c);
        }
        $str .= '</table>';
        $str .= '</center>';

        return $str;
    }
}