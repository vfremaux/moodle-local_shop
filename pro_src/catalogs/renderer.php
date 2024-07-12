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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/catalogs/renderer.php');

class shop_catalogs_renderer_extended extends shop_catalogs_renderer {

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
            foreach ($catalogs as $c) {
                if (empty($config->useslavecatalogs)) {
                    if ($c->ismaster || $c->isslave) {
                        continue;
                    }
                }
                $str .= $this->catalog_admin_line($c);
            }
        }
        $str .= '</table>';
        $str .= '</center>';

        return $str;
    }
}