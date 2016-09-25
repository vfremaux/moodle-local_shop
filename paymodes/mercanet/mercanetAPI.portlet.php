<?php

// regenerate the mercanet pathfile from template
if (has_capability('moodle/site:config', context_system::instance())) {
    // avoid normal customers to regenerate pathfile. This should be reserved to administrator when
    // setting up the shop.
    // this lowers write permission service breaking
    $this->generate_pathfile();
}

$logo_id2 = "winduck155X65.png";
$return_context = 'mercanetback' . '-' .$this->theshop->id.'-'.$portlet->transactionid;

// Mandatory parameters.

$parms[] = "merchant_id=".$config->mercanet_merchant_id;
$parms[] = "merchant_country=".strtolower(substr($config->sellerbillingcountry, 0, 2));
$parms[] = "amount=".floor($portlet->amount * 100);
$parms[] = "currency_code=".$config->mercanet_currency_code;

// Path file file initialisation (change as required)
    // ex :
    // -> Windows : $parm="$parm pathfile=c:/repertoire/pathfile";
    // -> Unix    : $parm="$parm pathfile=/home/repertoire/pathfile";

$os = (preg_match('/Linux/i', $CFG->os)) ? 'linux' : 'win' ;
$parms[] = 'pathfile='.$this->get_pathfile($os);

    // Si aucun transaction_id n'est affecté, request en génère
    // un automatiquement à partir de heure/minutes/secondes
    // Référez vous au Guide du Programmeur pour
    // les réserves émises sur cette fonctionnalité
    // $parms[] = 'transaction_id='.$portlet->onlinetransactionid;

    // Affectation dynamique des autres paramètres
    // Les valeurs proposées ne sont que des exemples
    // Les champs et leur utilisation sont expliqués dans le Dictionnaire des données
    // $parms[] = 'normal_return_url='.$portlet->returnurl;
$parms[] = 'cancel_return_url='.$portlet->cancelurl;
$parms[] = 'automatic_response_url='.$portlet->ipnurl;
$parm = "language=".strtolower(substr(current_language(), 0, 2));
    // $parm="$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
    // $parm="$parm header_flag=no";
    // $parm="$parm capture_day=";
    // $parm="$parm capture_mode=";
    // $parm="$parm bgcolor=";
    // $parm="$parm block_align=";
    // $parm="$parm block_order=";
    // $parm="$parm textcolor=";
    // $parm="$parm receipt_complement=";
    // $parm="$parm caddie=mon_caddie";

if ($USER->id) {
    $parms[] = 'customer_id='.$USER->id;
} else {
    $parms[] = 'customer_id=';
}

$parms[] = 'customer_email='.$portlet->customer->email;
    // $parm="$parm customer_ip_address=";
    // $parms[] = 'data=';
$parms[] = 'return_context='.$return_context;
    // $parm="$parm target=";
    // $parm="$parm order_id=";


    // Les valeurs suivantes ne sont utilisables qu'en pré-production
    // Elles nécessitent l'installation de vos fichiers sur le serveur de paiement
    // // $parm="$parm normal_return_logo=";
    // $parm="$parm cancel_return_logo=";
    // $parm="$parm submit_logo=";
    // $parm="$parm logo_id=";
$parms[] = 'logo_id2='.$logo_id2;
    // $parm="$parm advert=";
    // $parm="$parm background_id=";
    // $parm="$parm templatefile=";


    // insertion de la commande en base de données (optionnel)
    // A développer en fonction de votre système d'information

    // Initialisation du chemin de l'executable request (à modifier)
    // ex :
    // -> Windows : $path_bin = "c:/repertoire/bin/request";
    // -> Unix    : $path_bin = "/home/repertoire/bin/request";
    $path_bin = $this->get_request_bin($os);

    if (!is_file($path_bin) || !is_executable($path_bin)) {
          $APIcallerrorstr = get_string('errorcallingAPI', 'shoppaymodes_mercanet', $path_bin);
          echo ("<br/><center>$APIcallerrorstr</center><br/>");
          return;
    }

    // Appel du binaire request
    // La fonction escapeshellcmd() est incompatible avec certaines options avancées
      // comme le paiement en plusieurs fois qui nécessite  des caractères spéciaux 
      // dans le paramètre data de la requête de paiement.
      // Dans ce cas particulier, il est préférable d.exécuter la fonction escapeshellcmd()
      // sur chacun des paramètres que l.on veut passer à l.exécutable sauf sur le paramètre data.
    $parmstring = escapeshellcmd(implode(' ', $parms));
    $result = exec("{$path_bin} $parmstring");

    // sortie de la fonction : $result=!code!error!buffer!
    // - code=0    : la fonction génère une page html contenue dans la variable buffer
    // - code=-1     : La fonction retourne un message d'erreur dans la variable error

    //On separe les differents champs et on les met dans une variable tableau

    $mercanetanswer = explode ("!", "$result");
    
    // récupération des paramètres

    $code = $mercanetanswer[1];
    $error = $mercanetanswer[2];
    $message = $mercanetanswer[3];

    // analyse du code retour

      if (($code == '') && ($error == '') ) {
          $APIcallerrorstr = get_string('errorcallingAPI2', 'shoppaymodes_mercanet', $path_bin);
          echo ("<br/><center>$APIcallerrorstr</center><br/>");
          return;
    }

    // Erreur, affiche le message d'erreur

    elseif ($code != 0) {
        $mercanetapierrorstr = get_string('mercanetapierror', 'shoppaymodes_mercanet');
        echo "<center><b>$mercanetapierrorstr</b></center>";
        echo '<br/><br/>';
        echo get_string('mercaneterror', 'shoppaymodes_mercanet', $error);
    }

    // OK, affiche le formulaire HTML
    else {
        echo '<br/><br/>';
        # OK, affichage du mode DEBUG si activé
        echo $error.'<br/>';
        echo $message.'<br/>';
    }