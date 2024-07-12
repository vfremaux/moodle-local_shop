<?php

$string['productsextradatacolumns'] = 'Colonnes supplémentaires des unités de vente';
$string['configproductsextradatacolumns'] = 'si vous exploitez des produits spéciaux qui génèrent des
métadonnées annexes supplémentaires, vous pouvez puiser des attributs dans ces données pour les afficher dans
le listing des unités de vente. Certaines de ces métadonnées peuvent faciliter l\'identification et la gestion
de vos produits.';
$string['edit'] = 'Editer les métadonnées';
$string['usesmarturls'] = 'Utilisez des URLs simplifiées pour les produits';
$string['configusesmarturls'] = 'Si actif, et qu\'une règle de réécriture est ajoutée au serveur web (Apache ou nginx), les pages
de détail des produits peuvent être rendues accessibles par des URLs simplifiées de la forme :

<%WWWROOT%>/local/shop/front/product/<pid>

Réécriture pour Nginx :

    location /local/shop/pro/front/product/ {
        rewrite product/([0-9a-zA-Z-_]+)$ /local/shop/pro/front/product.php?itemalias=$1 last;
        rewrite product/([0-9a-zA-Z-_]+)/([a-z-_]+)$ /local/shop/pro/front/product.php?itemalias=$1&what=$2 last;
        rewrite product/[0-9a-zA-Z-_\/]+/([0-9a-zA-Z-_]+)$ /local/shop/pro/front/product.php?itemalias=$1 last;
        rewrite product/[0-9a-zA-Z-_\/]+/([0-9a-zA-Z-_]+)/([a-z-_]+)$ /local/shop/pro/front/product.php?itemalias=$1&what=$2 last;
    }

    location /local/shop/pro/front/productid/ {
        rewrite productid/([0-9]+)$ /local/shop/pro/front/product.php?itemid=$1 last;
        rewrite productid/([0-9]+)/([a-z-_]+)$ /local/shop/pro/front/product.php?itemid=$1&what=$2 last;
        rewrite productid/[0-9\/]+/([0-9]+)$ /local/shop/pro/front/product.php?itemid=$1 last;
        rewrite productid/[0-9\/]+/([0-9]+)/([a-z-_]+)$ /local/shop/pro/front/product.php?itemid=$1&what=$2 last;
    }

    location /local/shop/pro/front/summary/ {
        rewrite summary/([0-9]+) /local/shop/pro/front/summary.php?shopid=$1 last;
    }

    location /local/shop/front/ {
        rewrite front/([0-9]*) /local/shop/front/view.php?shopid=$1&view=shop last;
    }

    location /local/shop/front/categoryid/ {
        rewrite categoryid/([0-9]*)$ /local/shop/front/view.php?view=shop&category=$1 last;
        rewrite categoryid/([0-9]+)/([0-9]+)$ /local/shop/front/view.php?view=shop&shopid=$1&category=$2 last;
    }

    location /local/shop/front/category/ {
        rewrite category/([0-9a-zA-Z-_]*)$ /local/shop/front/view.php?view=shop&categoryalias=$1 last;
        rewrite category/([0-9]+)/([0-9a-zA-Z-_]+)$ /local/shop/front/view.php?view=shop&shopid=$1&categoryalias=$2 last;
    }
';
