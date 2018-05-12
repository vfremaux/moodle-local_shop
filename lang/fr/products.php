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

$string['searchforakeyinstructions'] = '
Cette clef vous est donnée par un client. vous pouvez tenter une recherche en tapant les quelques premiers chiffres de la clef
';
$string['addbundle'] = 'Ajouter un lot';
$string['addcategory'] = 'Ajouter une catégorie de produits';
$string['addoverride'] = 'Surcharger l\'élément';
$string['addproduct'] = 'Ajouter un produit';
$string['addset'] = 'Ajouter un assemblage';
$string['allcategories'] = 'Toutes les catégories';
$string['assets'] = 'Ressources associées';
$string['automatoin'] = 'Automatisation';
$string['behaviour'] = 'Comportement';
$string['billorderingnumber'] = 'Le numéro d\'ordre de la facture';
$string['categories'] = 'Catégories';
$string['customersnameonbill'] = 'Le nom du client mentionné sur la facture';
$string['deletebundle'] = 'Supprimer un lot';
$string['deletecategory'] = 'Supprimer une catégorie de produits';
$string['deleteoverride'] = 'Supprimer la surcharge';
$string['deleteproduct'] = 'Supprimer un produit';
$string['deleteset'] = 'Supprimer un assemblage';
$string['editbundle'] = 'Mettre à jour un lot';
$string['editcategory'] = 'Modifier une catégorie de produits';
$string['editproduct'] = 'Modifier un produit';
$string['editproductvariant'] = 'Modifier un produit (variante locale)';
$string['editset'] = 'Mettre à jour un assemblage';
$string['enablehandler'] = 'Activer le traitement d\'achat&nbsp;';
$string['eula'] = 'Texte des conditions de vente et d\'utilisation&nbsp;';
$string['financials'] = 'Paramètres financiers';
$string['handlerparams'] = 'Paramètres du gestionnaire&nbsp;';
$string['newbundle'] = 'Nouveau lot';
$string['newcategory'] = 'Nouvelle catégorie';
$string['newitem'] = 'Nouvel élément';
$string['newproduct'] = 'Nouveau produit';
$string['newset'] = 'Nouvel assemblage';
$string['noproductinbundle'] = 'Pas de produit dans le lot';
$string['noproductincategory'] = 'Pas de produits dans cette catégorie pour ce mode d\'accès';
$string['noproductinset'] = 'Pas de produit dans l\'assemblage';
$string['noproducts'] = 'Aucun produits';
$string['nocatsslave'] = 'Vous ne pouvez pas modifier les catégories (catalogue lié).';
$string['producteulas'] = 'Termes de licence spécifique';
$string['productiondata'] = 'Métadonnées de production';
$string['productcount'] = 'Produits dans la catégorie';
$string['productpassword'] = 'Mot de passe&nbsp;';
$string['parentcategory'] = 'Parent';
$string['quantaddressesusers'] = 'La quantité adresse des sièges&nbsp;';
$string['renewable'] = 'Renouvelable&nbsp;';
$string['rootcategory'] = '-- Racine --';
$string['removeproductfrombundle'] = 'Enlever le produit du lot';
$string['removeproductfromcatalogue'] = 'Supprimer le produit du catalogue';
$string['removeproductfromset'] = 'Enlever le produit de l\'assemblage';
$string['removeset'] = 'Supprimer l\'assemblage';
$string['requiredataadvice'] = 'Votre commande nécessite des informations que vous DEVEZ FOURNIR AVANT de pouvoir soumettre.';
$string['requireddata'] = 'Données à fournir par le client&nbsp;';
$string['requiredformatsuccess'] = 'Le format JSON des données requises est correct.';
$string['requireddatacaption'] = 'Certains des produits commandés nécessitent certaines données pour leur configuration. Entrez et validez les informations ci-dessous avant de continuer votre achat.';
$string['toset'] = 'Convertir en assemblage';
$string['tobundle'] = 'Convertir en lot';
$string['toproduct'] = 'Convertir en produit simple';
$string['unlinkproduct'] = 'Délier le produit';
$string['user_enrolment'] = 'Inscription individuelle';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Le cours support client utilise la définition par défaut';
$string['warningnocustomersupportcourse'] = 'aucun cours support client défini';
$string['errornocustomersupportcourse'] = 'Le cours support client défini {$a} n\'existe pas';

$string['handlerparams_help'] = '
## Boutique Moodle en ligne

### Paramètres du gestionnaire d\'action

Certains gestionnaires d\'action génériques prennent des paramètres. Ce champ permet de passer des valeurs spécifiques selon l\'instance de produit. Par exemple, un gestionnaire d\'action générique qui donne l\'accès étudiant Ã un cours a besoin de connaitre l\'id ou le nom du cours qui est concerné. Ce champ permettra de le spécifier produit par produit.

La forme générale des paramètres est une liste de paramètres nommés "à la façon" d\'une URL :
    \'param1=value1&param2=value2...\'

Certains paramètres sont systématiques à tous les gestionnaires standard :

    \'customersupport=<%courseid%>\'

Détermine le cours qui sera utilisé pour accueillir le client dans un espace support. S\'il n\'est pas défini, aucun espace support ne sera mise en place.

