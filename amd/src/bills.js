/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var shopbills = {

        init: function() {
            $('#shop-flow-control-toggle').bind('click', this.flowcontrol_toggle);

            log.debug('LocalShop AMD bills initialized.');
        },

        flowcontrol_toggle: function () {
            if ($('#shop-flow-controller').css('visibility') === 'hidden') {
                $('#shop-flow-controller').css('visibility', 'visible');
                $('#shop-flow-controller').css('display', 'table');
                $('#shop-flow-control-toggle').src = cfg.wwwroot + "/local/shop/pix/minus.png";
            } else {
                $('#shop-flow-controller').css('visibility', 'hidden');
                $('#shop-flow-controller').css('display', 'none');
                $('#shop-flow-control-toggle').src = cfg.wwwroot + "/local/shop/pix/plus.png";
            }
        }
    };

    return shopbills;
});
