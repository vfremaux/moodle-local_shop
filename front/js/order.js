/*
 * jshint undef:false, unused:false
 */
// jshint undef:false, unused:false

function send_confirm(){
    document.forms.bill.cmd.value = 'confirm';
    document.forms.bill.view.value = 'success';
    document.forms.bill.action = '/local/shop/front/view.php';
    document.forms.bill.target = '_self';
    document.forms.bill.submit();
}

var requiredorderfieldlist = null;

function haverequireddata() {
    if (requiredorderfieldlist === null) {
        return true;
    }
    for (i = 0; i < requiredorderfieldlist.length; i++) {
        if (document.forms.bill.elements[requiredorderfieldlist[i]].value === '') {
            return false;
        }
    }

    return true;
}

function listen_to_required_changes() {
    if (haverequireddata()){
        document.forms.confirmation.elements.go_confirm.disabled = false;
        advicediv = document.getElementById('shop-disabled-advice-span');
        advicediv.style.visibility = 'hidden';
    } else {
        document.forms.confirmation.elements.go_confirm.disabled = true;
        advicediv = document.getElementById('shop-disabled-advice-span');
        advicediv.style.visibility = 'visible';
    }
}

function accept_eulas(buttonobj){
    if (buttonobj.form.agreeeula.checked){
        agreediv = document.getElementById('euladiv');
        agreediv.style.display = 'none';
        agreediv.style.visibility = 'hidden';
        orderdiv = document.getElementById('order');
        orderdiv.style.display = 'block';
        orderdiv.style.visibility = 'show';
        blocksdiv = document.getElementById('region-pre');
        blocksdiv.style.display = 'block';
        blocksdiv.style.visibility = 'show';
    }
}

function delay_follow_up() {
    $('#prod-waiter').css('display', 'none');
    $('#shop-notification-message-followup').removeClass('shop-message-hidden');
}

function delay_results() {
    $('#shop-notification-result').removeClass('shop-message-hidden');
    $('#shop-continue-form').removeClass('shop-message-hidden');
}

setTimeout(delay_follow_up, 2000);

setTimeout(delay_results, 5000);
