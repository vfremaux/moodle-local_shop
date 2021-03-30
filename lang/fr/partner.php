<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,00
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

$string['allbutpartners'] = 'Hors partenaires';
$string['exportpartnerkey'] = 'Code Partenaire';
$string['exportpartnertag'] = 'Tag Partenaire';
$string['partner'] = 'Partenaire';
$string['partners'] = 'Partenaires ';
$string['editpartner'] = 'Modifier le partenaire';
$string['hasmoodleaccount'] = 'A un comte moodle associé (notifications)';
$string['hascustomeraccount'] = 'A un compte client associé';
$string['partnerkey'] = 'Clef de partenaire';
$string['partnersecretkey'] = 'Clef secrète de partenaire';
$string['managepartners'] = 'Gérer les partenaires';
$string['addpartner'] = 'ajouter un partenaire';
$string['newpartner'] = 'Ajouter un nouveau partenaire';
$string['nopartner'] = 'Aucun partenaire enregistré';
$string['partnername'] = 'Nom';
$string['partnernameexists'] = 'Ce partenaire est déjà enregistré.';
$string['partnerenabled'] = 'Partenariat actif';
$string['countbills'] = 'Factures émises';
$string['customerid'] = 'Compte client associé';
$string['partnerincome'] = 'Chiffre d\'affaire';
$string['moodleuser'] = 'Utilisateur moodle';
$string['moodleuser_help'] = 'Entrez un identifiant d\'utilisateur moodle associé pour que le partenaire puisse recevoir des notifications.';
$string['useraccount'] = 'Identifiant de l\'utilisateur moodle associé';
$string['erroremptypartnername'] = 'Le nom du partenaire ne peut être vide';
$string['referer'] = 'Referer';
$string['viewpartnerbills'] = 'Voir les factures du partenaire';

$string['managepartners_desc'] = 'Des partenaires peuvent importer des sessions d\'achat dans la boutique et les faire enregsitrer pour leur compte.';

$string['moodleuser_help'] = 'Le compte moodle associé au partenaire. Ce compte sera utilisé pour envoyer des notifications au partenaire.';

$string['referer_help'] = 'Lorsqu\'un partenaire envoie une requête de panier avec un pré-remplissage à partir d\'un site tiers, 
la boutique vérifiera l\'origine de l\'appel si ce champ est rempli avec le rerferer (url d\'origine attendue). Cette sécurité n\'est
pas absolue, mais contribue à minimiser les abus. Certains clients sont susceptibles de ne pas fournir ce champ, et la procédure d\'achat
complète devra être menée par l\'acheteur. Si ce champ est fourni et que l\'origine de la transaction peut être vérifiée, il est possible
d\'accélerer le processus d\'achat en sautant les étapes non essentielles.';

$string['partnercustomerid_help'] = 'Dans certains cas où les partenaires doivent être facturés, assignez un compte client au partenaire dans
lequel les factures généres seront émises.';

$string['partnersecretkey_help'] = 'Cette clef est confidentielle et NE DOIT pas être utilisée dans des URL ou des affichages visibles.
Elle est utilisée comme authentification d\'accès dans certaines opérations spéciales faites au nom des partenaires.';
