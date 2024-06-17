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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/local/shop/lib.php');

// Capabilities.

$string['shop:salesadmin'] = 'Est administrateur de ventes';
$string['shop:beassigned'] = 'Est assigné'; // Check this is really used.
$string['shop:accessallowners'] = 'Peut voir toutes les données des sous-vendeurs';
$string['shop:discountagreed'] = 'Bénéficie d\'une remise de base sur les produits';
$string['shop:export'] = 'Peut exporter les définitions de la boutique';
$string['shop:seconddiscountagreed'] = 'Bénéficie d\'une meilleure remise sur les produits';
$string['shop:thirddiscountagreed'] = 'Bénéficie de la meilleure remise sur les produits';
$string['shop:paycheckoverride'] = 'Déclenche la réalisation des produits même avec un paiement non interactif';
$string['shop:usenoninstantpayments'] = 'Peut utiliser des méthodes non interactives';

// Transaction states.

$string['ABANDONNED'] = 'Epuisé';
$string['AVAILABLE'] = 'Disponible';
$string['AVAILABLEINTERNAL'] = 'Dispo (interne)';
$string['ASCOMPLEMENT'] = 'Dispo (complément de produit existant)';
$string['CANCELLED'] = 'Annulée';
$string['COMPLETE'] = 'Réalisée';
$string['DELAYED'] = 'Confirmée paiement attendu';
$string['FAILED'] = 'Echec paiement';
$string['PARTIAL'] = 'A solder';
$string['PAYBACK'] = 'Avoir';
$string['PENDING'] = 'A traiter';
$string['PLACED'] = 'Non confirmée';
$string['PREPROD'] = 'Réalisée par anticipation';
$string['PREVIEW'] = 'Prévision';
$string['PROVIDING'] = 'Réappro';
$string['RECOVERING'] = 'Contentieux';
$string['REFUSED'] = 'Refusé';
$string['SHIP_'] = 'Port dû&nbsp;';
$string['SOLDOUT'] = 'Payée';
$string['SUSPENDED'] = 'Suspendu';
$string['WORKING'] = 'En établissement (interne)';

