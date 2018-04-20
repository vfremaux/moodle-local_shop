

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var shoppro = {

        init: function() {

            $('#id_s_local_shop_licensekey').bind('change', this.check_product_key);
            $('#id_s_local_shop_licensekey').trigger('change');
            log.debug('AMD Pro js initialized for local shop');
        },

        check_product_key: function() {

            that = $(this);

            var productkey = that.val().replace(/-/g, '');
            var payload = productkey.substr(0, 14);
            var crc = productkey.substr(14, 2);

            var calculated = shoppro.checksum(payload);

            var validicon = ' <img src="' + cfg.wwwroot + '/pix/i/valid.png' + '">';
            var cautionicon = ' <img src="' + cfg.wwwroot + '/pix/i/warning.png' + '">';
            var invalidicon = ' <img src="' + cfg.wwwroot + '/pix/i/invalid.png' + '">';
            var waiticon = ' <img src="' + cfg.wwwroot + '/pix/i/ajaxloader.gif' + '">';

            if (crc == calculated) {
                url = cfg.wwwroot + '/local/shop/pro/ajax/services.php?';
                url += 'what=license';
                url += '&service=check';
                url += '&customerkey=' + that.val();
                url += '&provider=' + $('#id_s_local_shop_licenseprovider').val();

                $('#id_s_local_shop_licensekey + img').remove();
                $('#id_s_local_shop_licensekey').after(waiticon);

                $.get(url, function(data) {
                    if (data.match(/SET OK/)) {
                        $('#id_s_local_shop_licensekey + img').remove();
                        $('#id_s_local_shop_licensekey').after(validicon);
                    } else {
                        $('#id_s_local_shop_licensekey + img').remove();
                        $('#id_s_local_shop_licensekey').after(invalidicon);
                    }
                }, 'html');
            } else {
                $('#id_s_local_shop_licensekey + img').remove();
                $('#id_s_local_shop_licensekey').after(cautionicon);
            }
        },

        /**
         * Calculates a checksum on 2 chars.
         */
        checksum: function(keypayload) {

            var crcrange = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var crcrangearr =  crcrange.split('');
            var crccount = crcrangearr.length;
            var chars = keypayload.split('');
            var crc = 0;

            for (ch in chars) {
                var ord = chars[ch].charCodeAt(0);
                crc += ord;
            }

            var crc2 = Math.floor(crc / crccount) % crccount;
            var crc1 = crc % crccount;
            return '' + crcrangearr[crc1] + crcrangearr[crc2];
        }
    };

    return shoppro;
})