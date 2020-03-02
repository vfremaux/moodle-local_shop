
define(['jquery', 'core/log'], function($, log) {

    stripecheckout = {

        sid: '',
        pk: '',

        init: function(args) {
            $('#stripe-checkout-button').bind('click', this.checkout);
            var params = JSON.parse(args);
            this.pk = params.pk;
            this.sid = params.sid;

            log.debug('ADM shoppaymode strip checkout initialized with sid: ' + this.sid);
        },

        checkout: function() {
            var stripe = Stripe(stripecheckout.pk);

            stripe.redirectToCheckout({
                sessionId: stripecheckout.sid
            }).then(function (result) {
                $('#stripe-error').html(result.error.message);
            });
        }

    };

    return stripecheckout;
});