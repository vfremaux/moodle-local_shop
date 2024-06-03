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

1. Creation d'un gestionnaire d'achat std_prorogate dont le but s'il est actionn� est de prolonger la dur�e de vie d'une unit� de vente sur la base de sa r�f�rence.
2. Modification du gestionnaire d'achat std_registerproduct en ajoutant la gestion d'une info 'extrasupport' permettant de donner une liste arbitraire de shortnames de cours o� l'acheteur doit �tre inscrit (en plus du cours support client) => Voir effet ci apr�s
3. Ajout d'une classification d'�tat de produit 'ASCOMPLEMEN' permettant de classer des produits boutique qui ne peuvent �tre vendus qu'en compl�ment d'une unit� de vente d�j� existante (par exemple une prorogation)
4. Modification du service d'accueil boutique des requ�tes provenant de plugins install�s pour accepter la pr�sence optionnelle d'une licensekey (identifiant une unit� de vente suppos�e existante dans la boutique)
5. Modification dans l'infrastructure pro g�n�rique des plugins de la commande "get_options" pour laisser passer une licensekey s'il en existe une d�j� enregistr�e localement dans le moodle client. (en rapport au point 4)

Effets et sc�narios : 

Deux produits doivent exister dans la boutique : 
- un produit "Register Product" qui repr�sentera la premi�re acquisition de clef de licence au moment de l'installation du plugin et son activation. La clef de licence �tant vide dans l'installation cliente, seul ce produit sera exprim� dans les options d'achats du processus partenaire (l'autre est masqu�).
- un autre produit sera constitu� sur la base d'un "prorogate" avec une dur�e (timeshift) d'un an (= 31�536�000 secondes). (� voir si on met le param�tre en secondes (unix) ou en jours pour des valeurs plus lisibles). Ce produit en mode ASCOMPLEMENT, ne sort pas sur le catalogue front, et sort dans les options du processus partenaire uniquement si une clef de licence existe d�j� dans moodle.

Les deux produits doivent avoir un idnumber coh�rent avec le nom du plugin : local_courseindex_01 et local_courseindex_01P par exemple.
09:30
Sc�nario 1 : Premiere mise en oeuvre : 
- Le distributeur active son interface distributeur
- Seul le produit d'enregistrement lui est pr�sent�.
- La validation de cette option d'achat : 
    - Enregistre le produit et la licence
    - Ajoute le client au customersupport (si besoin)
    - inscrit l'acheteur dans les cours supports suppl�mentaires (d�finis dans le produit)
- La licence est inscrite dans le plugin

Sc�nario 2 : Prorogation
- Le propri�taire du produit (le distributeur) re�oit une alerte de fin de validit�, du type 7 jours, 5 jours 3 jours 1 jour. (impl�mentation � v�rifier)
- le distributeur/propri�taire r�active le process distributeur dans l'interface du plugin
- Seul le produit de prorogation lui est propos� (l'autre est filtr� par la pr�sence d'un licensekey non nul)
- En activant l'option d'achat (et en transmettant sa licensekey) l'unit� de vente enregsitr�e existante, correspondant � cette clef, est prorog� de la dur�e d�finie dans le produit boutique. 
