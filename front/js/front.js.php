<?php
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

/**
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
header("Content-type: text/javascript");
header("Cache-Control: No-cache");

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$context = context_system::instance();
$PAGE->set_context($context);

$categories = $thecatalog->get_categories();
$shopproducts = $thecatalog->get_all_products($categories);

$units = 0;
if (isset($SESSION->shoppingcart->order)) {
    foreach ($SESSION->shoppingcart->order as $shortname => $q) {
        $units += $q;
    }
}

// Calculates and updates the seat count.
$requiredroles = $thecatalog->check_required_roles();
$required = $thecatalog->check_required_seats();
$assigned = shop_check_assigned_seats($requiredroles);
$notassignedstr = str_replace("'", '\\\'', get_string('notallassigned', 'local_shop'));
$myorderstr = str_replace("'", '\\\'', get_string('emptyorder', 'local_shop'));
$invalidemailstr = get_string('invalidemail', 'local_shop');
<<<<<<< HEAD
?>

function openPopup(target) {
   win = window.open(target, "product", "width=400,height=500,toolbar=0,menubar=0,statusbar=0");
}

function openSalesPopup() {
    winparams = "width=600,height=600,toolbar=0,menubar=0,statusbar=0, resizable=1,scrollbars=1";
    win = window.open(M.cfg.wwwroot + "/local/shop/popup.php?p=sales", "sales", winparams);
}

function showcategory(catid, allids) {
    allidsarr = allids.split(',');

    c = 0;
    for (hidecatid in allidsarr) {
        tohidetabid = '#catli' + allidsarr[hidecatid];
        $(tohidetabid).removeClass('active');
        if (c === 0) {
            $(tohidetabid).addClass('first');
        }

        if (c === allidsarr.length - 1) {
            $(tohidetabid).addClass('last');
        }
        $('#category' + allidsarr[hidecatid]).css('visibility', 'hidden');
        $('#category' + allidsarr[hidecatid]).css('display', 'none');
    }

    $('#catli'+catid).addClass('active');
    $('#category' + catid).css('visibility', 'visible');
    $('#category' + catid).css('display', 'block');
}

// this early loads from server
var required = '<?php echo $required; ?>';
var assigned = '<?php echo $assigned; ?>';

function ajax_waiter() {
    return '<div class="ajax-waiter">' +
                  '<center>' +
                  '<img src="' + M.cfg.wwwroot + '/local/shop/pix/loading29.gif" />' +
                  '<center>' +
                  '</div>';
}

function ajax_add_user(formobj) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    // Kind a very simple serialize/unserialize.
    rolelist = '<?php echo implode(',', $requiredroles); ?>';
    roles = rolelist.split(',');
<?php if (isset($SESSION->shoppingcart->order)) {
    echo 'productlist = \''.implode(',', array_keys($SESSION->shoppingcart->order))."';\n";
} else {
    echo "productlist = '';\n";
} ?>
    products = productlist.split(',');

    pt = new Object();
    pt.lastname = formobj.lastname.value;
    pt.firstname = formobj.firstname.value;
    pt.email = formobj.email.value;
    pt.city = formobj.city.value;
<?php if (!empty($theshop->enduserorganisationrequired)) { ?>
    pt.institution = formobj.institution.value;
<?php }
if (!empty($theshop->endusermobilephonerequired)) { ?>
    pt.phone2 = formobj.phone2.value;
<?php } ?>

    $('#participantlist').html(ajax_waiter());

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'users',
            what: 'addparticipant',
            participant: JSON.stringify(pt),
            roles: JSON.stringify(roles)
        },
        function(data, status) {
            $('#participantlist').html(data);
            formobj.lastname.value = '';
            formobj.firstname.value = '';
            formobj.email.value = '';
            // Keep city and institution values to speed up input
            // formobj.city.value = '';
<?php
if (!empty($theshop->enduserorganisationrequired)) {
?>
            // formobj.institution.value = '';
<?php
}
if (!empty($theshop->endusermobilephonerequired)) {
?>
            formobj.phone2.value = '';
<?php
}
?>

            for (i = 0; i < roles.length; i++) {
                for (j = 0; j < products.length; j++) {
                    $('#' + roles[i] + 'list' + products[j]).html(ajax_waiter);
                }
            }

            $.post(
                urlbase,
                {
                    id: '<?php echo $theshop->id ?>',
                    service: 'users',
                    what: 'assignalllistobj',
                },
                function(data, status) {
                    obj = JSON.parse(data);
                    obj.content;
                    for (i = 0; i < roles.length; i++) {
                        r = roles[i];
                        for (j = 0; j < products.length; j++) {
                            p = products[j];
                            html = obj.content[r][p];
                            $('#' + r + 'list' + p).html(html);
                        }
                    }
                }
            );
        }
    );
}

function ajax_delete_user(ptmail) {

    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    // Kind a very simple serialize/unserialize.
    rolelist = '<?php echo implode(',', $requiredroles); ?>';
    roles = rolelist.split(',');
<?php
if (isset($SESSION->shoppingcart->order)) {
    echo 'productlist = \''.implode(',', array_keys($SESSION->shoppingcart->order))."';\n";
} else {
    echo "productlist = '';\n";
}
?>
    products = productlist.split(',');

    $('#participantlist').html(ajax_waiter());

    $.post(urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'users',
            what: 'deleteparticipant',
            participantid: ptmail,
            roles: JSON.stringify(roles)
        },
        function(data, status) {
            $('#participantlist').html(data);

            for (i = 0; i < roles.length; i++) {
                for (j = 0; j < products.length; j++) {
                    $('#' + roles[i] + 'list' + products[j]).html(ajax_waiter);
                }
            }

            $.post(
                urlbase,
                {
                    id: '<?php echo $theshop->id ?>',
                    service: 'users',
                    what: 'assignalllistobj',
                },
                function(data, status) {
                    obj = JSON.parse(data);
                    obj.content;
                    for (i = 0; i < roles.length; i++) {
                        r = roles[i];
                        for (j = 0; j < products.length; j++) {
                            p = products[j];
                            html = obj.content[r][p];
                            $('#' + r + 'list' + p).html(html);
                        }
                    }
                }
            );
        }
    );
}

function ajax_add_assign(assignrole, product, selectobj) {

    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    requiredroles = JSON.parse('<?php echo json_encode($thecatalog->check_required_roles()); ?>');

    for (rix in requiredroles) {
        role = requiredroles[rix];
        $('#'+role+'list' + product).html(ajax_waiter());
    }

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'users',
            what: 'addassign',
            role:assignrole,
            product: product,
            participantid: selectobj.options[selectobj.selectedIndex].value
        },
        function(data, status) {
            rolestubs = JSON.parse(data);
            for (rix in requiredroles) {
                role = requiredroles[rix];
                $('#'+role + 'list'+product).html(rolestubs.content[role]);
            }

            // this need be done on positive return or we might unsync
            assigned++;
            if (assigned < required) {
                $('#next-button').css('opacity', '0.5');
                $('#next-button').removeClass('shop-active-button');
                $('#next-button').attr('disabled', 'disabled');
                $('#next-button').attr('title', '<?php echo $notassignedstr ?>');
            } else {
                $('#next-button').css('opacity', '1.0');
                $('#next-button').addClass('shop-active-button');
                $('#next-button').attr('disabled', null);
                $('#next-button').attr('title', '<?php print_string('continue', 'local_shop') ?>');
            }
        }
    );
}

function ajax_delete_assign(assignrole, product, email) {
    urlbase = M.cfg.wwwroot+'/local/shop/front/ajax/service.php';

    requiredroles = JSON.parse('<?php echo json_encode($thecatalog->check_required_roles()); ?>');

    for (rix in requiredroles) {
        role = requiredroles[rix];
        $('#'+role+'list'+product).html(ajax_waiter());
    }

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'users',
            what: 'deleteassign',
            role: assignrole,
            product: product,
            participantid: email
        },
        function(data, status) {
            rolestubs = JSON.parse(data);
            for (rix in requiredroles) {
                role = requiredroles[rix];
                $('#'+role+'list' + product).html(rolestubs.content[role]);
            }
            assigned--;
            if (assigned < 0) assigned = 0; // security, should not happen
            if (assigned < required) {
                $('#next-button').css('opacity', '0.5');
                $('#next-button').removeClass('shop-active-button');
                $('#next-button').attr('disabled', 'disabled');
                $('#next-button').attr('title', '<?php echo $notassignedstr ?>');
            } else {
                $('#next-button').css('opacity', '1.0');
                $('#next-button').addClass('shop-active-button');
                $('#next-button').attr('disabled', null);
                $('#next-button').attr('title', '<?php print_string('continue', 'local_shop') ?>');
            }
        }
    );
}

// This early loads from server.
var units = '<?php echo $units; ?>';

/*
 * @TODO id to remove
 *
 */
