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

require('../../config.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use \local_shop\Shop;

header("Content-Type: text/css\n\n");

$config = get_config('local_shop');

echo '
.shop-article.product > .shop-front-productpix img,
.shop-article.bundle > .shop-front-productpix img,
.shop-article.set > .shop-front-productpix img {
    height:'.$config->productimageheight.'px;
    margin-right: '.$config->productimagermargin.'px;
    width:'.$config->productimagewidth.'px;
}
';

echo '
.shop-article.element > .shop-front-productpix img {
    height:'.$config->elementimageheight.'px;
    margin-right: '.$config->elementimagermargin.'px;
    width:'.$config->elementimagewidth.'px;
}
';