$string['a'] = 'Arg A';
$string['abstract'] = 'Intitulé ';
$string['addcustomeraccount'] = 'Ajouter un nouveau compte client';
$string['addshipping'] = 'Ajouter une définition de port';
$string['addshippingzone'] = 'Ajouter une zone de livraison';
$string['admin'] = 'Administration';
$string['after'] = 'après';
$string['allbills'] = 'Toutes les factures';
$string['allcustomers'] = 'Tous les clients';
$string['allowtax'] = 'Activer les taxes';
$string['allowtax_help'] = 'Active la prise en compte de la taxe dans les calculs de facures.';
$string['allproductinstances'] = 'Toutes les unités de ventes';
$string['allproducts'] = 'Tous les produits';
$string['allshops'] = 'Toutes les boutiques';
$string['allshops_desc'] = 'Gérer les instances d\'offres commerciales';
$string['amonth'] = 'un mois';
$string['amount'] = 'Montant';
$string['apparence'] = 'Apparence';
$string['applicability'] = 'Régle d\'applicabilité';
$string['attach'] = 'Attacher un fichier';
$string['attachements'] = 'Attachements';
$string['availability'] = 'Disponibilité';
$string['availableproducts'] = 'Produits disponibles';
$string['b'] = 'Arg B';
$string['backoffice'] = 'Back Office';
$string['backtoadmin'] = 'Retour à l\'administration';
$string['backtocatalog'] = 'Revenir au catalogue';
$string['backtoshop'] = 'Aller à la boutique';
$string['backtoshopadmin'] = 'Revenir à l\'administration de la boutique';
$string['bankaccount'] = 'Code compte commerçant';
$string['bankaccountkey'] = 'Cle RIB commerçant';
$string['bankcode'] = 'Code banque commerçant';
$string['banking'] = 'Nom de la banque ';
$string['bankinginfo'] = 'Information bancaires du commerçant';
$string['bankoffice'] = 'Code agence commerçant';
$string['bic'] = 'Code BIC';
$string['bill'] = 'Facture';
$string['billdate'] = 'Date d\'émission';
$string['billid'] = 'Numéro de facture';
$string['billpaidstamp'] = 'Tampon "payé"';
$string['billpaidstamp_desc'] = 'A png or jpg image';
$string['bills'] = 'Factures';
$string['billscopeamount'] = 'Forfait H.T.';
$string['billsdeleted'] = 'Factures/commandes effacées';
$string['billsearch'] = 'Factures : Recherche';
$string['billseq'] = 'Numero d\'ordre';
$string['billspending'] = 'Factures&nbsp;: En cours';
$string['billtotal'] = 'Total Facture';
$string['blancktransactioncode'] = 'Le code de transaction n\'a jamais été <br>généré (factures manuelles)';
$string['bundle'] = 'Assemblage';
$string['c'] = 'Arg C';
$string['carefullchoice'] = 'Veillez à choisir un défaut parmi les méthodes activées.';
$string['catalog'] = 'Catalogue ';
$string['catalogadmin'] = 'Administration des catalogues';
$string['catalogsdeleted'] = 'Catalogues effacés';
$string['catalogue'] = 'Catalogue Produits ';
$string['catalogues'] = 'Catalogues';
$string['catdescription'] = 'Description';
$string['category'] = 'Catégories';
$string['categorydescription'] = 'Description&nbsp;';
$string['categoryname'] = 'Nom de la catégorie&nbsp;';
$string['categoryowner'] = 'Propriétaire de catégorie';
$string['catname'] = 'Nom';
$string['catnum'] = 'Numéro';
$string['check'] = 'Vérifier';
$string['checkpasswordemission'] = 'Tester l\'émission de mot de passe';
$string['chooseall'] = 'Tout';
$string['clear'] = 'supprimer le fichier';
$string['code'] = 'Code';
$string['configbankaccount'] = 'Le code du compte du commerçant';
$string['configbankaccountkey'] = 'La cle RIB du compte du commerçant';
$string['configbankcode'] = 'Le code banque de la banque du commerçant';
$string['configbanking'] = 'La banque du commerçant';
$string['configbankoffice'] = 'Le code de l\'agence banque du commerçant';
$string['configbic'] = 'Le code d\'identification BIC de la banque du commerçant';
$string['configcatalog'] = 'Catalogue';
$string['configcustomerorganisationrequired'] = 'Organisation demandée (client)';
$string['configdefaultcurrency'] = 'Suffixe de devise mentionnée derrière tous les montants';
$string['configdefaultcustomersupportcourse'] = 'Cours par défaut pour le support client';
$string['configdiscountrate'] = 'Fixe un taux de remise';
$string['configdiscountrate2'] = 'Fixe un taux de remise pour les utilisateurs ayant la capacité local/shop:seconddiscountagreed activée';
$string['configdiscountrate3'] = 'Fixe un taux de remise pour les utilisateurs ayant la capacité local/shop:thirddiscountagreed activée';
$string['configdiscounttheshold'] = 'Le seuil du chiffre d\'affaire pour remises';
$string['configelementimageheight'] = 'Hauteur de vignette de sous-composant de produit (px)';
$string['configelementimagermargin'] = 'Marge droite de vignette de sous-composant de produit (px)';
$string['configelementimagewidth'] = 'Largeur de vignette de sous-composant de produit (px)';
$string['configendusermobilephonerequired'] = 'Téléphone mobile demandé (participant)';
$string['configenduserorganisationrequired'] = 'Organisation demandée (participant)';
$string['configeula'] = 'Conditions de vente';
$string['confightaccesscred'] = 'Si moodle est opéré derrière une authentification HTTP, le schéma d\'authentification (login:password) pour les retours des services tiers.';
$string['configiban'] = 'Le numéro IBAN du compte du commerçant';
$string['configmaxitemsperpage'] = 'Nombre maximum d\'items par page';
$string['configprinttabbedcategories'] = 'Afficher les catégories en onglets';
$string['configproductimageheight'] = 'Hauteur de vignette produit (px)';
$string['configproductimagermargin'] = 'Marge droite de vignette produit (px)';
$string['configproductimagewidth'] = 'Largeur de vignette produit (px)';
$string['configselleraddress'] = 'L\'adresse du commerçant';
$string['configsellerbillingaddress'] = 'L\'adresse  du service comptable du commerçant';
$string['configsellerbillingcity'] = 'La ville du service comptable du commerçant';
$string['configsellerbillingcountry'] = 'Le pays du service comptable du commerçant';
$string['configsellerbillingzip'] = 'Le code postal du service comptable du commerçant';
$string['configsellercity'] = 'La ville du commerçant';
$string['configsellercountry'] = 'Le pays du commerçant';
$string['configsellerlogo'] = 'Le logo imprimé sur bons de commande, factures et devis.';
$string['configsellermail'] = 'L\'adresse mail du commerçant';
$string['configsellermailsupport'] = 'Adresse mail du support client';
$string['configsellername'] = 'Doit être le nom officiel du commerçant apparaissant sur les documents commerciaux.';
$string['configsellerphonesupport'] = 'Ligne téléphonique de support client';
$string['configsellerzip'] = 'Le code postal du commerçant';
$string['configserviceproxykey'] = 'Un proxy de service peut relayer à la boutique des demandes de services de produits que vous distribuez.';
$string['configshopcaption'] = 'Nom de boutique';
$string['configshopdescription'] = 'Description du service commercial';
$string['configtestmode'] = 'Permet d\'effectuer des opérations en mode test';
$string['configtestoverride'] = '¨Permet de lever exceptionnellement le blocage d\'achat du mode test';
$string['configtitle'] = 'Titre du bloc ';
$string['configtvaeurope'] = 'Le taux de tva européen';
$string['configusedelegation'] = 'Si activé, les utilisateurs peuvent détenir des produits et les vendre pour leur compte.';
$string['configuseshipping'] = 'Si activé, les frais de port sont pris en charge';
$string['confirmoperation'] = 'Confirmer l\'opération';
$string['extradataonproductinstances'] = 'Métadonnées produit additionnelles';
$string['configextradataonproductinstances'] = 'Un ou plusieurs champs de métadonnées (extradata) à afficher sous la référence produit pour aider à l\'identification de l\'instance. Ceci est fortement lié à l\'architecture interne technique de la boutique.';
$string['controls'] = 'Commandes';
$string['countrycodelist'] = 'Liste des pays de diffusion';
$string['countrytaxe'] = 'Pays de la taxe';
$string['courseowner'] = 'Propriétaire du cours';
$string['courseowner_desc'] = 'Le propriétaire du cours est responsable du contenu du cours et peut inviter d\'autres enseignants avec ou sans les droits d\'édition';
$string['currentowner'] = 'Sous-vendeur';
$string['customeraccount'] = 'Compte client';
$string['customeraccounts'] = 'Comptes clients';
$string['customername'] = 'Nom de client';
$string['customerorganisationrequired'] = 'Organisation demandée sur l\'interface ';
$string['customerrole_desc'] = 'Les personnes qui ont effectué un achat en boutique';
$string['customerrolename'] = 'Client';
$string['customersdeleted'] = 'Base client effacée';
$string['dedicated'] = 'Spécifique';
$string['defaultbilltitle'] = 'Achat en ligne {$a}';
$string['defaultcurrency'] = 'Devise par défaut';
$string['defaultprivatemessagepostpay'] = 'Achat de {$a->quantity} {$a->abstract}';
$string['defaultpublicmessagepostpay'] = 'Vous avez acheté {$a->quantity} {$a->abstract}';
$string['defaultsalesadminmessagepostpay'] = '{$a->quantity} {$a->abstract} acheté.';
$string['deletealllinkedproducts'] = 'Supprimer tous les produits liés';
$string['deletebillitems'] = 'Supprimer des éléments';
$string['deletebills'] = 'Supprimer factures';
$string['deleteproduct'] = 'Supprimer le produit';
$string['description'] = 'Description&nbsp;:&ensp;';
$string['disable'] = 'Désactiver';
$string['disabled'] = 'Désactivé';
$string['discountrate'] = 'Taux de remise';
$string['discountrate2'] = 'Taux de remise 2';
$string['discountrate3'] = 'Taux de remise 3';
$string['discounts'] = 'Réglages des remises';
$string['discountthreshold'] = 'Seuil de chiffre d\'affaire pour remise';
$string['dispo'] = 'Dispo';
$string['dosearch'] = 'Rechercher';
$string['downloadpdfbill'] = 'Télécharger en PDF';
$string['edit_categories'] = 'Edition des catégories';
$string['editbundle'] = 'Edition du lot';
$string['editcatalog'] = 'Modification de la description du catalogue';
$string['editcategory'] = 'Edition des catégories';
$string['editproduct'] = 'Edition du produit';
$string['editset'] = 'Edition de l\'assemblage';
$string['editshipping'] = 'Edition d\'une règle de port';
$string['editshippingzone'] = 'Edition de zone de port dû';
$string['editshop'] = 'Modifier une boutique';
$string['editshopsettings'] = 'Modifier les paramètres de la boutique';
$string['editshopsettings_desc'] = 'Cette version propose une instance unique de boutique Moodle. Vous pouvez modifier les paramètres ici.';
$string['edittaxe'] = 'Edition d\'une taxe';
$string['elementimageheight'] = 'Largeur sous-vignette';
$string['elementimagermargin'] = 'Marge droite sous-vignette';
$string['elementimagewidth'] = 'Hauteur sous-vignette';
$string['enablepaymodes'] = 'Modes de paiement&nbsp;';
$string['enable'] = 'Activer';
$string['error'] = 'Erreur&nbsp;:&ensp;';
$string['errorbadhandler'] = 'Le fichier de ce gestionnaire ({$a}) n\'existe pas. Ceci est une erreur de programmation qui devrait être rapportée aux développeurs de la boutique Moodle.';
$string['errornotownedbill'] = 'Cette facture ne vous appartient pas.';
$string['errorrequirementfieldtype'] = 'Type de champ inconnu {$a} dans les définitions de données requises';
$string['errorunimplementedhandlermethod'] = 'Cette methode de postproduction ({$a}) n\'est pas écrite dans ce gestionnaire. Ceci est une erreur de programmation qui devrait être rapportée aux développeurs de la boutique Moodle.';
$string['eulaagree'] = 'J\'accepte les conditions de ventes';
$string['eulaheading'] = 'Conditions générales de ventes';
$string['expectedpaiement'] = 'Délai de paiement&nbsp;';
$string['experimental'] = 'Fonctionnalités expérimentales';
$string['exportid'] = 'ID';
$string['exportinstitution'] = 'Institution';
$string['exportdepartment'] = 'Departement';
$string['exportpartner'] = 'Partenaire';
$string['exportdiscountcodes'] = 'Remises';
$string['exporttransactionid'] = 'TxID';
$string['exportonlinetransactionid'] = 'OnlineTxID';
$string['exportidnumber'] = 'Lettrage';
$string['formula'] = 'Formule';
$string['from'] = 'depuis (date)';
$string['fulldatefmt'] = '%d/%m/%Y %H:%M';
$string['generalsettings'] = 'Accède au formulaire de paramètres globaux dans l\'administration Moodle';
$string['generateacode'] = 'Générer un code';
$string['generic'] = 'Générique :';
$string['genericerror'] = 'Erreur interne&nbsp;:&ensp;{$a}';
$string['globalsettings'] = 'Réglages généraux';
$string['gotest'] = 'Lancer le test';
$string['gotobackoffice'] = 'Aller au backoffice';
$string['gotofrontoffice'] = 'Aller au front office';
$string['hashandlers'] = 'Ce produit a un gestionnaire d\'actions';
$string['helpdescription'] = 'la description';
$string['helpnote'] = 'les notes';
$string['helptax'] = 'la taxe';
$string['hour'] = '(heure)';
$string['htaccesscred'] = 'Authentification des retours HTTP (test)';
$string['iban'] = 'Code IBAN';
$string['identifiedby'] = 'identifié par';
$string['image'] = 'Image :';
$string['instancesettings'] = 'Réglages d\'instance';
$string['invalidbillid'] = 'N° de commande ou de facture introuvable';
$string['isdefault'] = 'Par défault';
$string['items'] = 'Items';
$string['knownaccount'] = 'Compte enregistré';
$string['label'] = 'Label ';
$string['leaflet'] = 'Brochure&nbsp;:&ensp;';
$string['leafletlink'] = 'Télécharger la brochure';
$string['leafleturl'] = 'Url de Brochure&nbsp;:&ensp;';
$string['link'] = 'Liaison ';
$string['login'] = 'J\'ai déjà un compte';
$string['managediscounts'] = 'Gérer les remises';
$string['managediscounts_desc'] = 'Gérer des instances de remise suivant plusieurs politiques de remises';
$string['manageshipping'] = 'Gérer les frais de port';
$string['manageshipping_desc'] = 'Gère les zones de port et les frais par produits';
$string['managetaxes'] = 'Gérer les taxes';
$string['manybillsasresult'] = 'Plusieurs factures correspondent aux critères que vous avez utilisés. Vous pouvez choisir parmi ';
$string['manyunitsasresult'] = 'Plusieurs unités de produit correspondent aux critères que vous avez utilisés. Vous pouvez choisir parmi ';
$string['master'] = 'Catalogue maître';
$string['maxdeliveryquant'] = 'Quantité maximale par transaction&nbsp;:&ensp;';
$string['maxitemsperpage'] = 'Taille de liste max';
$string['message'] = 'Message&nbsp;:&ensp;';
$string['miscellaneous'] = 'Autres options';
$string['missingcode'] = 'Un code produit doit être mentionné';
$string['mytotal'] = 'Voir mon total panier';
$string['name'] = 'Nom&nbsp;:&ensp;';
$string['namecopymark'] = ' - Copie';
$string['nametaxe'] = 'Nom de taxe';
$string['newbill'] = 'Nouvelle facture';
$string['newbillitem'] = 'Nouvel élément de facture';
$string['newcatalog'] = 'Nouveau catalogue';
$string['newshop'] = 'Ajouter une boutique';
$string['newshopinstance'] = 'Nouvelle boutique';
$string['nocatalogs'] = 'Aucun catalogue disponible';
$string['nocats'] = 'Pas de catégories';
$string['nocustomers'] = 'Pas de clients enregistrés';
$string['none'] = 'Aucun';
$string['nonmutable'] = 'Poduit non modifiable';
$string['nopaymodesavailable'] = 'Aucun moyen de payement disponible. Cela peut être dû à la configuration de la boutique ou à des conditions particulières de test.';
$string['nosamecurrency'] = 'Toutes les factures n\'ont pas la même unité. La somme n\'est pas consistante.';
$string['noshops'] = 'Aucune boutique définie';
$string['notaxes'] = 'Pas de taxes enregistrées';
$string['notes'] = 'Notes&nbsp;';
$string['notowner'] = 'Cet item ne vous appartient pas';
$string['notrace'] = 'Aucune trace pour cette transaction';
$string['num'] = 'N°&nbsp;';
$string['numtaxe'] = 'Numéro';
$string['objectexception'] = 'Exceptions de données : {$a}';
$string['oneday'] = 'un jour';
$string['onehour'] = 'une heure';
$string['onlyforloggedin'] = 'Seulement pour les utilisateurs connectés :';
$string['or'] = 'ou';
$string['order'] = 'Commande';
$string['orderType_OTHER'] = 'Autre service ou produit';
$string['orderType_PACK'] = 'Offre standard';
$string['orderType_PROD'] = 'Vente de produits';
$string['orderType_SERVICE'] = 'Prestation de service';
$string['orders'] = 'Commandes';
$string['outofcategory'] = 'Hors catégorie (racine)';
$string['outofset'] = 'Hors assemblage';
$string['paiedamount'] = 'Montant payé';
$string['param_a'] = 'Paramètre A&nbsp;';
$string['param_b'] = 'Paramètre B&nbsp;';
$string['param_c'] = 'Paramètre C&nbsp;';
$string['partnerkey'] = 'Clef partenaire';
$string['pastetransactionid'] = 'Coller un ID de transaction ';
$string['paymentmethods'] = 'Modes de paiement';
$string['paymodes'] = 'Mode de paiement&nbsp;';
$string['picktransactionid'] = 'Choisir un ID de transaction ';
$string['pluginname'] = 'Boutique';
$string['postproduction'] = 'Action sur un produit';
$string['potentialhandlererror'] = 'Pour l\'administrateur des ventes : le produit {$a} a un gestionnaire configuré, mais l\'association du cours cible semble manquante ou erronée.';
$string['price'] = 'Prix';
$string['price2'] = 'Prix 2';
$string['price3'] = 'Prix 3';
$string['printbill'] = 'Voir la version imprimable';
$string['printbilllink'] = 'Imprimer cette facture maintenant&nbsp;!';
$string['printorderlink'] = 'Imprimer';
$string['product'] = 'Produit :';
$string['productcode'] = 'Code produit :';
$string['productid'] = 'Identifiant produit';
$string['productimageheight'] = 'Largeur vignette';
$string['productimagermargin'] = 'Marge droite vignette';
$string['productimagewidth'] = 'Hauteur vignette';
$string['productioncomplete'] = 'Votre commande a été traitée.';
$string['productionresults'] = 'Résultats de production';
$string['productlabel'] = 'Label de produit';
$string['productline'] = 'Catalogue Produit ';
$string['productname'] = 'Nom de produit';
$string['productoperation'] = 'Opération sur votre produit';
$string['productpostprocess'] = 'Actions sur les produits (post production)';
$string['products'] = 'Produits&nbsp;:&ensp;';
$string['proforma'] = 'Facture Proforma';
$string['providedbymoodleshop'] = 'Groupe créé par la boutique moodle';
$string['provisionalnumber'] = 'Numérotation provisoire';
$string['quant'] = 'Quant';
$string['quantity'] = 'Quantité&nbsp;';
$string['rate'] = 'Taux';
$string['ratiotaxe'] = 'Ratio';
$string['recalculate'] = 'Recalculer';
$string['removesecurity'] = ' confirmez la suppression catalogues : ';
$string['required'] = 'Champ obligatoire';
$string['requiredformaterror'] = 'Il semble que la description des paramètres clients ne soit pas une chaine JSON correcte.';
$string['requiredparams'] = 'Paramètres client';
$string['reset'] = 'Réinitialiser';
$string['reset_desc'] = 'Réinitialise la boutique';
$string['resetbills'] = 'Effacer les commandes/factures';
$string['resetcatalogs'] = 'Effacer les catalogues';
$string['resetcustomers'] = 'Effacer la base client';
$string['resetitems'] = 'Eléments à effacer';
$string['results'] = 'Résultats';
$string['runningbills'] = 'Factures&nbsp;: En cours';
$string['sales'] = 'Ventes';
$string['salesconditions'] = 'Conditions de vente&nbsp;:&ensp;';
$string['salesmanagement'] = 'Administration des ventes';
$string['salesrole_desc'] = 'Les personnes qui ont ce rôle peuvent contrôler les opérations de la boutique en ligne';
$string['salesrolename'] = 'Commercial';
$string['salesservice'] = 'Service commercial';
$string['saverequs'] = 'Enregistrer votre configuration des produits';
$string['scantrace'] = 'Scanner les traces marchandes';
$string['search'] = 'Recherche';
$string['searchby'] = 'Chercher par';
$string['searchinbills'] = 'Visualiser tous les en-cours. Effectuer une recherche de facture.';
$string['searchincustomers'] = 'Visualiser et effectuer des recherches dans tous les comptes clients.';
$string['searchinproductinstances'] = 'Visualiser et effectuer des recherches dans les ventes';
$string['searchinproducts'] = 'Visualiser et effectuer des recherches dans le(s) catalogue(s) produits.';
$string['searchintaxes'] = 'Visualiser et éditer les taxes.';
$string['section'] = 'Catégorie&nbsp;:&ensp;';
$string['seebigger'] = 'Voir en plus grand';
$string['sel'] = 'Sel';
$string['selectall'] = 'Selectionner tout';
$string['sellerID'] = 'Identifiant légal du commercçant';
$string['selleraddress'] = 'Adresse du commerçant';
$string['sellerbillingaddress'] = 'Adresse comptable du commerçant';
$string['sellerbillingcity'] = 'Ville comptable du commerçant';
$string['sellerbillingcountry'] = 'Pays comptable du commerçant';
$string['sellerbillingzip'] = 'Code postal comptable du commerçant';
$string['sellercity'] = 'Ville du commerçant';
$string['sellercountry'] = 'Pays du commerçant';
$string['sellerlogo'] = 'Logo du commerçant';
$string['sellermail'] = 'Mél du commerçant';
$string['sellermailsupport'] = 'Support par courriel';
$string['sellername'] = 'Nom du commerçant';
$string['sellerphonesupport'] = 'Support téléphonique';
$string['sellerzip'] = 'Code postal du commerçant';
$string['seoalias'] = 'Alias pour le référencement';
$string['seotitle'] = 'Titre pour le référencement';
$string['seokeywords'] = 'Mots-clefs pour le référencement';
$string['seodescription'] = 'Description pour le référencement';
$string['seoalias_help'] = 'Un alias utilisé pour forger les "smarturls". Les smarturls peuvent utiliser les alias de catégories de produit et les alias de produits. On s\'attend à ce qu\'elles soient "lisibles" humainement';
$string['seotitle_help'] = 'Un titre caché dans l\'en-tête HTML. Il s\'agit d\'un élément crucial pour le référencement. Les titres de produit sont insérés pour les pages liées au produit.
Les titres de catégorie sont insérées dans les contextes de navigation sur une catégorie de produits. Par défaut le nom du produit sera utilisé, mais cela peut ne pas être optimal.';
$string['seokeywords_help'] = 'Même si les mots-clefs sont connus comme étant une technique ignorée par la plupart des moteurs de recherche, il reste possible de qualifier l\'élément par des mots clefs.';
$string['seodescription_help'] = 'Une descirption pour les moteurs de recherche. Elle ne dois pas excéder 255 caractères et présenter des mots-clefs significatifs dans son expression.';
$string['serviceproxykey'] = 'Clef du proxy de services';
$string['set'] = 'Assemblage&nbsp;:&ensp;';
$string['setid'] = 'Code assemblage ';
$string['settings'] = 'Réglages généraux';
$string['shipping'] = 'Règle tarifaire';
$string['shippingfixedvalue'] = 'Port forfaitaire ';
$string['shippings'] = 'Règles tarifaires de livraison';
$string['shipzone'] = 'Zone tarifaire de livraison';
$string['shipzones'] = 'Zones de livraison';
$string['shop'] = 'Accès à la boutique';
$string['shopcaption'] = 'Nom de la boutique ';
$string['shopdescription'] = 'Description de la boutique ';
$string['shopinstance'] = 'Instance de boutique';
$string['shopproductcreated'] = 'Créé par une postproduction de la Boutique Moodle';
$string['shops'] = 'Boutiques';
$string['shortname'] = 'Nom court';
$string['showdescriptioninset'] = 'Affichage de la description ';
$string['shownameinset'] = 'Affichage du nom ';
$string['signin'] = 'Se connecter';
$string['slave'] = 'Catalogue lié';
$string['slavegroupcannotbeedited'] = 'Un groupement d\'un produit esclave ne peut être modifié.';
$string['slaveto'] = 'Lié à ';
$string['softdelete'] = 'Désactiver le produit';
$string['softrestore'] = 'Réactiver le produit';
$string['sold'] = 'Ventes&nbsp;:&ensp;';
$string['standalone'] = 'Catalogue indépendant';
$string['status'] = 'Statut&nbsp;';
$string['stock'] = 'Stock';
$string['symb'] = ' (Devise à définir)';
$string['task_cron'] = 'Actions automatiques de boutique';
$string['task_weekly_notification'] = 'Notifications hebdomadaires des unités de vente';
$string['task_daily_notification'] = 'Notifications quotidiennes des unités de vente';
$string['taxcode'] = 'Code Taxe&nbsp;:&ensp;';
$string['taxcountry'] = 'Pays dans lequel la taxe est appliquée :';
$string['taxe'] = 'Taxe';
$string['taxes'] = 'Taxes';
$string['taxformula'] = 'Formule permettant de calculer le prix TTC des produits';
$string['taxname'] = 'Nom de la taxe&nbsp;:&ensp;';
$string['taxratio'] = 'Ratio de la taxe :';
$string['tendays'] = 'dix jours';
$string['tenunitspix'] = 'Icone pour 10 unités de vente&nbsp;:&ensp;';
$string['testmodeactive'] = 'Le service de vente de Moodle est en mode test. Nous n\'autorisons pas les paiements dans ce mode hormis les administrateurs pour une fonction de test.';
$string['testoverride'] = 'Suppression du verrou de test';
$string['testuser'] = 'Tester l\'utilisateur';
$string['threemonths'] = 'trois mois';
$string['thumbnail'] = 'Vignette&nbsp;:&ensp;';
$string['timetodo'] = 'Délai de facturation ';
$string['title'] = 'Intitulé';
$string['total'] = 'Total';
$string['totalprice'] = 'Prix Total';
$string['totaltaxed'] = 'Total TTC';
$string['totaltaxes'] = 'Taxes (total)';
$string['totaluntaxed'] = 'Total H.T.';
$string['tracescan'] = 'Scanner';
$string['tracescan_desc'] = 'Examine les traces et extrait les transactions';
$string['transactionid'] = 'Code Transaction';
$string['ttc'] = 'TTC';
$string['tvaeurope'] = 'Numéro de TVA intracommunautaire';
$string['type'] = 'Type';
$string['unit'] = 'Unit. (HT)';
$string['unitpix'] = 'Icone d\'unité de vente&nbsp;:&ensp;';
$string['unitprice1'] = 'Prix unit. HT (1)&nbsp;:&ensp;';
$string['unitprice1'] = 'Prix unitaire HT';
$string['unitprice2'] = 'Prix unit. HT (2)&nbsp;:&ensp;';
$string['unitprice3'] = 'Prix unit. HT (3)&nbsp;:&ensp;';
$string['unitprice4'] = 'Prix unit. HT (3)&nbsp;:&ensp;';
$string['unitprice5'] = 'Prix unit. HT (3)&nbsp;:&ensp;';
$string['unittests'] = 'Tester les produits';
$string['unity'] = 'Unitaire';
$string['unitycost'] = 'Coût unitaire';
$string['unlockcatalogs'] = 'Verrou de suppression catalogues';
$string['unselectall'] = 'Désélectionner tout';
$string['unset'] = '-- Non défini --';
$string['until'] = 'jusqu\'à';
$string['usedelegation'] = 'Délégation de vente';
$string['usedentries'] = 'Produits attribués';
$string['userdiscountagreed'] = 'Remise 1';
$string['userdiscountagreed2'] = 'Remise 2';
$string['userdiscountagreed3'] = 'Remise 3';
$string['userenewableproducts'] = 'Activer les produits renouvellables';
$string['userenrol'] = 'Inscription';
$string['useshipping'] = 'Frais de port';
$string['useslavecatalogs'] = 'Activer les catalogues maîtres/esclaves';
$string['usinghandler'] = 'Gestionnaire utilisé&nbsp;: {$a}';
$string['value'] = 'Valeur fixe';
$string['vendorinfo'] = 'Identité du vendeur';
$string['warning'] = 'Alerte&nbsp;:&ensp;';
$string['worktype'] = 'Type de prestation';

