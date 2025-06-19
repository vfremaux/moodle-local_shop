<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,00
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang for partners
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['allbutpartners'] = 'All but partners';
$string['partner'] = 'Partner';
$string['partners'] = 'Partners';
$string['editpartner'] = 'Edit partner';
$string['exportpartnerkey'] = 'Partner Key';
$string['exportpartnertag'] = 'Partner Tag';
$string['hasmoodleaccount'] = 'Has moodle account';
$string['hascustomeraccount'] = 'Has customer account';
$string['partnerkey'] = 'Partner key';
$string['partnersecretkey'] = 'Partner secret key';
$string['managepartners'] = 'Manage partners';
$string['addpartner'] = 'Add partner';
$string['newpartner'] = 'Add new partner';
$string['nopartner'] = 'No partners';
$string['partnername'] = 'Partner name';
$string['partnernameexists'] = 'This partner name already exists';
$string['partnerenabled'] = 'Partner is enabled';
$string['customerid'] = 'Associated customer';
$string['partnercustomerid'] = 'Associated customer for partners';
$string['countbills'] = 'Bills for partner';
$string['moodleuser'] = 'Moodle user';
$string['moodleuser_help'] = 'Pick a username of a moodle registered user so partner can receive notifications and emails.';
$string['useraccount'] = 'Moodle account username';
$string['partnerincome'] = 'Partner total income';
$string['erroremptypartnername'] = 'Partner name cannot be empty';
$string['referer'] = 'Referer';
$string['viewpartnerbills'] = 'View partner bills';

$string['managepartners_desc'] = 'Partners can import purchase sessions into the shop and register them on their behalf.';

$string['moodleuser_help'] = 'Moodle user linked to the partner. This identity will be used to send notifications.';

$string['referer_help'] = 'When the partner sends the shop a shopping cart pre-fill request from his own website, the
shop will check the referer site identity, if this setting is set with the remote web site rerferer. This is not an absolute
protection but contributes to lowering abuses. For web clients that do NOT provide referer attribute, the complete purchase
sequence will have to be played.';

$string['partnercustomerid_help'] = 'In some cases where partner may be charged in a shop operation, partners need to be
assigned to a cusstomer account to which invoices will be routed.';

$string['partnersecretkey_help'] = 'This key is confidential and is NOT used in any visible URL or display. It serves in some
special operations such as remote product activation process.';
