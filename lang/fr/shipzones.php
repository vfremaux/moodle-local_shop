<?php

$string['addshipping'] = 'Ajouter un frais de port';
$string['addshippingzone'] = 'Nouvelle zone';
$string['applicability'] = 'Formule d\'applicabilité'; 
$string['applicability_desc'] = 'Une formule qui active ou inhibe l\'application du port'; 
$string['billscopeamount'] = 'Part affectée';
$string['billscopeamount_desc'] = 'Part du montant de facture affecté par le port.';
$string['deleteshipping'] = 'Supprimer un frais de port';
$string['deletezone'] = 'Supprimer une zone';
$string['formula'] = 'Formule';
$string['newshipping'] = 'Nouveau frais de port';
$string['noshippings'] = 'Aucun calcul de frais de port défini';
$string['nozones'] = 'Aucune zone définie';
$string['param_a'] = 'Paramètre \'$a\'';
$string['param_b'] = 'Paramètre \'$b\'';
$string['param_c'] = 'Paramètre \'$c\'';
$string['shippingfixedvalue'] = 'Frais fixe';
$string['shippings'] = 'Frais de port';
$string['shippingzone'] = 'Zone de port';
$string['editshippingzone'] = 'Modifier la zone de port';
$string['shipzones'] = 'Zones de livraison';
$string['shipzone'] = 'Zone de livraison';
$string['usedentries'] = 'Frais de ports associés';
$string['zonecode'] = 'Code de zone';
$string['zoneid'] = 'Zone de port ';

$string['shippingfixedvalue_help'] = '
# Port fixe

Lorsque vous pouvez réduire le coût de port à une valuer fixe et simple, Utilisez ce champ pour donner la valeur de port pour le produit 
concerné en devise par défaut de la boutique associée.
';

$string['formula_help'] = '
# Port calculé

Give a parsable formula to calculate the shipping cost. You can use standard path functions (sqrt, arythmetic operators, log)
and use variables placeholders  as $ttc, $ht or one of the following parameters $a, $b or $c.
';
