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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes FreeOrder does not directly store any personal data about any user.';

$string['enablefreeorder'] = 'Free order (zero amount)';
$string['enablefreeorder2'] = 'Free order (zero amount)';
$string['enablefreeorder3'] = 'This order only contain free products...';
$string['freeorder'] = 'Free order';
$string['pluginname'] = 'Free order';

$string['pending_followup_text_tpl'] = '
<p>A free order should never be in pending state. If this occurs, please advise the administrators.</p>
';

$string['success_followup_text_tpl'] = '
<p class="courseshop-info">Your request is being prepared. You will notified by mail when complete.</p>
<p>If you do not have any result notification in a short delay, please contact our support service <%%SUPPORT%%>.</p>
';
