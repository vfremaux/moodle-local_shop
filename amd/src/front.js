/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var shopfront = {

        eulas: 'required',

        shopid: 0,

        init: function(params) {
            // Get all description toggles and bind them.
            $('.shop-description-toggle').bind('click', this.toggle_description);

            if (params) {
                shopfront.shopid = params.shopid;
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

            if (that.attr('src').match(/open/)) {
                that.attr('src', that.attr('src').replace('open', 'close'));
                that.parent().parent().children('.shop-front-description').removeClass('shop-desc-gradient');
            } else {
                that.attr('src', that.attr('src').replace('close', 'open'));
                that.parent().parent().children('.shop-front-description').addClass('shop-desc-gradient');
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

                url = cfg.wwwroot + '/local/shop/front/ajax/service.php?what=agreeeulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;
            } else {
                url = cfg.wwwroot + '/local/shop/front/ajax/service.php?what=reseteulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;
            }

            // Switch state in session to avoid agreeing eulas again (until order changes).
            $.get(url);
        },

        reset_eulas: function () {
            if (shopfront.eulas == 'agreed') {
                var url = cfg.wwwroot + '/local/shop/front/ajax/service.php?what=reseteulas';
                url += '&service=order';
                url += '&id=' + shopfront.shopid;

                // Switch state in session to avoid agreeing eulas again (until order changes).
                $.get(url);
            }
        }
    };

    return shopfront;

});
