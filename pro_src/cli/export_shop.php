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
 * CLI interface for exporting shop data
 *
 * @package local_shop
 * @copyright 2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');

if (!isset($CFG->dirroot)) {
    die ('$CFG->dirroot must be explicitely defined in moodle config.php for this script to be used');
}

require_once($CFG->dirroot.'/lib/clilib.php'); // Cli only functions.

// CLI options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'host' => false,
        'shopid' => false,
        'outputdir' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        's' => 'shopid',
        'O' => 'outputdir',
    )
);

// Display help.
if (!empty($options['help'])) {

echo "
Duplicates a complete shop and associated catalog.
Note : does NOT manage slave catalogs at the moment.

Options:
\t-h, --help              Print out this help
\t-H,--host               The hostname when in VMoodle environment
\t-s,--shopid             the source shop to duplicate. 
\t-s,--outputdir          Output dir. 

\$ sudo -u www-data /usr/bin/php local/shop/pro/cli/duplicate_shop.php --host=http://myvhost.mymoodle.org
";
    // Exit with error unless we're showing this because they asked for it.
    exit(empty($options['help']) ? 1 : 0);
}

// Now get cli options.

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error("Not recognized options ".$unrecognized);
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.
if (!$CLI_VMOODLE_PRECHECK) {
    require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'); // Global moodle config file.
}
echo('Config check : playing for '.$CFG->wwwroot."\n");

if (empty($options['shopid'])) {
    die("No source shopid is defined\n");
}

$idmapping = [];
$counts = [];

$shop = $DB->get_record('local_shop', ['id' => $options['shopid']]);

// Clone active catalog.

$catalog = $DB->get_record('local_shop_catalog', ['id' => $shop->catalogid]);
$shop->catalogs[$catalog->id] = $catalog;
$shop->catalogs[$catalog->id]->categories = [];

// Clone categories.
$categories = $DB->get_records('local_shop_catalogcategory', ['catalogid' => $catalog->id]);
$counts['categories'] = 0;
if (!empty($categories)) {
    foreach ($categories as $cc) {
        $shop->catalogs[$catalog->id]->categories[$cc->id] = $cc;
        $shop->catalogs[$catalog->id]->categories[$cc->id]->items = [];
    }
}

$cis = $DB->get_records('local_shop_catalogitem', ['catalogid' => $catalog->id]);
$counts['items'] = 0;
if (!empty($cis)) {
    foreach ($cis as $ci) {
        $shop->catalogs[$catalog->id]->categories[$ci->categoryid]->items[$ci->id] = $ci;
    }
}

if (!empty($options['outputdir'])) {
    $output = $options['outputdir'].'/moodle_shop_'.$shop->id.'.json';
} else {
    $output = $CFG->dataroot.'/moodle_shop_'.$shop->id.'.json';
}

if ($FILE = fopen($output, 'w')) {
    fputs($FILE, json_encode($shop));
    fclose($FILE);
}

echo "All Done.\n";