#### Gestionnaire générique : Inscription dans un cours

**Paramètres du gestionnaire**

    \'coursename=<%shortname du cours%>\'

Le nom court du cours étant unique dans Moodle, il ne peut y avoir ambiguité

    \'role=<%nom court du role (ex:student)%>\'

Le nom court du rôle à attribuer

    \'duration=<%duree en jours%>\'

La durée est effective à partir de la date d\'exécution du gestionnaire.

#### Gestionnaire générique : Création d\'une cours (version pro)

**Paramètres du gestionnaire**

    \'template=<%shortname du gabarit%>\'

Le nom court du cours étant unique dans Moodle, il ne peut y avoir ambiguité

    \'categoryid=<%id de catégorie%>\'

L\'ID numérique de la catégorie dans laquelle le cours doit être créé. Le propriétaire du produit doit être Créateur de cours
(ou avoir les capacités adéquates) dans cette catégorie pour que le produit puisse se réaliser.

    \'duration=<%duree en jours%>\'

La durée est effective à partir de l\'exécution du gestionnaire.

#### Gestionnaire générique : Création d\'une catégorie (version pro)

**Paramètres du gestionnaire**

    \'parentcategory=<%categoryid%>\'

La catégorie parente où sera créée la sous-categorie au nom du client. Le propriétaire du produit doit avoir
un role de Gestionnaire ou les capacités adéquates pour permettre au produit d\'être réalisé.

#### Gestionnaire générique : Assignation d\'un rôle sur un contexte

**Paramètres du gestionnaire**

    \'contextlevel=<%contextlevelID%>\'   (10 = système, 40 = catégorie, 50 = cours, 70 = module,80 = bloc)

Le niveau de contexte

    \'instance=<%instanceID%>\'

L\'ID d\'instance de l\'objet selon le niveau de contexte (par exemple l\'id du cours pour le niveau 50). Le propriétaire du produit
doit avoir des capacités adéquates sur le contexte désigné.

    \'role=<%roleshortname%>\'

Le nom abrégé du rôle à assigner.
';

$string['renewable_help'] = '
Lorsqu\'un produit est marqué comme renouvelable, cela suppose qu\'une durée de vie du produit a pu être définie, comptée à partir de la date d\'achat (en général).
Ceci doit être géré par le gestionnaire d\'achat lié au produit, et paramétré en interne par des paramètres du gesitonnaire. Tous les gestionnaires standard ne
supportent pas nécessairement la notion de durée. Lorsque cette option est utilisée, les interfaces clientes activeront les notifications du cycle de vie du produit,
et permettront de renouveller l\'achat du même produit pour en étendre la durée. Le client devra alors fournir son code produit disponible dans son interface de support client.
';

$string['producteulas_help'] = '
Les termes de licence spécifique de chaque produit du panier seront aggrégés en une présentation globale de conditions de vente juste
avant la phase de confirmation de prise de commande.
';

$string['requireddata_help'] = '
Certains gestionnaires nécessitent de récolter des données du client.

La définition de ce formulaire utilise une syntaxe JSON pour définir les informations
attendues et les élements de formulaire à utiliser dans la boutique.

La description adopte la structure suivante :

    array(
        array(\'field\' => \'the_field_name\',
              \'label\' => \'some visible name\',
              \'type\' => \'textfield\',
              \'desc\' => \'some desc\',
              \'attrs\' => array(\'size\' => 80)),
         array(\'field\' => \'description_sample\',
               \'label\' => \'Description (sample)\',
               \'type\' => \'textarea\',
               \'desc\' => \'Short Description (sample)\'),
         array(\'field\' => \'template_sample\',
               \'label\' => \'Model (sample)\',
               \'type\' => \'select\',
               \'desc\' => \'Course template (sample)\',
               \'options\' => array(\'MOD1\' => \'Model1\', \'MOD2\' => \'Model2\')));

L\'expression résultante est :

[{"field":"the_field_name","label":"some visible name","type":"textfield","desc":"some desc","attrs":{"size":80}},
 {"field":"description_sample","label":"Description (sample)","type":"textarea","desc":"Short Description (sample)"},
 {"field":"template_sample","label":"Model (sample)","type":"select","desc":"Course template (sample)",
           "options":{"MOD1":"Model1","MOD2":"Model2"}}]

Vous pouvez utiliser un service en ligne comme http://www.objgen.com/json pour
vous faciliter le formatage de la structure.

';

$string['quantaddressesusers_help'] = 'Les produits utilisant cette option affectent l\'étape de saisie des utilisateurs.';

$string['productiondata_help'] = 'Des données supplémentaires attachées au produit et dont certains traitements spécifiques pourraient avoir besoin.
Ces données ne sont ni liées au gestionnaire, et ne sont pas des options du client, mais des données fournies à des mécanismes de boutique ou des
customisations additionnelles. A titre d\'exemple, des données d\'identité de composant pour une vérification automatique des numéros de license.
Entrez les données sous forme d\'une chaine JSON représentant un tableau associatif de données (simples ou complexes selon le mécanisme à alimenter).';