function ajax_add_unit(id, productname, maxquant) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    $('#bag_' + productname).html(ajax_waiter());

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'shop',
            what: 'addunit',
            productname: productname
        },

        function(data, status) {
            dataobj = JSON.parse(data);
            $('#bag_' + productname).html(dataobj.html);

            if ((maxquant > 0) && (dataobj.quant >= maxquant)) {
                $('#ci-' + productname).attr('disabled', 'disabled');
            }

            ajax_update_details(id);
            ajax_update_totals(id);

        }
    );

    units++;
    $('#next-button').attr('disabled', null);
    $('#next-button').addClass('shop-active-button');
    $('#next-button').css('opacity', '1.0');
    $('#next-button').attr('title', '<?php print_string('continue', 'local_shop') ?>');
}

function ajax_delete_unit(id, productname) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    $('#bag_' + productname).html(ajax_waiter());

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'shop',
            what: 'deleteunit',
            productname: productname,
            clearall: 0
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            $('#bag_' + productname).html(dataobj.html);

            ajax_update_details(id);
            ajax_update_totals(id);
            $('#ci-' + productname).attr('disabled', null);
        }
    );

    units--;
    if (units == 0) {
        $('#next-button').attr('disabled', 'disabled');
        $('#next-button').removeClass('shop-active-button');
        $('#next-button').css('opacity', '0.5');
        $('#next-button').attr('title', '<?php echo $myorderstr ?>');
    }
}

