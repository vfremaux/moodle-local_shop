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
 * Lang for PRO zone switch
s *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['plugindist'] = 'Plugin distribution';
$string['plugindist_desc'] = '
<p>This plugin is the community version and is published for anyone to use as is and check the plugin\'s
core application. A "pro" version of this plugin exists and is distributed under conditions to feed the life cycle, upgrade,
 documentation and improvement effort.</p>
<p>Please contact one of our distributors to get "Pro" version support.</p>
<p><a href="http://www.mylearningfactory.com/index.php/documentation/Distributeurs?lang=en_utf8">MyLF Distributors</a></p>';

// Caches.
$string['cachedef_pro'] = 'Caches some pro related options and data';

require_once($CFG->dirroot.'/local/shop/lib.php'); // to get xx_supports_feature();
if ('pro' == local_shop_supports_feature()) {
    include($CFG->dirroot.'/local/shop/pro/lang/en/pro.php');
}