$string['local_shop_backoffice_read_service'] = 'Backoffice shop definition access';

$string['privacy:metadata:shop_customer:id'] = 'L\'identifiant interne du client';
$string['privacy:metadata:shop_customer:firstname'] = 'Le prénom du client';
$string['privacy:metadata:shop_customer:lastname'] = 'Le nom du client';
$string['privacy:metadata:shop_customer:hasaccount'] = 'Si le client est lié à un compte d\'utilisateur moodle';
$string['privacy:metadata:shop_customer:email'] = 'Le mail du client';
$string['privacy:metadata:shop_customer:address'] = 'L\'adresse du client';
$string['privacy:metadata:shop_customer:zip'] = 'Le code postal du client';
$string['privacy:metadata:shop_customer:city'] = 'La ville du client';
$string['privacy:metadata:shop_customer:country'] = 'Le pays du client';
$string['privacy:metadata:shop_customer:organisation'] = 'L\'organisation du client';
$string['privacy:metadata:shop_customer:invoiceinfo'] = 'Si le client a demandé une adresse de facturation séparée, l\'adresse de facturation';
$string['privacy:metadata:shop_customer:timecreated'] = 'La date de création de l\'enregistrement client';
$string['privacy:metadata:shop_customer'] = 'Information personnelle d\'un compte client ou d\'un pré-acheteur';

