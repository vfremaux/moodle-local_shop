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
 * CLI interface for importing shop data.
 * Shop data must be an exported JSON file out from export_shop.php script.
 * It will be imported as the "next to come" shopid. If an existing shopid is provided as input
 * it will just append all the shop content to that shop, creating additional catalog and categories.
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
        'file' => false,
        'shopid' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'f' => 'input',
        's' => 'shopid',
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
\t-s,--file               the file to import. 
\t-s,--shopid             If an existing shop id is given, adds shop defines to thsi shop. Creates the next id if not given.

\$ sudo -u www-data /usr/bin/php local/shop/pro/cli/import_shop.php --host=http://myvhost.mymoodle.org
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
    $shopid = $DB->get_field('local_shop', 'MAX(id)', []);
    $nextshopid = $shopid + 1;
}

$idmapping = [];
$counts = [];
$allcatagories = [];
$allcatalogitems = [];
$allshops = [];

// Open file

if (empty($options['file'])) {
    die("A import file must be given\n");
}

// Adjust file to dataroot if not absolute path.
if (strpos($options['file'], '/') !== 0) {
    echo "Adding dataroot to : {$options['file']} ";
    $options['file'] = $CFG->dataroot.'/'.$options['file'];
}

if (!file_exists($options['file'])) {
    die("Could not open the file {$options['file']} \n");
}

// Parsing json file.
$json = implode("\n", file($options['file']));

$shop = json_decode($json);

if (empty($shop)) {
    die("Could not convert json content \n");
}

// Now we have a supposed valid $shop structure.
if (empty($options['shopid'])) {
    // Remove the catalog part. Keep the shop record.
    $catalogs = $shop->catalogs;
    unset($shop->catalogs);

    $oldid = $shop->id;
    unset($shop->id);

    // Insets a new shop getting the last inserted id as new id.
    $idmapping['shop'][$oldid] = $DB->insert_record('local_shop', $shop);
    $allshops[$idmapping['shop'][$oldid]] = $shop;
} else {
    $shop = $DB->get_record('local_shop', ['id' => $options['shopid']]);
    echo "Taking existing shop with id {$options['shopid']} \n";
}

echo "Starting import\n";

$trans = $DB->start_delegated_transaction();

if (!empty($catalogs)) {
    foreach ($catalogs as $ct) {
        echo "\tProcessing catalog {$ct->id} \n";
        // Extract categories structure. Get catalog record.
        $categories = $ct->categories;
        unset($ct->categories);

        $oldid = $ct->id;
        unset($ct->id);
        $idmapping['catalog'][$oldid] = $DB->insert_record('local_shop_catalog', $ct);

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                echo "\t\tProcessing category {$cat->id} \n";
                // Extract items and get category record.
                $items = $cat->items;
                unset($cat->items);

                $oldid = $cat->id;
                unset($cat->id);
                $cat->catalogid = $idmapping['catalog'][$cat->catalogid];
                $idmapping['catalogcategory'][$oldid] = $DB->insert_record('local_shop_catalogcategory', $cat);
                // Record categories for post processing.
                $allcategories[$idmapping['catalogcategory'][$oldid]] = $cat;

                if (!empty($items)) {
                    foreach ($items as $it) {
                        echo "\t\tProcessing item {$it->id} \n";
                        $oldid = $it->id;
                        unset($it->id);
                        $it->catalogid = $idmapping['catalog'][$it->catalogid];
                        $it->categoryid = $idmapping['catalogcategory'][$it->categoryid];
                        $idmapping['catalogitem'][$oldid] = $DB->insert_record('local_shop_catalogitem', $it);
                        $allcatalogitems[$idmapping['catalogitem'][$oldid]] = $it;
                    }
                }
            }
        }
    }
}

// 2nd pass to remap shop active catalog to an imported catalog.
if (!empty($allshops)) {
    // If we have such key, the inserted shop needs to have a catalog remapped to an imported catalog.
    foreach ($allshops as $newsh) {
        $DB->set_field('local_shop', 'catalogid', $idmapping['shop'][$newsh->catalogid],  ['id'  => $newsh->id]);
    }
}


// 2nd pass to process catalogcategory parents (parentid).
if (!empty($allcategories)) {
    echo "Remapping catalog categories parents\n";
    // Post process parendid using mapping array.
    foreach ($allcategories as $catid => $cat) {
        if ($cat->parentid) {
            echo "\tremapping parentid  {$cat->parentid} to ".$idmapping['catalogcategory'][$cat->parentid]."\n";
            $DB->set_field('local_shop_catalogcategory', 'parentid', $idmapping['catalogcategory'][$cat->parentid], ['id' => $catid]);
        }
    }
}

if (!empty($allcatalogitems)) {
    echo "Remapping catalog items set references\n";
    // Post process parendid using mapping array.
    foreach ($allcatalogitems as $itemid => $it) {
        if ($it->setid) {
            echo "\tremapping setid  {$it->setid} to ".$idmapping['catalogitem'][$it->setid]."\n";
            $DB->set_field('local_shop_catalogitem', 'setid', $idmapping['catalogitem'][$it->setid], ['id' => $itemid]);
        }
    }
}

$trans->allow_commit();

// 2nd pass to process catalogitem set internal reference (setid).

echo "All Done.\n";