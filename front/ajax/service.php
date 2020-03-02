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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;
use local_shop\Shop;

$PAGE->set_url(new moodle_url('/local/shop/front/ajax/service.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');

$service = required_param('service', PARAM_TEXT);
$shopid = required_param('id', PARAM_INT);
$theshop = new Shop($shopid);
$thecatalog = new Catalog($theshop->catalogid);

$output = '';

$action = optional_param('what', '', PARAM_TEXT);
require_once($CFG->dirroot.'/local/shop/front/'.$service.'.controller.php');
$controllerclass = "\\local_shop\\front\\{$service}_controller";
$controller = new $controllerclass($theshop, $thecatalog);
$controller->receive($action);
echo $controller->process($action);