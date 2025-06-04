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
 * Lang for discounts
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adddiscount'] = 'Ajouter une remise';
$string['applieddiscounts'] = 'Remises';
$string['editdiscount'] = 'Modifier une remise';
$string['applydata'] = 'Paramètres d\'application';
$string['applyon'] = 'Portée';
$string['argument'] = 'Argument';
$string['discountinstitutionmatch'] = 'Par institution';
$string['discountlongtimecustomer'] = 'Anciens clients';
$string['discountsuccessfullordernum'] = 'Nombre d\'achats réalisés';
$string['discountunconditional'] = 'Inconditionnel';
$string['discountorderamount'] = 'Montant de commande atteint (commande courante)';
$string['discountusercapability'] = 'Capacité utilisateur';
$string['discountoffercode'] = 'Code promotionnel';
$string['multiplediscountoffercode'] = 'Code promotionnels multiples';
$string['multipleratios'] = 'Multiple (voir les réglages)';
$string['partnerdiscount'] = 'Remise partenaire';
$string['partnermultiplediscountoffercode'] = 'Codes promotionnels "partenaire"';
$string['discountenabled'] = 'Actif';
$string['discountname'] = 'Intitulé de la remise';
$string['discounttype'] = 'Algorithme';
$string['discountruledata'] = 'Données spécifiques de l\'algorithme';
$string['discountapplieson'] = 'Portée de la remise';
$string['discountapplydata'] = 'Données spécifiques pour la portée';
$string['ruledata_help'] = 'Données spécifiques :<br><ul>
    <li>Code promotionnel : un simple token alphanumérique. ex : CODEPROMO000</li>
    <li>Codes promotionnels multiples : une liste de triplets &lt;code&gt;|&lt;taux&gt;|&lt;tagpartenaire&gt;</li>
    </ul>';
$string['errorbaddiscounttokenformat'] = 'Erreur de format : La donnée devrait être un simple token alphanumérique, sans espaces ni tirets';
$string['errorbaddiscountmulticodeformat'] = 'Erreur de format : La donnée une liste de triplets <code>|<tauxentier>|<codepartenaire>';
$string['errordiscountnameexistsinshop'] = 'Erreur : Cet intitulé est déjà utilisé dans cette boutique.';
$string['erroremptydiscountitemlist'] = 'Erreur : La portée est réduite mais aucune liste de produit n\'a été spécifiée.';
$string['errordiscount:badratioformat'] = 'Erreur : le taux n\'est pas un nombre';
$string['errordiscount:emptycode'] = 'Erreur : Le code promo est vide';
$string['errordiscount:notenougharguments'] = 'Erreur : Pas assez d\'arguments sur la ligne';
$string['nodiscounts'] = 'Pas de remise définie';
$string['newdiscountinstance'] = 'Nouvelle instance de remise';
$string['onitemlist'] = 'Une sélection du catalogue';
$string['itemlist'] = 'Sélection du catalogue';
$string['onbill'] = 'Toute la commande';
$string['ratio'] = 'Taux';
$string['enabled'] = 'Actif';
$string['operator'] = 'Opérateur';
$string['accumulate'] = 'Accumulatif (par défaut)';
$string['takeover'] = 'Remplace (et stoppe)';
$string['stopchainifapplies'] = 'Stoppe si s\applique';
$string['stopchainifnotapplies'] = 'Stoppe si ne s\'applique pas';
$string['entercode'] = 'Entrez le code';
$string['codeverified'] = 'Ce code est valide et active une remise applicable';
$string['codefailed'] = 'Ce code n\'a pas pu être vérifié pour cette remise';

$string['applydata_help'] = '
Une remise peut concerner la totalité du catalogue ou uniquement une sélection de produits
dans celui-ci. Sélectionnez les produits qui seront concernés par la remise.
';

$string['ratio_help'] = 'Pourcentage de remise sur la portée. Selon certains choix d\'algorithmes et de données spécifiques,
ce taux peut ne pas avoir d\'effet direct et être supplanté par des décisions de l\'algorithme choisi.';

$string['type_help'] = '
<h3>algorithmes de remise&nbsp;:</h3>

<ul>
<li><b>Inconditionnel</b> : La remise est appliquée à toutes les commandes.</li>
<li><b>Par institution</b> : La remise est appliquée aux profils acheteurs qui matchent l\'institution (donnée d\'algorithme -- institution).</li>
<li><b>Anciens clients</b> : La remise est appliquée aux clients connectés dont le compte client est plus vieux que (donnée d\'algorithme -- jours).</li>
<li><b>Nombre d\'achats</b> : La remise est appliquée aux clients connectés dont le compte client a réalisé au moins
 (donnée d\'algorithme -- nombre) factures.</li>
<li><b>Montant commande</b> : La remise est appliquée si le montant de la commande atteint un montant de (donnée d\'algorithme -- nombre).</li>
<li><b>Capacité utilisateur</b> : La remise est appliquée si l\'utilisateur identifié a une certaine capacité dans le contexte (système par défaut)
 (donnée d\'algorithme -- structure json telle que {"capability":"<nom_capacité>"[, "contextid":"<contextid>"]}).</li>
<li><b>Code promo</b> : La remise s\'applique si le code promo associé est entré.</li>
<li><b>Codes promo multiples</b> : Un des taux de remise s\'applique si le code qui lui est associé est entré. Plusieurs codes peuvent être configurés.</li>
<li><b>Codes promo "partenaires"</b> : Un des taux de remise s\'applique si le code qui lui est associé est entré. La commande est associée au
partenaire associé au code.</li>
</ul>
';

$string['operator_help'] = '
<h3>Opération&nbsp;:</h3>
<p>L\'opération lorsque la remise est évaluée dans l\'ordre de la liste de remises :</p>
<ul>
<li><b>Accumulatif:</b> accumule la remise avec les remises précédentes dans la pile</li>
<li><b>Remplace et stoppe:</b> la remise remplace le calcul précédent de la pile. Toutes les remises antérieures sont annulées. L\'évaluation de la pile de remise est stoppée.</li>
<li><b>Stoppe si s\'applique:</b> La remise est appliquée en mode cumulatif, puis l\'évaluation de la pile est stoppée si la remise était applicable.</li>
<li><b>Stoppe si ne s\'applique pas:</b> La remise est appliquée en mode cumulatif, puis l\'évaluation de la pile est stoppée si la remise N\'était PAS applicable.</li>
</ul>';
