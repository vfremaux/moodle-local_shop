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
