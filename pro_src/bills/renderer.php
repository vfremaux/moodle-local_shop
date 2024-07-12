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
 * Local renderer for bills management pro extensions).
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/bills/renderer.php');
require_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');

use local_shop\Partner;

class shop_bills_renderer_extended extends shop_bills_renderer {

    public function export_attachements_template($bill) {
        global $OUTPUT;

        $fs = get_file_storage();
        $template = new StdClass;

        $contextid = context_system::instance()->id;
        $attachments = $fs->get_area_files($contextid, 'local_shop', 'billfiles', $bill->id, "itemid, filepath, filename", false);
        if (empty($attachments)) {
            $template->hasattachments = false;
            $template->nobillattachementnotification = $OUTPUT->notification(get_string('nobillattachements', 'local_shop'));
        } else {
            $template->hasattachments = true;
            foreach ($attachments as $afile) {
                $attachedfiletpl = $this->attachment($afile, $bill);
                $template->attachedfiles[] = $attachedfiletpl;
            }
        }

        $params = array('type' => 'bill', 'billid' => $bill->id, 'id' => $this->theshop->id);
        $attachurl = new moodle_url('/local/shop/pro/bills/attachto.php', $params);
        $template->attachurl = $attachurl;

        return $template;
    }

    public function attachment($file, $bill) {
        global $OUTPUT;

        $template = new StdClass;
        $context = context_system::instance();

        $pathinfo = pathinfo($file->get_filename());
        $filename = $pathinfo['basename'];

        $template->fileicon = $OUTPUT->image_url(file_file_icon($file, 64));
        $template->filename = $file->get_filename();
        $template->fileurl = moodle_url::make_pluginfile_url($context->id, 'local_shop', 'billattachments',
                                                   $file->get_itemid(), '/', $filename);

        $template->filesize = $file->get_filesize();

        $params = [
            'view' => 'viewBill',
            'id' => $file->get_id(),
            'what' => 'unattach',
            'billid' => $bill->id
        ];
        $template->linkurl = new moodle_url('/local/shop/bills/view.php', $params);

        return $template;
    }

    public function export_ownership_template($bill) {
        global $OUTPUT, $DB;

        $template = new StdClass;

        $contextid = context_system::instance()->id;
        $partners = Partner::get_instances(['shopid' => $bill->theshop->id]);

        if (empty($partners)) {
            $template->haspartners = false;
        } else {
            $template->haspartners = true;
            foreach ($partners as $p) {
                if ($bill->partnerid == $p->id) {
                    $template->current = "[{$p->partnerkey}] {$p->name}";
                }
                $selectmenu[$p->id] = "[{$p->partnerkey}] {$p->name}";
            }
            $template->partnerselect = html_writer::select($selectmenu, 'partnerid');
        }

        $template->sesskey = sesskey();

        $template->billid = $bill->id;
        $template->shopid = $this->theshop->id;
        $assignurl = new moodle_url('/local/shop/bills/view.php');
        $template->assignurl = $assignurl;

        $params = [
            'view' => 'viewBill',
            'billid' => $bill->id,
            'id' => $this->theshop->id,
            'what' => 'unassignpartner',
            'sesskey' => $template->sesskey
        ];
        $unassignurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->unassignurl = $unassignurl;

        return $template;
    }
}