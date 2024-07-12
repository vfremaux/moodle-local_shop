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
 * CLI interface for duplicating a shop
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
        'newname' => false,
        'shopid' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'n' => 'newname',
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
\t-s,--shopid             the source shop to duplicate. 
\t-n,--newname            the nexw shop name. If not given, will be appended with a copymark. 

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

$sourceshop = $DB->get_record('local_shop', ['id' => $options['shopid']]);

mtrace("Cloning shop $shop->name\n");

$shopoldid = $sourceshop->id;
if (empty($options['newname'] || ($options['newname'] == $sourceshop->name))) {
    $sourceshop->name .= get_string('namecopymark', 'local_shop'); 
} else {
    $sourceshop->name = $options['newname']; 
}

$shop = clone($sourceshop);
unset($shop->id);

// Let it be idempotent.
if (!$oldshop = $DB->get_record('local_shop', ['name' => $shop->name])) {
    $shop->id = $DB->insert_record('local_shop', $shop);
    $idmapping['shop'][$shopoldid] = $shop->id;
} else {
    $shop = $oldshop;
    $idmapping['shop'][$shopoldid] = $shop->id;
}

// Clone active catalog.

$sourcecatalog = $DB->get_record('local_shop_catalog', ['id' => $sourceshop->catalogid]);
$catalogoldid = $sourcecatalog->id;
mtrace("Recording original bound catalog is id $catalogoldid ");

$catalog = clone($sourcecatalog);
$catalog->name .= get_string('namecopymark', 'local_shop'); 
unset($catalog->id);

if (!$oldrecord = $DB->get_record('local_shop_catalog', ['name' => $catalog->name])) {
    $catalog->id = $DB->insert_record('local_shop_catalog', $catalog);
    $idmapping['shop_catalog'][$catalogoldid] = $catalog->id;
} else {
    $catalog->id = $oldrecord->id;
    $idmapping['shop_catalog'][$catalogoldid] = $oldrecord->id;
}

// Update catalog mapping.
$shop->catalogid = $catalog->id;
$DB->update_record('local_shop', $shop);

mtrace("Cloning Catalog categories from old catalog id $catalogoldid\n");

// Clone categories.
$categories = $DB->get_records('local_shop_catalogcategory', ['catalogid' => $catalogoldid]);
$counts['categories'] = 0;
if (!empty($categories)) {
    foreach ($categories as $cc) {
        $oldccid = $cc->id;
        unset($cc->id);
        $cc->catalogid = $catalog->id;
        if (!$oldrecord = $DB->get_record('local_shop_catalogcategory', ['catalogid' => $cc->catalogid, 'name' => $cc->name])) {
            $cc->id = $DB->insert_record('local_shop_catalogcategory', $cc);
            mtrace("Cloning catalog category $cc->name to new id $cc->id in catalog $cc->catalogid\n");
            $counts['categories']++;
            $idmapping['shop_catalog_category'][$oldccid] = $cc->id;
        } else {
            $idmapping['shop_catalog_category'][$oldccid] = $oldrecord->id;
            mtrace("Catalog category $cc->name exists in catalog $cc->catalogid\n");
        }
    }
} else {
    mtrace("\tNo Catalog category to clone\n");
}

// Clone catalog items.
$cis = $DB->get_records('local_shop_catalogitem', ['catalogid' => $catalogoldid]);
$counts['items'] = 0;
if (!empty($cis)) {
    mtrace("Cloning catalog items\n");
    foreach ($cis as $ci) {
        $ci->catalogid = $idmapping['shop_catalog'][$ci->catalogid];
        $oldciid = $ci->id;
        unset($ci->id);
        $ci->shortname .= '_Copy';
        $ci->code .= '_Copy';
        $ci->idnumber .= '_Copy';
        $ci->categoryid = $idmapping['shop_catalog_category'][$ci->categoryid];
        if (!$oldrecord = $DB->get_record('local_shop_catalogitem', ['categoryid' => $ci->categoryid, 'code' => $ci->code])) {
            $ci->id = $DB->insert_record('local_shop_catalogitem', $ci);
            duplicate_catalogitem_fileareas($oldciid, $ci->id);
            mtrace("Cloning catalog category $ci->code to new id $ci->id in category $ci->categoryid\n");
            $counts['items']++;
            $idmapping['shop_catalogitem'][$oldciid] = $ci->id;
        } else {
            duplicate_catalogitem_fileareas($oldciid, $oldrecord->id);
            $idmapping['shop_catalogitem'][$oldciid] = $oldrecord->id;
            mtrace("Catalog item $ci->code exists in catalog $ci->categoryid\n");
        }
    }
} else {
    mtrace("\tNo Catalog items to clone\n");
}

mtrace("Remapping sets\n");

// Post product copy, remap setid in products. Getting new products and passing through.
$newcis = $DB->get_records('local_shop_catalogitem', ['catalogid' => $idmapping['shop_catalog'][$catalogoldid]]);
if (!empty($newcis)) {
    $remaps = 0;
    foreach ($newcis as $ci) {
        if ($ci->setid > 0) {
            $ci->setid = $idmapping['shop_catalogitem'][$ci->setid];
            $DB->update_record('local_shop_catalogitem', $ci);
            $remaps++;
        }
    }
    mtrace("\Remapped $remaps set items\n");
} else {
    mtrace("\tNo set records to remap\n");
}

echo "All Done.\n";

function duplicate_catalogitem_fileareas($oldid, $newid) {
    global $DB;

    $fs = get_file_storage();

    $areas = ['catalogitemimage', 'catalogitemthumb', 'catalogitemleaflet', 'catalogitemunit'];

    $systemcontext = context_system::instance();

    foreach ($areas as $area) {
        $allareafiles = $DB->get_records('files', ['contextid' => $systemcontext->id, 'component' => 'local_shop', 'filearea' => $area, 'itemid' => $oldid]);
        if (!empty($allareafiles)) {
        	$realareafiles = [];
            foreach ($allareafiles as $filerec) {
                if (empty($filerec->filename) || $filerec->filename == '.') {
                    continue; // Is a dir.
                }
                $realareafiles[$filerec->id] = $filerec;
            }
            mtrace("\tCopying ".count($realareafiles)." from area $area\n");
            foreach ($realareafiles as $filerec) {
                $storedfile = $fs->get_file_by_id($filerec->id);

                // Clone the file.
                $filedesc = new StdClass;
                $filedesc->contextid = $systemcontext->id;
                $filedesc->component = $filerec->component;
                $filedesc->filearea = $filerec->filearea;
                $filedesc->itemid = $newid;
                $filedesc->filepath = $filerec->filepath;
                $filedesc->filename = $filerec->filename;
                if ($oldstoredfile = $fs->get_file($filedesc->contextid, $filedesc->component, $filedesc->filearea, $filedesc->itemid, $filedesc->filepath, $filedesc->filename)) {
                    // Remove any older file that would be in the way.
                    $oldstoredfile->delete();
                }
                try {
                    $fs->create_file_from_storedfile($filedesc, $storedfile);
                } catch (Exception $ex) {
                    // Let pass exceptions.
                }
            }
        }
    }
}