$string['privacy:metadata:customer_ownership:userid'] = 'L\'identifiant du référent du compte client';
$string['privacy:metadata:customer_ownership:customerid'] = 'L\'identifiant du comtpe client';
$string['privacy:metadata:customer_ownership'] = 'Information sur la responsabilité du compte client';

$string['privacy:metadata:shop_bill:shopid'] = 'The identifier of the shop instance where the purchase was done';
$string['privacy:metadata:shop_bill:userid'] = 'The moodle user id owning the bill';
$string['privacy:metadata:shop_bill:idnumber'] = 'The external identification number (accounting system compatible)';
$string['privacy:metadata:shop_bill:ordering'] = 'The order number of the bill';
$string['privacy:metadata:shop_bill:customerid'] = 'The identifier of the customer record that has purchased';
$string['privacy:metadata:shop_bill:invoiceinfo'] = 'The invoicing information givin by the customer at the instant of purchase';
$string['privacy:metadata:shop_bill:title'] = 'The title of the invoice';
$string['privacy:metadata:shop_bill:worktype'] = 'The type of production required';
$string['privacy:metadata:shop_bill:status'] = 'The statefull state of the purchase';
$string['privacy:metadata:shop_bill:remotestatus'] = 'The remote payement system status, when available';
$string['privacy:metadata:shop_bill:emissiondate'] = 'The date of purchase';
$string['privacy:metadata:shop_bill:lastactiondate'] = 'The date of the last operation on the bill';
$string['privacy:metadata:shop_bill:assignedto'] = 'The id of an internal moodle user the bill is assigned to review or monitor';
$string['privacy:metadata:shop_bill:timetodo'] = 'An indication of the work time needed to build the products (not used)';
$string['privacy:metadata:shop_bill:untaxedamount'] = 'The total amount without taxes';
$string['privacy:metadata:shop_bill:taxes'] = 'The total of dued taxes';
$string['privacy:metadata:shop_bill:amount'] = 'The total amount tax included';
$string['privacy:metadata:shop_bill:currency'] = 'The current currency of the invoice';
$string['privacy:metadata:shop_bill:convertedamount'] = 'The converted amount (seller currency)';
$string['privacy:metadata:shop_bill:transactionid'] = 'The unique transaction id';
$string['privacy:metadata:shop_bill:onlinetransactionid'] = 'The remote payment system transaction id if available';
$string['privacy:metadata:shop_bill:expectedpaiment'] = 'The amount to be paied (not yet used)';
$string['privacy:metadata:shop_bill:paiedamount'] = 'The amount having been payed (not yet used)';
$string['privacy:metadata:shop_bill:paymode'] = 'The payment method';
$string['privacy:metadata:shop_bill:ignoretax'] = 'If the current purchase is free of tax or not';
$string['privacy:metadata:shop_bill:productiondata'] = 'The data collected to build the products';
$string['privacy:metadata:shop_bill:paymentfee'] = 'An amount of paied back fee the seller need to pay to the payment system';
$string['privacy:metadata:shop_bill:productionfeedback'] = 'The feedback received from production process';
$string['privacy:metadata:shop_bill'] = 'All the information about a purchase';

