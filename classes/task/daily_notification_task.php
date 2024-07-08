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
 * Daily notifications
 *
 * @package local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\task;

/**
 * Scheduled task to compile completion based checks.
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 */
class daily_notification_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_daily_notification', 'local_shop');
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot.'/local/shop/cronlib.php');
        $manager = new \local_shop\cron\manager();
        $manager->notify_daily_task();
    }
}