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

// jshint unused: true, undef:true

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var shopproductdetail = {

        init: function() {
            // Binds response to product pass code when defined.
            $('#ci-pass').bind('change', this.check_pass_code);
        },

        /**
         * Similar to front.js, but single product in page.
         * @param {Object} e - The event
         */
        check_pass_code: function(e) {

            var that = $(this);

            var productname = that.attr('data-product');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            var ajax_waiter_img = '<img width="14" height="14" src="' + cfg.wwwroot + '/local/shop/pix/ajaxloader.gif" />';
            var ajax_success_img = '<img width="14" height="14" src="' + cfg.wwwroot + '/local/shop/pix/valid.png" />';
            var ajax_failure_img = '<img width="14" height="14" src="' + cfg.wwwroot + '/local/shop/pix/invalid.png" />';

            $('#ci-pass-status-' + productname).html(ajax_waiter_img);
            $('#cimod-pass-status-' + productname).html(ajax_waiter_img);

            var input = that.val() + e.key;

            $.post(
                urlbase,
                {
                    service: 'shop',
                    what: 'checkpasscode',
                    productname: productname,
                    passcode: input
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    if (dataobj.status == 'passed') {
                        $('#ci-add-to-bag').attr('disabled', null);
                        $('#ci-pass-status-' + productname).html(ajax_success_img);
                        $('#cimod-' + productname).attr('disabled', null);
                        $('#cimod-pass-status-' + productname).html(ajax_success_img);
                    } else {
                        $('#ci-add-to-bag').attr('disabled', 'disabled');
                        $('#ci-pass-status-' + productname).html(ajax_failure_img);
                        $('#cimod-pass-status-' + productname).html(ajax_failure_img);
                    }
                },
                'html'
            );
        },
    };

    return shopproductdetail;

});