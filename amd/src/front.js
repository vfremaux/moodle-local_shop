

define(['jquery', 'core/log'], function($, log) {

    var shopfront = {

        init: function() {
            // Get all descirption toggles and bind them
            $('.shop-description-toggle').bind('click', this.toggle_description);

            log.debug('AMD Local Shop Front initialized');
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
        }
    };

    return shopfront;

});