$string['privacy:metadata:shop_bill_item:billid'] = 'The purchase id the bill item belongs to';
$string['privacy:metadata:shop_bill_item:ordering'] = 'The order of the item in the bill';
$string['privacy:metadata:shop_bill_item:type'] = 'The type of the item';
$string['privacy:metadata:shop_bill_item:itemcode'] = 'The catalog item code';
$string['privacy:metadata:shop_bill_item:catalogitem'] = 'The catalog item id';
$string['privacy:metadata:shop_bill_item:abstract'] = 'The short abstract of the product at the instant of purchase';
$string['privacy:metadata:shop_bill_item:description'] = 'The description of the product at the instant of purchase';
$string['privacy:metadata:shop_bill_item:delay'] = 'The delay to produce or deliver (not used)';
$string['privacy:metadata:shop_bill_item:unitcost'] = 'The unit cost of the item at the instant of purchase';
$string['privacy:metadata:shop_bill_item:quantity'] = 'the ordered quantity';
$string['privacy:metadata:shop_bill_item:totalprice'] = 'The total calculated price';
$string['privacy:metadata:shop_bill_item:taxcode'] = 'The id of the applied tax';
$string['privacy:metadata:shop_bill_item:bundleid'] = 'The bundle the item belongs to';
$string['privacy:metadata:shop_bill_item:customerdata'] = 'The information that was asked to the customer to tune the product';
$string['privacy:metadata:shop_bill_item:productiondata'] = 'The information that was collected by the purchase process on build';
$string['privacy:metadata:shop_bill_item'] = 'Information about individual elements of a purchase';

