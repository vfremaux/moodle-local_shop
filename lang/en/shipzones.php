<?php

$string['addshipping'] = 'Add shipping';
$string['addshippingzone'] = 'Add new zone';
$string['applicability'] = 'A formula that triggers or disables applicability of the shipping'; 
$string['applicability'] = 'Applicability formula'; 
$string['billscopeamount'] = 'Bill amount scope';
$string['billscopeamountdesc'] = 'Part of the amount of the bill being considered for shipping';
$string['deleteshipping'] = 'Delete shipping';
$string['deletezone'] = 'Delete Shipping zone';
$string['formula'] = 'Formula';
$string['newshipping'] = 'New shipping';
$string['noshippings'] = 'No Shippings';
$string['nozones'] = 'No zones defined';
$string['param_a'] = 'Parameter \'$a\'';
$string['param_b'] = 'Parameter \'$b\'';
$string['param_c'] = 'Parameter \'$c\'';
$string['shippingfixedvalue'] = 'Fix ship cost';
$string['shippings'] = 'Product Shippings';
$string['shippingzone'] = 'Shipping zone';
$string['shipzone'] = 'Ship zone';
$string['shipzones'] = 'Shipping zones';
$string['editshippingzone'] = 'Edit shipping zone';
$string['usedentries'] = 'Used shipping entries';
$string['zonecode'] = 'Zone code';
$string['zoneid'] = 'Shipping zone id';

$string['shippingfixedvalue_help'] = '
#Shipping

When you can restrict the shipping calulation to a simple fix value per unit, then use this field
giving the shiping amount in the current currency of the shop.
';

$string['formula_help'] = '
#Shipping calculation

Give a parsable formula to calculate the shipping cost. You can use standard path functions (sqrt, arythmetic operators, log)
and use variables placeholders  as $ttc, $ht or one of the following parameters $a, $b or $c.
';
