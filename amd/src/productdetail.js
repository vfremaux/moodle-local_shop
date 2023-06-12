/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var shopproductdetail = {

        init: function() {
            $('#ci-pass').bind('change', this.check_pass_code);
        },

        /*
         * Similar to front.js, but single product in page.
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