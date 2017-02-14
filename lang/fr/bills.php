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

$string['addbill'] = 'Ajouter une facture';
$string['addbillitem'] = 'Ajouter un élément de facture';
$string['actualstate'] = 'Etat actuel';
$string['allowingtax'] = 'l\'activation de la taxe';
$string['assignedto'] = 'Assignée à ';
$string['backto'] = 'Revenir à l\'état';
$string['bill'] = 'Facture&nbsp;:&ensp;';
$string['billtitle'] = 'Titre de la facture&nbsp;:&ensp;';
$string['bill_ALLs'] = 'Toutes';
$string['bill_CANCELLEDs'] = 'Factures annulées';
$string['bill_PLACEDs'] = 'Factures placées';
$string['bill_PARTIALs'] = 'En paiement non soldées';
$string['bill_PAYBACKs'] = 'Avoirs clients';
$string['bill_PENDINGs'] = 'Commandes a réaliser';
$string['bill_RECOVERINGs'] = 'Factures en récupération';
$string['bill_SOLDOUTs'] = 'Factures Payées';
$string['bill_WORKINGs'] = 'Factures en travail';
$string['bill_COMPLETEs'] = 'Factures réalisées';
$string['bill_PREPRODs'] = 'Factures en réalisation anticipée';
$string['bill_FAILEDs'] = 'Factures en échec paiement';
$string['bill_REFUSEDs'] = 'Factures en refus paiement';
$string['bill_assignation'] = 'l\'assignation du traitement d\'une facture';
$string['billing'] = 'Facturation';
$string['billstates'] = 'Etats de facture';
$string['chooseuser'] = 'Choisir un utilisateur';
$string['choosecustomer'] = 'Choisir un compte client';
$string['customer_account'] = 'l\'assignation d\'une facture à un compte client';
$string['editbillitem'] = 'Modifier un élément de facture';
$string['exportasxls'] = 'Exporter en XLS';
$string['generateacode'] = 'Générer un code';
$string['goto'] = 'Aller à l\'état';
$string['lettering'] = 'Lettrage';
$string['letteringupdated'] = 'Lettrage enregistré';
$string['nobillattachements'] = 'Aucun document attaché à cette facture.';
$string['nobills'] = 'Aucune facture dans cette catégorie.';
$string['nocodegenerated'] = 'Le code de transaction n\'a jamais été <br>généré (factures manuelles):';
$string['noletteringaspending'] = 'Ceci n\'est pas une facture. <br/>Seule une facture peut être lettrée.';
$string['or'] = 'ou ';
$string['paimentcode'] = 'Code réglement&nbsp;:&ensp;';
$string['providetransactioncode'] = 'Fournir ce code dans toute communication à propos de votre achat';
$string['seethecustomerdetail'] = 'Voir le compte client';
$string['biquantity'] = 'Quantité&nbsp;:&ensp;';
$string['timetodo'] = 'Délai de réalisation&nbsp;:&ensp;';
$string['totalTTC'] = 'Total TTC&nbsp;:&ensp;';
$string['totaltex'] = 'Total hors taxes&nbsp;:&ensp;';
$string['totalti'] = 'Total produits&nbsp;:&ensp;';
$string['billtaxes'] = 'Total taxes&nbsp;:&ensp;';
$string['transaction'] = 'Transaction';
$string['transactioncode'] = 'Code de transaction';
$string['searchtimerange'] = 'La période probable de la transaction';
$string['uniqueletteringfailure'] = '<a href="{$a}">Une autre facture</a> porte déjà ce lettrage.';
$string['unittex'] = 'Unitaire hors taxes&nbsp;:&ensp;';
$string['updatelettering'] = 'Mettre à jour';
$string['pickuser'] = 'Choisissez un compte client ou un compte utilisateur&nbsp;:&ensp;';

$string['help_userid_help'] = '
# L\'association d\'un code client Ã une facture

Une facture doit être émise pour un client

Le client doit posséder un compte client, identifier par un numéro

Remplissez le champ avec l\'ID client avec un numéro de client existant

