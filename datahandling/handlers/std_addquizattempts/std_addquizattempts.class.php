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
 * Main handler class.
 *
 * @package     local_shop
 * @category    local
 * @subpackage  product_handlers
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * STD_ADD_TRAINING_CREDITS is a standard shop product action handler that adds coursecredits to the customer
 * credit account. This will only work when the trainingcredits enrol method is installed an enabled.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

class shop_handler_std_addquizattempts extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_addquizattempts'; // For unit test reporting.
        parent::__construct($label);
    }

    public function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    /**
     * this product should not be available if the current user (purchaser) is
     * not enrolled in the course where the quizzes stand.
     * Note at this moment that first quiz instance id reference must exist and be sane.
     */
    public function is_available(&$catalogitem) {
        global $USER, $DB, $CFG;

        if (!is_dir($CFG->dirroot.'/mod/quiz/accessrule/usernumattempts')) {
            // Usenumattempts not even installed.
            return false;
        }

        if (!isloggedin() || isguestuser()) {
            // Never available for not logged in people anyway.
            return false;
        }

        if (!empty($catalogitem->handlerparams['quizid'])) {
            $quizzesids = explode(',', $catalogitem->handlerparams['quizid']);
            $firstid = array_shift($quizzesids);
            $quiz = $DB->get_record('quiz', ['id' => $firstid]);
            if (!$quiz) {
                $catalogitem->notavailablereason = "Bad quiz id reference";
                return false;
            }
            $course = $DB->get_record('course', ['id' => $quiz->course]);
        } else if (!empty($catalogitem->handlerparams['quizidnumber'])) {
            $quizzesidnums = explode(',', $catalogitem->handlerparams['quizidnumber']);
            $firstidnum = array_shift($quizzesidnums);
            $params = array('idnumber' => $firstidnum);
            $cm = $DB->get_record('course_modules', $params);
            if (!$cm) {
                $catalogitem->notavailablereason = "Bad quiz idnumber reference";
                return false;
            }
            $course = $DB->get_record('course', ['id' => $cm->course]);
        } else if (!empty($catalogitem->handlerparams['quizcmid'])) {
            $quizzescmids = explode(',', $catalogitem->handlerparams['quizcmid']);
            $firstcmid = array_shift($quizzescmids);
            $params = array('id' => $firstcmid);
            $cm = $DB->get_record('course_modules', $params);
            if (!$cm) {
                $catalogitem->notavailablereason = "Bad quiz course module reference";
                return false;
            }
            $course = $DB->get_record('course', ['id' => $cm->course]);
        } else {
            $catalogitem->notavailablereason = "No quiz reference field.";
            return false;
        }

        if (!$course || !$course->visible) {
            // Hide product if course has disappeared or is not visible.
            $catalogitem->notavailablereason = "Quiz references to a hidden course";
            return false;
        }

        $context = context_course::instance($course->id);

        $return = is_enrolled($context, $USER);
        if ($return) {
            return true;
        } else {
            $catalogitem->notavailablereason = "Not enrolled in course";
            return false;
        }
    }

    public function produce_prepay(&$data, &$errorstatus) {
        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';
        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['attemptsamount'])) {
            $message = "[{$data->transactionid}] STD_ADD_QUIZ_ATTEMPTS Postpay Warning :";
            $message /= " No attempts defined. defaults to one per quantity unit";
            shop_trace($message);
            $data->actionparams['attemptsamount'] = 1;
        }

        if (!array_key_exists('quizidnumber', $data->actionparams)
                && !array_key_exists('quizid', $data->actionparams)
                        && !array_key_exists('quizcmid', $data->actionparams)) {
            // We need at least one.
            $message = "[{$data->transactionid}] STD_ADD_QUIZ_ATTEMPTS Postpay Error:";
            $message /= " No quiz associated";
            shop_trace($message);

            $fb = get_string('productiondata_failure_public', 'shophandlers_std_addquizattempts', 'Code : ADD QUIZ ATTEMPTS');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_addquizattempts', $data);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_addquizattempts', $data);
            $productionfeedback->salesadmin = $fb;
            shop_trace("[{$data->transactionid}] STD_ADD_QUIZ_ATTEMPTS Postpay Error : No quiz defined.");
            return $productionfeedback;
        }

        $quizzes = [];
        if (!is_dir($CFG->dirroot.'/mod/quiz/accessrule/usernumattempts')) {
            // Usenumattempts not installed.
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_addquizattempts', 'Code : ADD QUIZ ATTEMPTS');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_addquizattempts', $data);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_addquizattempts', $data);
            $productionfeedback->salesadmin = $fb;
            shop_trace("[{$data->transactionid}] STD_ADD_QUIZ_ATTEMPTS Postpay Error : Userquiz Limits not installed.");
            return $productionfeedback;
        }

        // Add attempts to usernumattempts for the associated quiz(zes).
        if (!empty($data->actionparams['quizid'])) {
            $quizids = explode(',', $data->actionparams['quizid']);
            // Validate all quizids, in case some were deleted.
            foreach ($quizids as $qid) {
                if ($quiz = $DB->get_record('quiz', ['id' => $data->actionparams['quizid']])) {
                    $quizzes[$quiz->id] = $quiz;
                }
            }
        } else if (!empty($data->actionparams['quizcmid'])) {
            $cmids = explode(',', $data->actionparams['quizcmid']);
            foreach ($cmids as $cmid) {
                $cmid = trim($cmid);
                if ($cm = $DB->get_record('course_modules', ['id' => $cmid])) {
                    // Let assume quiz/cm relation is sane.
                    $quizzes[$cm->instance] = $DB->get_record('quiz', ['id' => $cm->instance]);
                }
            }
        } else if (!empty($data->actionparams['quizidnumber'])) {
            $cmidnums = explode(',', $data->actionparams['quizidnumber']);
            $module = $DB->get_record('modules', ['name' => 'quiz']);
            foreach ($cmidnums as $idnum) {
                $idnum = trim($idnum);
                if ($cm = $DB->get_record('course_modules', ['idnumber' => $idnum])) {
                    if ($cm->module == $module->id) {
                        // Check we are a quiz.
                        $quizzes[$cm->instance] = $DB->get_record('quiz', ['id' => $cm->instance]);
                    }
                }
            }
        }
        // In this version we silently discard all misfits. Product verification should have notified any to the
        // shop administrator.

        // Add user attempts to quiz.
        $quiznames = [];
        debug_trace($quizzes, TRACE_DEBUG_FINE);
        if (!empty($quizzes)) {
            foreach ($quizzes as $q) {
                // Loggedin user id is assuming purchasing for himself.
                debug_trace($data->actionparams);
                require(\accessrule_usernumattempts\xapi::add_attempts($USER->id, $q->id, $data->actionparams['attemptsamount']));
            }
        }

        $e = new Stdclass;
        $e->txid = $data->transactionid;
        $e->credits = $data->actionparams['attemptsamount'] * $data->quantity;
        $e->quizname = implode(', ', $quiznames);
        $e->username = $USER->username;
        $fb = get_string('productiondata_post_public', 'shophandlers_std_addquizattempts', $e);
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_post_private', 'shophandlers_std_addquizattempts', $e);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_post_sales', 'shophandlers_std_addquizattempts', $e);
        $productionfeedback->salesadmin = $fb;

        shop_trace("[{$data->transactionid}] STD_ADD_QUIZ_ATTEMPTS Postpay : Complete.");
        return $productionfeedback;
    }

    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $module = $DB->get_record('modules', ['name' => 'quiz']);

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!array_key_exists('quizidnumber', $data->actionparams)
                && !array_key_exists('quizid', $data->actionparams)
                        && !array_key_exists('quizcmid', $data->actionparams)) {
            $errors[$data->code][] = get_string('errorunassignedquiz', 'shophandlers_std_addquizattempts');
        }

        if (!isset($data->actionparams['attemptsamount'])) {
            $warnings[$data->code][] = get_string('warningnullcredits', 'shophandlers_std_addquizattempts');
        }

        // Check if all quizes in same course. Warn if not.
        $firstcourse = null;
        $courseids = [];

        if (!empty($data->actionparams['quizid'])) {
            $quizids = explode(',', $data->actionparams['quizid']);
            // Validate all quizids, in case some were deleted.
            foreach ($quizids as $qid) {
                if (!$quiz = $DB->get_record('quiz', ['id' => $data->actionparams['quizid']])) {
                    $errors[$data->code][] = get_string('errorbadquizref', 'shophandlers_std_addquizattempts', $qid);
                } else {
                    $courseids[$quiz->course] = $quiz->course;
                    if (is_null($firstcourse)) {
                        $firstcourse = $quiz->course;
                    }
                }
            }
        } else if (!empty($data->actionparams['quizcmid'])) {
            $cmids = explode(',', $data->actionparams['quizcmid']);
            foreach ($cmids as $cmid) {
                $cmid = trim($cmid);
                if (!$cm = $DB->get_record('course_modules', ['id' => $cmid])) {
                    // Let assume quiz/cm relation is sane.
                    $errors[$data->code][] = get_string('errorbadcmref', 'shophandlers_std_addquizattempts', $cmid);
                } else {
                    $courseids[$cm->course] = $cm->course;
                    if (is_null($firstcourse)) {
                        $firstcourse = $cm->course;
                    }
                    if ($cm->module != $module->id) {
                        $errors[$data->code][] = get_string('errornotaquiz', 'shophandlers_std_addquizattempts', $idnum);
                    }
                }
            }
        } else if (!empty($data->actionparams['quizidnumber'])) {
            $cmidnums = explode(',', $data->actionparams['quizidnumber']);
            foreach ($cmidnums as $idnum) {
                $idnum = trim($idnum);
                if (!$cm = $DB->get_record('course_modules', ['idnumber' => $idnum])) {
                    $errors[$data->code][] = get_string('errorbadidnumber', 'shophandlers_std_addquizattempts', $idnum);
                } else {
                    $courseids[$cm->course] = $cm->course;
                    if (is_null($firstcourse)) {
                        $firstcourse = $cm->course;
                    }
                    if ($cm->module != $module->id) {
                        $errors[$data->code][] = get_string('errornotaquiz', 'shophandlers_std_addquizattempts', $idnum);
                    }
                }
            }
        }

        // Check if only one course.
        $coursenum = count(array_keys($courseids));
        if ($coursenum == 0) {
            $errors[$data->code][] = get_string('errornoquizvalid', 'shophandlers_std_addquizattempts');
        }
        if ($coursenum > 1) {
            $firstcourse = $DB->get_record('course', ['id' => $firstcourseid], 'id,shortname,fullname,idnumber');
            $warnings[$data->code][] = get_string('warningmultiplecourses', 'shophandlers_std_addquizattempts', $firstcourse);
        }
    }
}