<?php


$string['productsextradatacolumns'] = 'Extra columns in product instance list';
$string['edit'] = 'Edit';
$string['configproductsextradatacolumns'] = 'For special products who generates extra data in its lifecycle, you can grap
attribute names to be displayed additionnaly in the product instance list (coma or spaces) for better identification';
$string['usesmarturls'] = 'Use smart product urls';
$string['configusesmarturls'] = 'If enabled, and a rewrite rule is added (Apache or nginx), product detailed pages can be 
accessed and thus referenced as smart url of the form <%WWWROOT%>/local/shop/front/product/<pid>

Nginx : 
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
        rewrite summary/([0-9]+)$ /local/shop/pro/front/summary.php?shopid=$1 last;
    }

    location /local/shop/front/ {
        rewrite front/([0-9]*)$ /local/shop/front/view.php?shopid=$1&view=shop last;
    }

    location /local/shop/front/categoryid/ {
        rewrite categoryid/([0-9]+)$ /local/shop/front/view.php?view=shop&category=$1 last;
        rewrite categoryid/([0-9]+)/([0-9]+)$ /local/shop/front/view.php?view=shop&shopid=$1&category=$2 last;
    }

    location /local/shop/front/category/ {
        rewrite category/([0-9a-zA-Z-_]+)$ /local/shop/front/view.php?view=shop&categoryalias=$1 last;
        rewrite category/([0-9]+)/([0-9a-zA-Z-_]+)$ /local/shop/front/view.php?view=shop&shopid=$1&categoryalias=$2 last;
    }

';