$string['privacy:metadata:shop_product:customerid'] = 'The identifier of the custommer account';
$string['privacy:metadata:shop_product:catalogitemid'] = 'The reference to the catalog definition of the product';
$string['privacy:metadata:shop_product:initialbillitemid'] = 'the last bill item that updated the product';
$string['privacy:metadata:shop_product:contexttype'] = 'The moodle context level the product is related to';
$string['privacy:metadata:shop_product:instanceid'] = 'The moodle internal instance id the product is related to';
$string['privacy:metadata:shop_product:startdate'] = 'The start date of the product';
$string['privacy:metadata:shop_product:enddate'] = 'The peremption date of the product';
$string['privacy:metadata:shop_product:reference'] = 'A unique product reference identifier';
$string['privacy:metadata:shop_product:productiondata'] = 'Some metadata giving an image of how was built the product';
$string['privacy:metadata:shop_product:extradata'] = 'Some additional information the product has';
$string['privacy:metadata:shop_product:deleted'] = 'If the product has been deleted';
$string['privacy:metadata:shop_product'] = 'Information about product instances owned by the customer';

$string['privacy:metadata:shop_product_event:productid'] = 'The product instance the event is related to';
$string['privacy:metadata:shop_product_event:billitemid'] = 'The bill item the event relates to';
$string['privacy:metadata:shop_product_event:eventtype'] = 'Type of event';
$string['privacy:metadata:shop_product_event:eventdata'] = 'Metadata related to the event';
$string['privacy:metadata:shop_product_event:datecreated'] = 'When the event occured';
$string['privacy:metadata:shop_product_event'] = 'Life cycle event related to a purchased product instance';


