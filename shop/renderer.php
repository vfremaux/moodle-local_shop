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
 * Renderer for shop management.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
use local_shop\Catalog;

class shop_shop_renderer {

    /**
     * prints a full catalog on screen
     * @param int $id the current active shop instance
     * @param array $shops All shops.
     */
    public function shops($id, $shops) {
        global $OUTPUT;

        $namestr = get_string('name');
        $descriptionstr = get_string('description');
        $currencystr = get_string('currency', 'local_shop');
        $catalogstr = get_string('catalog', 'local_shop');
        $blocksstr = get_string('linkedblocks', 'local_shop');

        $table = new html_table();
        $table->width = "98%";
        $table->size = array();
        $table->head = array($namestr, $descriptionstr, $currencystr, $catalogstr, $blocksstr, '');

        foreach ($shops as $sh) {
            if ($sh->catalogid) {
                $catalog = new Catalog($sh->catalogid);
                $catname = $catalog->name;
            } else {
                $catname = '--N.C.--';
            }
            $blockcount = $sh->get_blocks();

            if (local_shop_supports_feature('shop/instances')) {
                $editurl = new moodle_url('/local/shop/pro/shop/edit_shop.php', array('id' => $id, 'shopid' => $sh->id, 'sesskey' => sesskey()));
            } else {
                $editurl = new moodle_url('/local/shop/shop/edit_shop.php', array('id' => $id, 'shopid' => $sh->id, 'sesskey' => sesskey()));
            }
            $commands = '<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle').'</a>';

            if ($blockcount == 0 && local_shop_supports_feature('shop/instances')) {
                $params = array('view' => 'viewAllShops', 'what' => 'delete', 'id' => $id, 'shopid' => $sh->id, 'sesskey' => sesskey());
                $deleteurl = new moodle_url('/local/shop/pro/shop/view.php', $params);
                $commands .= ' <a href="'.$deleteurl.'">'.$OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle').'</a>';
            }

            $shopurl = new moodle_url('/local/shop/front/view.php', array('shopid' => $sh->id));
            $shoplink = '<a href="'.$shopurl.'">'.format_string($sh->name).'</a>';

            $table->data[] = array($shoplink,
                                   format_text($sh->description, $sh->descriptionformat),
                                   $sh->get_currency('symbol'), $catname,
                                   $blockcount,
                                   $commands);
        }

        return html_writer::table($table);
    }
}