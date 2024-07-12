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
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');
require_once($CFG->dirroot.'/local/shop/pro/prolib.php');

use \local_shop\Category;

function get_smart_product_url($catalogitem, $byalias = true, $fulltree = false) {
    global $CFG;

    if (empty($catalogitem)) {
        throw new coding_exeption("Null catalogitem");
    }

    $cattree = '';

    if ($fulltree) {
        $category = new Category($catalogitem->categoryid);
        if ($byalias) {
            if (!empty($category->seoalias)) {
                $cattree = $category->seoalias.'/';
            } else {
                $cattree = $category->id.'/';
            }
        } else {
            $cattree = $category->id.'/';
        }
        while ($category->parentid) {
            $category = new Category($catalogitem->parentid);
            if ($byalias) {
                if (!empty($category->seoalias)) {
                    $cattree = $category->seoalias.'/'.$cattree;
                } else {
                    $cattree = $category->id.'/'.$cattree;
                }
            } else {
                $cattree = $category->id.'/'.$cattree;
            }
        }
    }

    // Seoalias has been built above. Not a getter here !
    $seoalias = $catalogitem->seoalias;
    if ($byalias && !empty($seoalias)) {
        return $CFG->wwwroot.'/local/shop/pro/front/product/'.$cattree.$seoalias;
    } else {
        return $CFG->wwwroot.'/local/shop/pro/front/productid/'.$cattree.$catalogitem->id;
    }
}

/**
 * Get a smart url for accessing category in shop front.
 */
function get_smart_category_url($shopid, $category, $blockid = null, $byalias = true) {
    global $CFG;

    $config = get_config('local_shop');
    $promanager = \local_shop\pro_manager::instance();

    if (empty($config->usesmarturls) || !$promanager->require_pro('products/smarturls')) {
        $params = array('view' => 'shop',
                        'category' => $category->id,
                        'id' => $shopid,
                        'blockid' => $blockid);
        return new moodle_url('/local/shop/front/view.php', $params);
    }

    if (empty($category)) {
        throw new coding_exeption("Null category");
    }

    $alias = $category->seoalias; // Beware of magic getters !
    if ($byalias && !empty($alias)) {
        return $CFG->wwwroot.'/local/shop/front/category/'.$shopid.'/'.$category->seoalias;
    } else {
        return $CFG->wwwroot.'/local/shop/front/categoryid/'.$shopid.'/'.$category->id;
    }
}

/**
 * Sends to adapted theme SEO overrides for head replacement.
 * This works only with adapted theme such as fordson_fel or klassplace,
 * in case of supported SEO additions (local/shop pro version).
 */
function local_shop_setup_seo_overrides(\local_shop\CatalogItem $catalogitem) {
    global $HEAD;

    $promanager = \local_shop\pro_manager::instance();
    if (!$promanager->require_pro('products/smarturls')) {
        return;
    }

    if (!isset($HEAD)) {
        $HEAD = new StdClass;
    }

    if (!isset($HEAD->metas)) {
        $HEAD->metas = new StdClass;
    }

    // Beware : magic getters behind.
    $seokeywords = $catalogitem->seokeywords;
    if (!empty($seokeywords)) {
        $HEAD->metas->keywords = $seokeywords;
    }

    $seodescription = $catalogitem->seodescription;
    if (!empty($seodescription)) {
        $HEAD->metas->description = $seodescription;
    }
}

/**
 * Fires CURL smart order urls for SEO triggering.
 */
function local_shop_fire_smart_order($catalogitem) {

    $promanager = \local_shop\pro_manager::instance();
    if (!$promanager->require_pro('products/smarturls')) {
        shop_debug_trace("Local shop. License check failed for feature \"products/smarturls\"... ", SHOP_TRACE_ERRORS);
        return;
    }

    $smarturl = get_smart_product_url($catalogitem, $byalias = true, $fulltree = false);
    if ($smarturl) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $smarturl.'/order');
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        shop_debug_trace("Curl send to $smarturl.'/order' ... ", SHOP_TRACE_DEBUG);
        $result = curl_exec($ch);
    } else {
        shop_debug_trace("No smart url available... ", SHOP_TRACE_DEBUG);
    }
}