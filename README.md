# moodle-local_shop

Full features moodle integrated shop.

Versions : 

Community version :
   - Full featured front office sequencer
   - Complete backoffice for usual operations
   - Basic payment methods (paypal, offline wire transfer or check)
   - Basic purchase automation handlers
   - One single shop
   - One single catalogue
   - Ten products limit

Pro version :
   - Full community features
   - Extended banking interfaces (ogone, mercanet, systempay)
   - Extended purchase automation handlers
   - Multiple shop instances
   - Multiple catalogs
   - Master/slave catalogs
   - No product count limit

Pro in preview :
   - Complementary backoffice reports
   - Full WS support

Patented distributors :

   - ActiveProLearn : http://www.activeprolearn.com (sales@activeprolearn.com)

Public documentations : 

   - French : https://docs.activeprolearn/doku.php?id=:local:shop
   - English : https://docs.activeprolearn/en/doku.php?id=:local:shop

2018031100
##################################################

Adds support for remote product licensing servicing.

2020072900 --- XX.0013
##################################################

Transfers discounts to pro zone and redraw the discount policies application.

2021100700 --- XX.0017
##################################################

Adds pro caching zone

2022090900 --- XX.0019
##################################################

Add registration to report_zabbix senders.

2023032400 --- XX.0020
##################################################

Add smarturls feature (pro).

2023041800 --- XX.0021
##################################################

Add product instance expiration notifications to sales admins.

2024053100 --- XX.022
##################################################

1. Creation d'un gestionnaire d'achat std_prorogate dont le but s'il est actionné est de prolonger la durée de vie d'une unité de vente sur la base de sa référence.
2. Modification du gestionnaire d'achat std_registerproduct en ajoutant la gestion d'une info 'extrasupport' permettant de donner une liste arbitraire de shortnames de cours où l'acheteur doit être inscrit (en plus du cours support client) => Voir effet ci après
3. Ajout d'une classification d'état de produit 'ASCOMPLEMEN' permettant de classer des produits boutique qui ne peuvent être vendus qu'en complément d'une unité de vente déjà existante (par exemple une prorogation)
4. Modification du service d'accueil boutique des requêtes provenant de plugins installés pour accepter la présence optionnelle d'une licensekey (identifiant une unité de vente supposée existante dans la boutique)
5. Modification dans l'infrastructure pro générique des plugins de la commande "get_options" pour laisser passer une licensekey s'il en existe une déjà enregistrée localement dans le moodle client. (en rapport au point 4)

Effets et scénarios : 

Deux produits doivent exister dans la boutique : 
- un produit "Register Product" qui représentera la première acquisition de clef de licence au moment de l'installation du plugin et son activation. La clef de licence étant vide dans l'installation cliente, seul ce produit sera exprimé dans les options d'achats du processus partenaire (l'autre est masqué).
- un autre produit sera constitué sur la base d'un "prorogate" avec une durée (timeshift) d'un an (= 31 536 000 secondes). (à voir si on met le paramètre en secondes (unix) ou en jours pour des valeurs plus lisibles). Ce produit en mode ASCOMPLEMENT, ne sort pas sur le catalogue front, et sort dans les options du processus partenaire uniquement si une clef de licence existe déjà dans moodle.

Les deux produits doivent avoir un idnumber cohérent avec le nom du plugin : local_courseindex_01 et local_courseindex_01P par exemple.
09:30
Scénario 1 : Premiere mise en oeuvre : 
- Le distributeur active son interface distributeur
- Seul le produit d'enregistrement lui est présenté.
- La validation de cette option d'achat : 
    - Enregistre le produit et la licence
    - Ajoute le client au customersupport (si besoin)
    - inscrit l'acheteur dans les cours supports supplémentaires (définis dans le produit)
- La licence est inscrite dans le plugin

Scénario 2 : Prorogation
- Le propriétaire du produit (le distributeur) reçoit une alerte de fin de validité, du type 7 jours, 5 jours 3 jours 1 jour. (implémentation à vérifier)
- le distributeur/propriétaire réactive le process distributeur dans l'interface du plugin
- Seul le produit de prorogation lui est proposé (l'autre est filtré par la présence d'un licensekey non nul)
- En activant l'option d'achat (et en transmettant sa licensekey) l'unité de vente enregsitrée existante, correspondant à cette clef, est prorogé de la durée définie dans le produit boutique. 
