/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config', 'core/str'], function($, log, cfg, str) {

    var shopfront = {

        eulas: 'required',

        shopid: 0,

        units: 0,

        required: 0,

        assigned: 0,

        endusermobilephonerequired: 0,

        // Strings mapping :
        // 0 =>
        // 1 => str_replace("'", '\\\'', get_string('notallassigned', 'local_shop'))
        // 2 => 'continue', 'local_shop'
        // 3 => str_replace("'", '\\\'', get_string('emptyorder', 'local_shop'))
        strings: [],

        init: function(params) {

            var stringdefs = [
                {key: 'invalidemail', component: 'local_shop'}, // 0
                {key: 'notallassigned', component: 'local_shop'}, // 1
                {key: 'continue', component: 'local_shop'}, // 2
                {key: 'emptyorder', component: 'local_shop'}, // 3
            ];

            str.get_strings(stringdefs).done(function(s) {
                shopfront.strings = s;
            });

            // Get all description toggles and bind them.
            $('.shop-description-toggle').bind('click', this.toggle_description);
            $('.local-shop-detail-delete').bind('click', this.clear_product);
            $('.local-shop-order-detail').bind('change', this.update_product);
            $('.local-shop-password').bind('keypress', this.check_pass_code);
            $('.local-shop-email').bind('change', this.check_email);
            $('.local-shop-add-unit').bind('click', this.add_unit);
            $('.local-shop-delete-unit').bind('click', this.delete_unit);
            $('.local-shop-add-assign').bind('change', this.add_assign);
            $('.local-shop-delete-assign').bind('click', this.delete_assign);
            $('.local-shop-toggle-invoiceinfo').bind('change', this.toggle_invoiceinfo);
            $('.local-shop-delete-user').bind('click', this.delete_user);
            $('.local-shop-add-user').bind('click', this.add_user);

            if (params) {
                shopfront.shopid = params.shopid;
                shopfront.units = params.units;
                shopfront.required = params.required;
                shopfront.assigned = params.assigned;
                shopfront.endusermobilephonerequired = params.endusermobilephonerequired;
            }

            log.debug('AMD Local Shop Front initialized');
        },

        initeulas: function(params) {
            // Get all description toggles and bind them.
            $('#shop-eula-confirm').bind('click', this.accept_eulas);

            if (params) {
                if (params.eulas === 'required') {
                    $('#region-pre').css('display', 'none');
                }
                shopfront.eulas = params.eulas;
            }

            log.debug('AMD Local Shop Front Eulas initialized');
        },

        toggle_description: function() {

            var that = $(this);

            if (that.attr('src').match('/open/')) {
                that.attr('src', that.attr('src').replace('open', 'close'));
                that.parent().parent().children('.shop-front-description').removeClass('shop-desc-gradient');
            } else {
                that.attr('src', that.attr('src').replace('close', 'open'));
                that.parent().parent().children('.shop-front-description').addClass('shop-desc-gradient');
            }
        },

        toggle_invoiceinfo: function() {

            var elm;

            if (this.checked) {
                $('#shop-invoiceinfo-wrapper').css('display', 'block');
                if (document.driverform.elements['invoiceinfo::organisation'].value == '') {
                    elm = document.driverform.elements['invoiceinfo::organisation'];
                    elm.value = document.driverform.elements['customerinfo::organisation'].value;
                }
                if (document.driverform.elements['invoiceinfo::city'].value == '') {
                    elm = document.driverform.elements['invoiceinfo::city'];
                    elm.value = document.driverform.elements['customerinfo::city'].value;
                }
            } else {
                $('#shop-invoiceinfo-wrapper').css('display', 'none');
            }
        },

        accept_eulas: function() {

            var url;

            if ($('#shop-agreeeula').val() == 1) {
                $('#euladiv').css('display', 'none');
                $('#order').css('display', 'block');
                $('#order').css('visibility', 'show');
                $('#region-pre').css('display', 'block');
                $('#region-pre').css('visibility', 'show');

                url = cfg.wwwroot + '/local/shop/front/ajax/service.php';
                url += '?what=agreeeulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;
            } else {
                url = cfg.wwwroot + '/local/shop/front/ajax/service.php';
                url += '?what=reseteulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;
            }

            // Switch state in session to avoid agreeing eulas again (until order changes).
            $.get(url);
        },

        reset_eulas: function () {
            if (shopfront.eulas == 'agreed') {
                var url = cfg.wwwroot + '/local/shop/front/ajax/service.php';
                url += '?what=reseteulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;

                // Switch state in session to avoid agreeing eulas again (until order changes).
                $.get(url);
            }
        },

        add_assign: function() {

            var that = $(this);
            var assignrole = that.attr('data-role');
            var product = that.attr('data-product');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            var requiredroles = that.attr('data-requiredroles').split(',');
            var role, rix;

            for (rix in requiredroles) {
                role = requiredroles[rix];
                $('#addassign' + role + 'list' + product).html(shopfront.waiter());
            }

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'users',
                    what: 'addassign',
                    role: assignrole,
                    product: product,
                    participantid: this.options[this.selectedIndex].value
                },
                function(data) {
                    var rolestubs = JSON.parse(data);
                    for (rix in requiredroles) {
                        role = requiredroles[rix];
                        $('#addassign' + role + 'list' + product).html(rolestubs.content[role]);
                    }

                    // this need be done on positive return or we might unsync
                    shopfront.assigned++;
                    if (shopfront.assigned < shopfront.required) {
                        $('#next-button').css('opacity', '0.5');
                        $('#next-button').removeClass('shop-active-button');
                        $('#next-button').attr('disabled', 'disabled');
                        $('#next-button').attr('title', shopfront.strings[1]);
                    } else {
                        $('#next-button').css('opacity', '1.0');
                        $('#next-button').addClass('shop-active-button');
                        $('#next-button').attr('disabled', null);
                        $('#next-button').attr('title', shopfront.strings[2]);
                    }
                },
                'html'
            );
        },

        delete_assign: function(assignrole, product, email) {

            var that = $(this);

            var assignrole = that.attr('data-role');
            var product = that.attr('data-product');
            var email = that.attr('data-email');

            var urlbase = cfg.wwwroot+'/local/shop/front/ajax/service.php';

            var requiredroles = that.attr('data-requiredroles').split(',');
            var role,rix;

            for (rix in requiredroles) {
                role = requiredroles[rix];
                $('#assignrole' + role + 'list' + product).html(shopfront.waiter());
            }

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'users',
                    what: 'deleteassign',
                    role: assignrole,
                    product: product,
                    participantid: email
                },
                function(data) {
                    var rolestubs = JSON.parse(data);
                    for (rix in requiredroles) {
                        role = requiredroles[rix];
                        $('#assignrole' + role + 'list' + product).html(rolestubs.content[role]);
                    }
                    shopfront.assigned--;
                    if (shopfront.assigned < 0) {
                        shopfront.assigned = 0; // security, should not happen
                    }
                    if (shopfront.assigned < shopfront.required) {
                        $('#next-button').css('opacity', '0.5');
                        $('#next-button').removeClass('shop-active-button');
                        $('#next-button').attr('disabled', 'disabled');
                        $('#next-button').attr('title', shopfront.strings[1]);
                    } else {
                        $('#next-button').css('opacity', '1.0');
                        $('#next-button').addClass('shop-active-button');
                        $('#next-button').attr('disabled', null);
                        $('#next-button').attr('title', shopfront.strings[2]);
                    }
                },
                'html'
            );
        },

        /*
         * @TODO id to remove
         *
         */
        add_unit: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var productname = that.attr('data-product');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            $('#bag_' + productname).html(shopfront.waiter());

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'addunit',
                    productname: productname
                },

                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#bag_' + productname).html(dataobj.html);

                    shopfront.update_details();
                    shopfront.update_totals();

                    var maxquant = that.attr('data-maxquant');
                    log.debug(maxquant + '>=' + dataobj.quant);
                    if ((maxquant > 0) && (dataobj.quant >= maxquant)) {
                        $('#ci-' + productname).attr('disabled', 'disabled');
                        $('#cimod-' + productname).attr('disabled', 'disabled');
                    }

                },
                'html'
            );

            shopfront.units++;
            $('#next-button').attr('disabled', null);
            $('#next-button').addClass('shop-active-button');
            $('#next-button').css('opacity', '1.0');
            $('#next-button').attr('title', shopfront.strings[2]);
        },

        delete_unit: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var productname = that.attr('data-product');
            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            $('#bag_' + productname).html(shopfront.waiter());

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'deleteunit',
                    productname: productname,
                    clearall: 0
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#bag_' + productname).html(dataobj.html);

                    shopfront.update_details();
                    shopfront.update_totals();

                    log.debug('enabling product add button ' + productname);
                    $('#ci-' + productname).attr('disabled', null);
                    $('#cimod-' + productname).attr('disabled', null);
                },
                'html'
            );

            shopfront.units--;
            if (shopfront.units == 0) {
                $('#next-button').attr('disabled', 'disabled');
                $('#next-button').removeClass('shop-active-button');
                $('#next-button').css('opacity', '0.5');
                $('#next-button').attr('title', shopfront.strings[3]);
            }
        },

        clear_product: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var productname = that.attr('data-product');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            $('#bag_' + productname).html(shopfront.waiter());

            $('#id_' + productname).val(0);
            $('#ci-' + productname).attr('disabled', null);
            $('#id_total_' + productname).val(0);

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'deleteunit',
                    productname: productname,
                    clearall: 1
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#bag_' + productname).html(dataobj.html);

                    shopfront.update_details();
                    shopfront.update_totals();
                },
                'html'
            );
        },

        update_product: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var productname = that.attr('data-product');
            var maxquant = that.attr('data-maxquant');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            var currentval = $('#id_' + productname).val();
            if (maxquant > 0 && currentval > maxquant) {
                currentval = maxquant;
            }

            $('#id_' + productname).val(currentval);
            $('#bag_' + productname).html(shopfront.waiter());

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'setunits',
                    productname: productname,
                    quant: currentval
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#bag_' + productname).html(dataobj.html);

                    shopfront.update_details();
                    shopfront.update_totals();
                },
                'html'
            );
        },

        update_totals: function() {

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            $('#shop-ordertotals').html(shopfront.waiter());

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'ordertotals'
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#shop-ordertotals').html(dataobj.html);
                    $('.local-shop-detail-delete').unbind('click');
                    $('.local-shop-detail-delete').bind('click', shopfront.clear_product);
                    $('.local-shop-order-detail').unbind('change');
                    $('.local-shop-order-detail').bind('change', shopfront.update_product);
                },
                'html'
            );
        },

        update_details: function() {

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            $('#order-detail').html(shopfront.waiter());
            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'orderdetails'
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    $('#order-detail').html(dataobj.html);
                    $('.local-shop-delete-unit').unbind('click');
                    $('.local-shop-delete-unit').bind('click', shopfront.delete_unit);
                },
                'html'
            );
        },

        /**
         * Adds a user in the participant list.
         * Update role assign selection list.
         */
        add_user: function() {

            var that = $(this);
            var formobj = document.forms['participant'];

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            // Kind a very simple serialize/unserialize.
            var rolelist = that.attr('data-requiredroles');
            var roles = rolelist.split(',');
            var productlist = that.attr('data-products');
            var products = productlist.split(',');

            var pt = new Object();
            pt.lastname = formobj.lastname.value;
            pt.firstname = formobj.firstname.value;
            pt.email = formobj.email.value;
            pt.city = formobj.city.value;
            if (!shopfront.enduserorganisationrequired) {
                pt.institution = formobj.institution.value;
            }

            if (!(shopfront.endusermobilephonerequired)) {
                pt.phone2 = formobj.phone2.value;
            }

            $('#participantlist').html(shopfront.waiter());

            $.post(
                urlbase,
                {
                    id: shopfront.shopid,
                    service: 'users',
                    what: 'addparticipant',
                    participant: JSON.stringify(pt),
                    roles: JSON.stringify(roles)
                },
                function(data) {

                    var formobj;
                    var i,j;

                    $('#participantlist').html(data);
                    // Rebind delete icons.
                    $('.local-shop-delete-user').unbind('click');
                    $('.local-shop-delete-user').bind('click', shopfront.delete_user);

                    // Reset firstname, lastname and email for next user.
                    formobj = document.forms['participant'];
                    formobj.lastname.value = '';
                    formobj.firstname.value = '';
                    formobj.email.value = '';
                    // Keep city and institution values to speed up input
                    // formobj.city.value = '';
                    if (!(shopfront.endusermobilephonerequired)) {
                        formobj.phone2.value = '';
                    }

                    var availables = that.attr('data-availableseats');
                    availables--;
                    that.attr('data-availableseats', availables);
                    if (availables === 0) {
                        that.attr('disabled', 'disabled');
                    }

                    for (i = 0; i < roles.length; i++) {
                        for (j = 0; j < products.length; j++) {
                            $('#' + roles[i] + 'list' + products[j]).html(shopfront.waiter());
                        }
                    }

                    $.post(
                        urlbase,
                        {
                            id: shopfront.shopid,
                            service: 'users',
                            what: 'assignalllistobj',
                        },
                        function(data) {
                            var i, j, r, p, obj, html;

                            obj = JSON.parse(data);
                            for (i = 0; i < roles.length; i++) {
                                r = roles[i];
                                for (j = 0; j < products.length; j++) {
                                    p = products[j];
                                    html = obj.content[r][p];
                                    $('#' + r + 'list' + p).html(html);
                                }
                            }
                            $('.local-shop-add-assign').unbind('change');
                            $('.local-shop-add-assign').bind('change', this.add_assign);
                        },
                        'html'
                    );
                },
                'html'
            );
        },

        delete_user: function() {

            var that = $(this);
            var ptmail = that.attr('data-ptmail');

            var urlbase = cfg.wwwroot + '/local/shop/front/ajax/service.php';

            // Kind a very simple serialize/unserialize.
            var rolelist = that.attr('data-requiredroles');
            var roles = rolelist.split(',');
            var productlist = that.attr('data-products');
            var products = productlist.split(',');

            $('#participantlist').html(shopfront.waiter());

            $.post(urlbase,
                {
                    id: shopfront.shopid,
                    service: 'users',
                    what: 'deleteparticipant',
                    participantid: ptmail,
                    roles: JSON.stringify(roles)
                },
                function(data) {
                    var i,j;

                    $('#participantlist').html(data);
                    // Rebind delete icons.
                    $('.local-shop-delete-user').unbind('click');
                    $('.local-shop-delete-user').bind('click', shopfront.delete_user);

                    var availables = that.attr('data-availableseats');
                    that.attr('data-availableseats', availables + 1);
                    that.attr('disabled', null);
                    $('#addparticipant-line').css('display', 'block');

                    for (i = 0; i < roles.length; i++) {
                        for (j = 0; j < products.length; j++) {
                            $('#' + roles[i] + 'list' + products[j]).html(shopfront.waiter());
                        }
                    }

                    $.post(
                        urlbase,
                        {
                            id: shopfront.shopid,
                            service: 'users',
                            what: 'assignalllistobj',
                        },
                        function(data) {
                            var i, j, r, p, obj, html;
                            obj = JSON.parse(data);
                            for (i = 0; i < roles.length; i++) {
                                r = roles[i];
                                for (j = 0; j < products.length; j++) {
                                    p = products[j];
                                    html = obj.content[r][p];
                                    $('#' + r + 'list' + p).html(html);
                                }
                            }
                            $('.local-shop-add-assign').unbind('change');
                            $('.local-shop-add-assign').bind('change', this.add_assign);
                        },
                        'html'
                    );
                }
            );
        },

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
                    id: shopfront.shopid,
                    service: 'shop',
                    what: 'checkpasscode',
                    productname: productname,
                    passcode: input
                },
                function(data) {
                    var dataobj = JSON.parse(data);
                    if (dataobj.status == 'passed') {
                        $('#ci-' + productname).attr('disabled', null);
                        $('#ci-pass-status-' + productname).html(ajax_success_img);
                        $('#cimod-' + productname).attr('disabled', null);
                        $('#cimod-pass-status-' + productname).html(ajax_success_img);
                    } else {
                        $('#ci-pass-status-' + productname).html(ajax_failure_img);
                        $('#cimod-pass-status-' + productname).html(ajax_failure_img);
                    }
                },
                'html'
            );
        },

        check_email: function() {

            var that = $(this);

            if (/^\w+([\.+-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(that.val())) {
                return (true);
            }
            alert(shopfront.strings[0]);
            that.val('');
            return (false);
        },

        waiter: function() {
            return '<div class="ajax-waiter">' +
              '<center>' +
              '<img src="' + cfg.wwwroot + '/local/shop/pix/loading29.gif" />' +
              '<center>' +
              '</div>';
        },

        open_popup: function(target) {
            var that = $(this);
            var target = that.attr('data-target');
            window.open(target, "product", "width=400,height=500,toolbar=0,menubar=0,statusbar=0");
        },

        openSalesPopup: function() {
            var winparams = "width=600,height=600,toolbar=0,menubar=0,statusbar=0, resizable=1,scrollbars=1";
            window.open(cfg.wwwroot + "/local/shop/popup.php?p=sales", "sales", winparams);
        }

    };

    return shopfront;

});
