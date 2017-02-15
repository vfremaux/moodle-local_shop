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

        if (is_null($catalog)) {
            $str = '<tr>';
            $str .= '<th align="left" class="header c0">';
            $str .= get_string('name', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="header c1">';
            $str .= get_string('description', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="header c2">';
            $str .= get_string('categories', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="header c2">';
            $str .= get_string('items', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="header lastcol">';
            $str .= get_string('controls', 'local_shop');
            $str .= '</th>';
            $str .= '</tr>';

            return $str;
        }

        $str = '';

        $class = ($catalog->items == 0) ? 'empty' : '';
        $str .= '<tr class="'.$class.'" valign="top">';
        $str .= '<td>';

        if ($catalog->isslave) {
            $str .= '<img src="'.$this->output->pix_url('link', 'local_shop').'" />';
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
        $str .= '<a href="'.$editurl.'"><img src="'.$this->output->pix_url('t/edit').'"></a>';
        if ($catalog->is_not_used()) {
            $params = array('catalogid' => $catalog->id, 'what' => 'deletecatalog');
            $deleteurl = new moodle_url('/local/shop/index.php', $params);
            $str .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$this->output->pix_url('/t/delete').'"></a>';
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

        $str = '';
        $str .= '<table width="100%" cellspacing="10">';
        $str .= '<tr valign="top">';
        $str .= '<td valign="top" width="25%">';
        $str .= '<b>'.get_string('allproducts', 'local_shop').'</b>';
        $str .= '</td>';
        $str .= '<td valign="top">';
        $str .= get_string('searchinproducts', 'local_shop');
        $str .= '<br>';
        $str .= '<table width="100%" class="generaltable">';

        $str .= $this->catalog_admin_line(null);
        if ($catalogs) {
            foreach ($catalogs as $c) {
                $str .= $this->catalog_admin_line($c);
            }
        }
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }
}