Si vous souhaitez consulter, créer, gérer un compte client, rendez-vous sur la page de gestion des comptes clients dans l\'administration de la boutique';

$string['shopform_help'] = '
# Informations de compte

Commander sur ce site nécessite un compte

Pour le bon déroulement de la commande, il est important de remplir "toutes" les informations demandées afin de créer un compte client
L\'utilisateur doit donc renseigné son Nom, son Prénom, sa Ville, son Pays et son adresse de courriel

L\'adresse de courriel saisie doit être dans un format valide (exemple : client@fournisseur.fr)';

$string['taxhelp_help'] = '
# L\'association d\'un code taxe à un code produit

Un produit doit être obligatoirement associé à un code taxe

Sélectionnez un code taxe existant parmis la liste déroulante

Si vous souhaitez créer une taxe, veuillez vous rendre dans la section de gestion des taxes de l\'administration';

$string['description_help'] = '<!-- Version: $Id: description.html,v 1.2 2011-05-22 16:05:00 vf Exp $ -->

# Aide à la saisie de textes

La saisie de textes fonctionne dans Moodle à peu près de la façon dont vous l\'attendez. Vous avez en plus la possibilité d\'ajouter à votre texte des "smilies" ou "binettes", des adresses URL et quelques balises HTML.

## Binettes (emoticons)

<div class="indent">
  <table border="0" cellpadding="10">
    <tr>
      <th>
        Nom
      </th>

      <th>
        Dessin
      </th>

      <th>
        Ce qu\'il faut taper
      </th>
    </tr>

    <tr>
      <td>
        sourire
      </td>

      <td>
        <img alt="" src="pix/s/smiley.gif" class="icon" />
      </td>

      <td>
        :-)
      </td>
    </tr>

    <tr>
      <td>
        triste
      </td>

      <td>
        <img alt="" src="pix/s/sad.gif" class="icon" />
      </td>

      <td>
        :-(
      </td>
    </tr>

    <tr>
      <td>
        très content
      </td>

      <td>
        <img alt="" src="pix/s/biggrin.gif" class="icon" />
      </td>

      <td>
        :-D
      </td>
    </tr>

    <tr>
      <td>
        clin d\'oeil
      </td>

      <td>
        <img alt="" src="pix/s/wink.gif" class="icon" />
      </td>

      <td>
        ;-)
      </td>
    </tr>

    <tr>
      <td>
        confus
      </td>

      <td>
        <img alt="" src="pix/s/mixed.gif" class="icon" />
      </td>

      <td>
        :-/
      </td>
    </tr>

    <tr>
      <td>
        choqué
      </td>

      <td>
        <img alt="" src="pix/s/wideeyes.gif" class="icon" />
      </td>

      <td>
        8-)
      </td>
    </tr>

    <tr>
      <td>
        langue
      </td>

      <td>
        <img alt="" src="pix/s/tongueout.gif" class="icon" />
      </td>

      <td>
        :-P
      </td>
    </tr>

    <tr>
      <td>
        surprise
      </td>

      <td>
        <img alt="" src="pix/s/surprise.gif" class="icon" />
      </td>

      <td>
        8-o
      </td>
    </tr>

    <tr>
      <td>
        cool
      </td>

      <td>
        <img alt="" src="pix/s/cool.gif" class="icon" />
      </td>

      <td>
        B-)
      </td>
    </tr>
  </table>
</div>

## URLs

<div class="indent">
  <p>
    Tout texte commençant par <strong>www.</strong> ou par <strong>http://</strong> est automatiquement converti en un lien cliquable.
  </p>

  <p>
    Par exemple : <a href="http://www.google.com/">www.google.com</a> or <a href="http://moodle.org/">http://moodle.org</a>
  </p>
</div>

## Balises HTML