function ajax_update_totals(id) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    $('#shop-ordertotals').html(ajax_waiter());

    $.post(
        urlbase,
        {
            id: id,
            service: 'shop',
            what: 'ordertotals'
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            $('#shop-ordertotals').html(dataobj.html);
        }
    );
}

function ajax_update_details(id) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    $('#order-detail').html(ajax_waiter());
    $.post(
        urlbase,
        {
            id: id,
            service: 'shop',
            what: 'orderdetails'
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            $('#order-detail').html(dataobj.html);
        }
    );
}

function ajax_clear_product(id, productname) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    $('#bag_' + productname).html(ajax_waiter());

    $('#id_' + productname).val(0);
    $('#id_total_' + productname).val(0);

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'shop',
            what: 'deleteunit',
            productname: productname,
            clearall: 1
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            $('#bag_' + productname).html(dataobj.html);

            ajax_update_details(id);
            ajax_update_totals(id);
        }
    );
}

function ajax_update_product(id, productname, maxquant) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';

    currentval = $('#id_'+productname).val();
    if (maxquant > 0 && currentval > maxquant) {
        currentval = maxquant;
    }

    $('#id_'+productname).val(currentval);
    $('#bag_'+productname).html(ajax_waiter());

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'shop',
            what: 'setunits',
            productname: productname,
            quant: currentval
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            $('#bag_'+productname).html(dataobj.html);

            ajax_update_details(id);
            ajax_update_totals(id);
        }
    );
}

function local_toggle_invoiceinfo(check) {
    if (check.checked) {
        $('#shop-invoiceinfo-wrapper').css('display', 'block');
        if (document.driverform.elements['invoiceinfo::organisation'].value == '') {
            elm = document.driverform.elements['invoiceinfo::organisation'];
            elm.value = document.driverform.elements['customerinfo::organisation'].value
        }
        if (document.driverform.elements['invoiceinfo::city'].value == '') {
            elm = document.driverform.elements['invoiceinfo::city'];
            elm.value = document.driverform.elements['customerinfo::city'].value
        }
    } else {
        $('#shop-invoiceinfo-wrapper').css('display', 'none');
    }
}

function check_pass_code(productname, textinput, event) {
    urlbase = M.cfg.wwwroot + '/local/shop/front/ajax/service.php';
    ajax_waiter_img = '<img width="14" height="14" src="' + M.cfg.wwwroot + '/local/shop/pix/ajaxloader.gif" />';
    ajax_success_img = '<img width="14" height="14" src="' + M.cfg.wwwroot + '/local/shop/pix/valid.png" />';
    ajax_failure_img = '<img width="14" height="14" src="' + M.cfg.wwwroot + '/local/shop/pix/invalid.png" />';

    $('#ci-pass-status-'+productname).html(ajax_waiter_img);

    var input = textinput.value + event.key;

    $.post(
        urlbase,
        {
            id: '<?php echo $theshop->id ?>',
            service: 'shop',
            what: 'checkpasscode',
            productname: productname,
            passcode: input
        },
        function(data, status) {
            dataobj = JSON.parse(data);
            if (dataobj.status == 'passed') {
                $('#ci-'+productname).attr('disabled', null);
                $('#ci-pass-status-'+productname).html(ajax_success_img);
            } else {
                $('#ci-pass-status-'+productname).html(ajax_failure_img);
            }
        }
    );
}


function checkemail(textselect) {
    if (/^\w+([\.+-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(textselect.value)) {
        return (true);
    }
    alert("<?php echo $invalidemailstr ?>");
    textselect.value = '';
    return (false);
}
=======
>>>>>>> MOODLE_40_STABLE