$string['noproducts'] = "
<h3>Catalogue vide</h3>
<p>Le catalogue produit ne contient aucune entrée.
";

$string['lettering_help'] = '
<p>Le lettrage permet de faire correspondre les factures en ligne avec la séquence comptable.</p>
';

$string['bill_complete_text_tpl'] = '
Cette commande est enregistrée.<br/>
';

$string['buy_instructions_tpl'] = '
Vous pouvez régler par des procédures différentes. Cochez ici le mode de règlement choisi, puis laissez-vous
guider par la procédure qui vous sera indiquée :
';

$string['customer_welcome_tpl'] = '
<h3>Bienvenue dans l\'espace client</h3>
<p>Vous pouvez dans cet espace accéder à toutes les informations qui vous concernent. Vous pourrez suivre
l\'historique de vos factures, de vos tickets d\'assistance, de l\'état de nos opérations vous concernant.
';

$string['delete_catalog_dialog_tpl'] = '
Voulez-vous vraiment supprimer ce catalogue ?

Catalogues maîtres : Tous les catalogues liés seront
également supprimés.
';

$string['empty_bill_tpl'] = '
<h3>Cette facture n\'a pas d\'éléments</h3>
<p>Vous pouvez ajouter des éléments de facture en utilisant les liens au bas de cet écran.
';

$string['empty_taxes_tpl'] = '
Cette facture n\'a pas d\'éléments taxés
<p>Les taxes peuvent être appliquées élément par élément.
';

$string['no_bill_attachements_tpl'] = '
<h5>Pas d\'éléments attachés</h5>
<p>Vous pouvez attacher des fichiers ou autre documents à la facture. Utilisez le lien ci-dessous. Vous pouvez
également attacher un fichier ou document (un seul) à chacun des éléments de facture. Utilisez l\'icone
<img src="images/icons/attach.gif"> de la ligne de commandes des entrées de facture.
';

$string['no_bills_in_account_tpl'] = '
<h3>Aucune facture</h3>
<p>Vous n\'avez jamais été facturé par notre société.
';

$string['no_categories_tpl'] = '
<p>Aucune catégorie n\'est définie dans le catalogue.
';

$string['no_orders_in_acocunt_tpl'] = '
<h3>Pas de commandes</h3>
<p>Vous pouvez créer une commande par le lien "nouvelle commande" ci-dessous.
';

$string['no_products_in_set_tpl'] = '
<p>Aucun produit enregistré dans l\'assemblage.
';

$string['no_products_tpl'] = '
<h3>Catalogue vide</h3>
<p>Le catalogue produit ne contient aucune entrée. </p>
';

$string['no_product_shippings_tpl'] = '
<h3>Frais de port</h3>
<p>Le catalogue ne définit pas de frais d\'envoi pour ce produit.
';

$string['no_zones_tpl'] = '
<h3>Zones de port</h3>
<p>Le catalogue ne définit aucune zone de livraison.
';

$string['out_euro_zone_advice_tpl'] = '
<b>Attention :</b> en raison des frais bancaires prohibitifs, nous ne pouvons accepter ni les chèques hors
zone euro, ni les règlements par swift. Merci de votre compréhension.
';

$string['post_billing_message_tpl'] = '
vous remercie de votre achat. Votre commande est actuellement en cours de vérification...
<br/>
';

$string['sales_feedback_tpl'] = '
<h3><%%SELLER%%></h3>
<h4>Commande client</h2>

<p>Vous avez effectué une commande sur le serveur <%%SERVER%%>.

<p><u>Identification Client :</u>
<hr/>
<b>Nom :</b> <%%FIRSTNAME%%><br/>
<b>Prénom :</b> <%%LASTNAME%%><br/>
<b>Mel :</b> <%%MAIL%%><br/>
<b>Ville :</b> <%%CITY%%><br/>
<b>Pays :</b> <%%COUNTRY%%><br/>
<hr/>
<p><u>Résumé Commande</u>
<hr/>
<b>Montant total TTC :</b> <%%AMOUNT%%><br/>
<b>Mode de paiement envisagé :</b> <%%PAYMODE%%><br/>
<b>Nombre d\'unités de vente :</b> <%%ITEMS%%>
<hr/>
<%%PRODUCTION_DATA%%>
<hr/>
Vous pouvez accéder à votre facture par <a href="<%%SERVER_URL%%>/login/index.php?ticket=<%%TICKET%%>">ce lien</a>.
';

$string['search_bill_failed_tpl'] = '
<h3 class="error">Erreur dans la recherche</h3>
<p>Aucune facture ne correspond aux critères de recherche que vous avez défini. Veuillez modifier vos
critères et retenter une recherche.
';

$string['search_product_failed_tpl'] = '
<h3 class="error">Erreur dans la recherche</h3>
<p>Aucun produit ne correspond aux critères de recherche que vous avez défini&nbsp;:
<p><code>recherche par&nbsp;: {$a->by} valeur&nbsp;: <?php echo  {$a->value} ?></code>
<p>Veuillez modifier vos critères et retenter une recherche.
';

$string['transaction_confirm_tpl'] = '
<h3><%%SELLER%%></h3>
<h4>Confirmation commande client</h4>

<p>La commande ci-dessous a été facturée sur le site <%%SERVER%%> :

