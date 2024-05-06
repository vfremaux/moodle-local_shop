/*
 * Some form helpers to edit complex dynamic metadata arrays.
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log'], function($, log) {

    var shopmetadataedit = {

        /**
         * Initiates, placing events handler in content.
         */
        init: function() {
            $('input[name="editextradata"]').bind('click', this.openeditmodal);
            $('input[name="editproductiondata"]').bind('click', this.openeditmodal);
            $('input[name="edithandlerparams"]').bind('click', this.openeditmodal);
        },

        /**
         * Parses the textarea content and extracts property:value pairs.
         */
        parsevalues: function(values, format) {
            var arr, part, parts, valuearr = [];

            if (format === 'json') {
                // Jsonified format as {"key1":"val1", "key2":"val2",...}
                valuearr = JSON.parse(values);
            } else {
                // Url encoded format as key1=val1&key1=val2... etc.
                parts = values.split('&');
                for (part in parts) {
                    arr = parts[part].split('=');
                    valuearr[arr[0]] = arr[1];
                }
            }

            return valuearr;
        },

        /**
         * Recombines property::value array into the text area content.
         */
        recombinevalues: function(values, format) {
            var val;
            var valueparts = [];

            if (format === 'json') {
                // Jsonified format as {"key1":"val1", "key2":"val2",...}
                return JSON.stringify(values);
            } else {
                // Url encoded format as key1=val1&key1=val2... etc.
                for (val in values) {
                    valueparts.push(val + '=' + values[val]);
                }
                return valueparts.join('&');
            }
        },

        /**
         * Opens modal and build a local form from textarea value.
         */
        openeditmodal: function(e) {

            e.stopPropagation();
            e.preventDefault();

            log.debug("Opening modal for metadatas.");

            var that = $(this);
            var row;
            var rowname;
            var rowinput;
            var closebtn;
            var theform = $('#metadata-edit-modal-form');
            var themodal = $('#metadata-edit-modal-container');
            var associatedtextarea = $('[data-edithandle=' + that.attr('id') + ']');
            var format = associatedtextarea.attr('data-format');
            var values = shopmetadataedit.parsevalues(associatedtextarea.val(), format);
            var valkey;

            log.debug('Detected format is ' + format + '.');

            for (valkey in values) {
                row = $('<div/>', {
                    'class': 'mtd-row'
                });
                rowname = $('<div class="mtd-name">' + valkey + '</div>');
                rowinput = $('<div/>');
                rowinput.append($('<input/>', {
                    type: 'text',
                    size: 80,
                    value: values[valkey],
                    placeholder: valkey,
                    name: valkey
                }));
                row.append(rowname).append(rowinput);
                theform.append(row);
            }

            row = $('<div/>', {
                'class': 'mtd-row'
            });
            rowname = $('<div/>');
            rowname.append($('<input/>', {
                type: 'text',
                value: '',
                placeholder: 'New key',
                name: 'newkey'
            }));
            rowinput = $('<div/>');
            rowinput.append($('<input/>', {
                type: 'text',
                value: '',
                size: 80,
                placeholder: 'New value',
                name: 'newval'
            }));
            row.append(rowname).append(rowinput);
            theform.append(row);

            row = $('<div/>', {
                'class': 'mtd-row'
            });
            closebtn = $('<div/>');
            closebtn.append($('<input/>', {
                type: 'button',
                id: 'mtd-close',
                value: 'Save',
                'data-format': format,
                'data-for': associatedtextarea.attr('id'),
                'class': 'btn btn-primary'
            }));
            closebtn.append($('<input/>', {
                type: 'button',
                id: 'mtd-cancel',
                value: 'Cancel',
                'data-format': format,
                'data-for': associatedtextarea.attr('id'),
                'class': 'btn btn-primary'
            }));
            row.append($('<div/>')).append(closebtn);
            theform.append(row);
            $('#mtd-close').bind('click', shopmetadataedit.closeeditmodal);
            $('#mtd-cancel').bind('click', shopmetadataedit.canceleditmodal);
            themodal.css('display', 'block');
        },

        /**
         * Closes without saving.
         */
        canceleditmodal: function(e) {

            var themodal = $('#metadata-edit-modal-container');
            var theform = $('#metadata-edit-modal-form');

            e.stopPropagation();
            e.preventDefault();

            theform.empty();
            themodal.css('display', 'none');
        },

        /**
         * Saves back metadata and closes helper modal.
         */
        closeeditmodal: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);
            var themodal = $('#metadata-edit-modal-container');
            var theform = $('#metadata-edit-modal-form');
            var valuebuf;
            var format = that.attr('data-format');
            var newkey, newval;

            // Search all inputs in form.
            var inputs = $('input[type="text"]', theform);

            valuebuf = new Object();
            inputs.each(function () {
                if ($(this).attr('name') === 'newkey') {
                    newkey = $(this).val();
                } else if ($(this).attr('name') === 'newval') {
                    newval = $(this).val();
                } else {
                    valuebuf[$(this).attr('name')] = $(this).val();
                }
            });
            if (newkey) {
                valuebuf[newkey] = newval;
            }

            $('#' + that.attr('data-for')).val(shopmetadataedit.recombinevalues(valuebuf, format));
            theform.empty();
            themodal.css('display', 'none');
        }
    };

    return shopmetadataedit;
});
