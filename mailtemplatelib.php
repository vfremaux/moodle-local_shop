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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * useful templating functions from an older project of mine, hacked for Moodle
 * @param string $template the template's file name from $CFG->sitedir
 * @param array $infomap a hash containing pairs of parm => data to replace in template
 * @param string $subplugin when in a subplugin, the frankenstyle name of the subplugin
 * @param string $lang 
 * @param string $transactionid
 * @return a fully resolved template where all data has been injected
 */
function shop_compile_mail_template($template, $infomap, $subplugin, $lang = '', $transactionid = '') {
    global $CFG, $USER;

    if (empty($lang)) {
        $lang = @$USER->lang;
    }
    if (empty($lang)) {
        $lang = $CFG->lang;
    }

    $notification = shop_get_mail_template($template, $lang, $subplugin, $transactionid);
    foreach ($infomap as $key => $value) {
        $notification = str_replace("<%%{$key}%%>", $value, $notification);
    }
    return $notification;
}

/**
 * resolves and get the content of a Mail template, acoording to the user's current language.
 * @param string $virtual the virtual mail template name
 * @param string $lang if default language must be overriden
 * @param string $subplugin if default language must be overriden
 * @return string the template's content or false if no template file is available
 */
function shop_get_mail_template($virtual, $lang, $subplugin = '') {
    global $CFG;

    if ($lang == '') {
        $lang = $CFG->lang;
    }

    if (empty($subplugin)) {
        return new lang_string($virtual.'_tpl', 'local_shop', $lang);
    } else {
        return new lang_string($virtual.'_tpl', $subplugin, $lang);
    }
}