<p><u>Identification Client :</u>
<hr>
<b>Nom&nbsp;:</b> <%%FIRSTNAME%%><br>
<b>Prénom&nbsp;:</b> <%%LASTNAME%%><br>
<b>Mel&nbsp;:</b> <%%MAIL%%><br>
<b>Ville&nbsp;:</b> <%%CITY%%><br>
<b>Pays&nbsp;:</b> <%%COUNTRY%%><br>
<hr>
<p><u>Résumé Commande</u>
<hr>
<b>Montant total H.T.&nbsp;:</b> <%%AMOUNT%%><br>
<b>Taxes&nbsp;:</b> <%%TAXES%%><br>
<b>TTC&nbsp;:</b> <%%TTC%%><br>
<b>Mode de paiement envisagé&nbsp;:</b> <%%PAYMODE%%><br>
<b>Nombre d\'objets&nbsp;:</b> <%%ITEMS%%><br>
<b>Code transaction&nbsp;:</b> <code><%%TRANSACTION%%></code>
<hr/>
<%%PRODUCTION_DATA%%>
<hr/>
<p>Examen de la commande (personnes autorisées)
<hr>
<a href="<%%SERVER_URL%%>/login/index.php?ticket=<%%TICKET%%>">Visualiser la commande dans la gestion commerciale</a>
';

$string['upload_failure_tpl'] = '
<h5>Erreur de téléchargement</h5>
<p>Votre téléchargement a échoué pour la raison suivante : <span class="error"><%%ERROR%%></span>
';

$string['upload_success_tpl'] = '
<h5>Chargement effectué</h5>
<p>Votre fichier est maintenant attaché à sa cible. Utilisez le lien ci-dessous si votre écran de travail n\'est
pas restauré au delà des 5 prochaines secondes.
';

$string['upload_text'] = '
<h5>Zone de téléchargement</h5>
<p>Choisissez un fichier sur votre poste de travail par le bouton "Parcourir". Validez l\'envoi. Vous pouvez être
confrontés à des limites de taille. La limite actuelle est de {$a} Mo.
';

$string['discountrate_help'] = '
Fixe un taux de remise
';
$string['discountrate2_help'] = '
Fixe un taux de remise pour les utilisateurs ayant la capacité local/shop:seconddiscountagreed activée
';

$string['discountrate3_help'] = '
Fixe un taux de remise pour les utilisateurs ayant la capacité local/shop:thirddiscountagreed activée
';

$string['discountthreshold_help'] = '
Le seuil du chiffre d\'affaire pour activer la première remise.
';

$string['eula_help'] = 'Vous devez prendre connaissance et accepter les conditions de vente suivantes avant
de procéder à un achat sur {$a}. La validation de ce formulaire vaut pour acceptation de ces conditions.';

$string['resetguide'] = 'Cette commande permet d\'effacer tout ou partie des données de la boutique.
Certaines valeurs sont liées : par exemple<br/><ul><li>Si vous effacez la base client, vous effacez
nécessairement les factures</li><li>Si vous effacez les catalogues, vous pouvez garder la base client,
mais toutes les commandes/factures seront effacées.</li></ul>';

$string['shops_help'] = 'Des boutiques sont des instances de vente appuyées sur un catalogue et opérant
selon certaines modalités.';

$string['categoryowner_desc'] = 'Le propriétaire d\'une catégorie peut gérer le contenu de sa catégorie et
inviter des enseignants et d\'autres créateurs de cours. Il en reste respondable.';

global $CFG;
require($CFG->dirroot.'/local/shop/lang/fr/front.php');
require($CFG->dirroot.'/local/shop/lang/fr/catalogs.php');
require($CFG->dirroot.'/local/shop/lang/fr/discounts.php');
require($CFG->dirroot.'/local/shop/lang/fr/shops.php');
require($CFG->dirroot.'/local/shop/lang/fr/bills.php');
require($CFG->dirroot.'/local/shop/lang/fr/partner.php');
require($CFG->dirroot.'/local/shop/lang/fr/products.php');
require($CFG->dirroot.'/local/shop/lang/fr/purchasemanager.php');
require($CFG->dirroot.'/local/shop/lang/fr/customers.php');
require($CFG->dirroot.'/local/shop/lang/fr/tax.php');
require($CFG->dirroot.'/local/shop/lang/fr/shipzones.php');
require($CFG->dirroot.'/local/shop/lang/fr/pdf.php');

if ('pro' == local_shop_supports_feature()) {
    include($CFG->dirroot.'/local/shop/pro/lang/fr/pro.php');
}

// Currencies.

$string['currency'] = 'Monnaie ';
$string['EUR'] = 'Euro';
$string['CHF'] = 'Franc Suisse';
$string['USD'] = 'Dollar US';
$string['CAD'] = 'Dollar Canadien';
$string['AUD'] = 'Dollar Australien';
$string['GPB'] = 'Livre Sterling';
$string['TRY'] = 'Livre turque';
$string['PLN'] = 'Zloty (Pologne)';
$string['RON'] = 'Leu (Roumanie)';
$string['ILS'] = 'Shekel (Israel)';
$string['KRW'] = 'Won (Corée)';
$string['JPY'] = 'Yen (Japon)';
$string['TND'] = 'Dinar (Tunisien, marché intérieur)';
$string['MAD'] = 'Dinar (Maroc, marché intérieur)';

$string['EURsymb'] = '&euro;';
$string['CHFsymb'] = 'F. (CH)';
$string['USDsymb'] = '$ (US)';
$string['CADsymb'] = '$ (CA)';
$string['AUDsymb'] = '$ (AU)';
$string['GPBsymb'] = '£';
$string['TRYsymb'] = '£ (TK)';
$string['PLNsymb'] = 'Zl';
$string['RONsymb'] = 'Leu';
$string['ILSsymb'] = 'Sh';
$string['KRWsymb'] = 'Won (corea)';
$string['JPYsymb'] = 'Yen (japan)';
$string['TNDsymb'] = 'Dn (TU)';
$string['MADsymb'] = 'Dn (MA)';

$string['userenewableproducts_desc'] = 'Les produits renouvellables ont un cycle de vie plus complexes et peuvent notifier les
propriétaires quand une échéance de durée de vie arrive à son terme. Une transaction spéciale permet d\'augmenter la durée de vie
du produit.';

$string['useslavecatalogs'] = 'Les catalogues maîtres esclaves permettent de créer des variantes locales de catalogues pour
altérer la langue de présentation des produits ou les gammes de prix proposés.';

include(__DIR__.'/pro_additional_strings.php');