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
 * Capabilities
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'local/shop:salesadmin' => [

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    'local/shop:beassigned' => [

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    /*
     * people having this capability will be allowed to see data from all individual shop
     * subhowners.
     */
    'local/shop:accessallowners' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
        ],
    ],

    /*
     * people having this capability will be get a discount
     */
    'local/shop:discountagreed' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
        ],
    ],

    /*
     * people having this capability will be get a second level discount
     */
    'local/shop:seconddiscountagreed' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
        ],
    ],

    /*
     * people having this capability will be get a third level discount
     */
    'local/shop:thirddiscountagreed' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
        ],
    ],

    /*
     * people having this capability can use check payment with a direct production (trusted customers)
     */
    'local/shop:paycheckoverride' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
        ],
    ],

    /*
     * people having this capability will be presented noninstantpayments methods
     */
    'local/shop:usenoninstantpayments' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],

    /*
     * people having this capability will be presented noninstantpayments methods
     */
    'local/shop:export' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
