<?php

$string['enabletest'] = 'Test payment';
$string['enabletest2'] = 'Test payment';
$string['enabletest3'] = 'You choosed using shop test payment...';
$string['pluginname'] = 'Test Payment';
$string['test'] = 'Test payment';
$string['interactivepay'] = 'Interactive payment';
$string['ipnpay'] = 'IPN Trigger without closing';
$string['ipnpayandclose'] = 'IPN Trigger with transaction closing';
$string['paydelayedforipn'] = 'Payment with IPN deferred';

$string['testadvice'] = '
<p>This is a fake simulated payment method to allow processing tests on the end of the purchase path. Never use in production mode, or real customers could buy products without effective fund transaction !</p>
<p>Here you can trigger four testing actions : </p>
<ul><li><b>Direct pay:</b> As fastest way to validate a bill and get production triggered. This will never represent a real transaction as transaction is either delayed for payment confirmation (offline), or delayed for some Online IPN asynchronous returns.</li>
<li><b>Triggering payement with IPN</b>: This is the simulation of most online payment methods, as real operations will be processed on IPN return. This may simulate when a customer comes back from
payment service interface and returns back to the site.</li>
<li><b>IPN Return without termination</b>: This activates a simulation of an IPN return in a separate window. You can use this trigger before the above link and check order is complete. the order is not clesed so it can be played again for test purpose.</li>
<li><b>IPN Trigger with termination</b>: This activates a simulation of an IPN return in a separate window. You can use this trigger before the above link and check order is complete. the order will be terminated and the shopping cart set back to empty state.</li>
</ul>';

$string['pending_followup_text_tpl'] = '
<p>Purchase is waiting for IPN return. Processing will be done on IPN runtime.</p>
<p>Test suport info: <%%SUPPORT%%>.</p>
';

$string['success_followup_text_tpl'] = '
<p>Test payment has been registered in DB. Products are being processed.</p>
<p>Test support information: <%%SUPPORT%%>.</p>
';