<div class="indent">
  <p>
    Vous pouvez utiliser quelques balises HTML pour rendre vos textes plus clairs et structurés.
  </p>

  <table border="0" cellpadding="5" cellspacing="5">
    <tr>
      <th scope="col">
        Balises HTML
      </th>

      <th scope="col">
        Effet produit
      </th>
    </tr>

    <tr>
      <td>
        <b> gras </b>
      </td>

      <td>
        <strong>texte en caractères gras</strong>
      </td>
    </tr>

    <tr>
      <td>
        <i> italique </i>
      </td>

      <td>
        <em>texte en caractères italiques</em>
      </td>
    </tr>

    <tr>
      <td>
        <u> souligné </u>
      </td>

      <td>
        <u>texte souligné</u>
      </td>
    </tr>

    <tr>
      <td>
        <font color="green"> exemple </font>
      </td>

      <td>
        <font color="green">texte en couleur verte</font>
      </td>
    </tr>
  </table>
</div>';

$string['lettering_help'] = '## Boutique en ligne

### Lettrage de facture

Le lettrage de facture permet d\'enregistrer la correspondance entre les factures en ligne de la boutique
et un logiciel de facturation externe.

Selon les législations, des obligations légales contraignent la numérotation des factures. La boutique en
ligne Moodle enregistre des factures en ligne poru la seule activité de vente sur Moodle. Il est indispensable
de pouvoir effectuer la correspondance entre les transactions en ligne et les factures "officiellement" établies
dans un logiciel de comptabilité.

Le lettrage ne peut accepter plusieurs références identiques, et ce quelque soit le catalogue utilisé par la boutique.';

$string['customer_account_help'] = '
# Aide sur l\'assignation d\'une facture à un compte client

Une facture doit être assignée à un compte client.

Choisissez un compte client déja créer, ou un compte utilisateur de la plateforme.

Si un compte utilisateur est sélectionné, un compte client est automatiquement créé et la facture sera automatiquement
assignée à ce nouveau compte client.';

$string['formula_creation_help'] = '
# Edition de formule du prix TTC

L\'utilisateur doit saisir la formule permettant de calculer le prix TTC d\'un article en fonction de la taxe.
Cette formule doit intégrer les variables du prix TTC ($ttc), du prix HT ($ht), et du ratio de la taxe ($tr)

Exemple : $ttc = $ht + ($ht*$tr/100)';

$string['allowtax_help'] = '
# Aide sur l\'activation de taxe

Vous pouvez désactiver la prise en compte de taxe pour une facture.

Si vous ne prenez pas en compte les taxes, il n\'y aura pas de taxe prise en compte pour le montant total de la facture.';

$string['bill_assignation_help'] = '
# Aide sur l\'attribution du traitement d\'une facture

Un traitement de facture doit être assigné à un utilisateur qui possède les droits appropriés.

Sélectionnez l\'utilisateur auquel vous voulez assigné le traitement de la facture.';

$string['billstates_help'] = '
## Boutique en ligne Moodle

### Etats des factures/commandes

Les factures/commandes ont un cycle de vie déterminé par un moteur d\'états et de transitions.

Les état possibles sont les suivants :

En travail (WORKING)
:   Les factures en travail sont des factures entrées manuellement par les opérateurs de la boutique, et non encore validées.

Placées (PLACED)
:   Les factures placées sont des bons de commande non encore confirmées par les utilisateurs.

Commandes à réaliser (PENDING)
:   Les factures à réaliser ont été confirmées, mais ont un mode de paiement et de prodution non automatique qui doit
être supervisé par les opérateurs de la boutique.

Factures payées (SOLDOUT)
:   Les factures ont été intégralement payées, soit par un procédé automatique en ligne soit par marquage manuel par les
opérateurs de la boutique (après contrôle de réception de paiement).

Factures réalisées (COMPLETE)
:   Les factures payées ont été produites, automatiquement, par activation des gestionnaires de production des produits
ou marqués manuellement comme telles si la production n\'est pas numérique.

Factures annulées (CANCELLED)
:   Les factures annulée par l\'opérateur (mise à l\'écart des factures gelées).

Factures en échec paiement (FAILED)
:   Les factures annulée suite à un échec du paiement en ligne du client.

Avoirs clients (PAYBACK)
:   Des factures soldées peuvent se transformer en avoir clients si un rembourement doit être fait.</dl>';