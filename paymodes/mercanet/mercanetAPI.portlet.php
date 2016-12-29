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

// Regenerate the mercanet pathfile from template.

if (has_capability('moodle/site:config', context_system::instance())) {
    /*
     * avoid normal customers to regenerate pathfile. This should be reserved to administrator when
     * setting up the shop.
     * this lowers write permission service breaking
     */
    $this->generate_pathfile();
}

$logo_id2 = "winduck155X65.png";
$return_context = 'mercanetback' . '-' .$this->theshop->id.'-'.$portlet->transactionid;

// Mandatory parameters.

$parms[] = "merchant_id=".$config->mercanet_merchant_id;
$parms[] = "merchant_country=".strtolower(substr($config->sellerbillingcountry, 0, 2));
$parms[] = "amount=".floor($portlet->amount * 100);
$parms[] = "currency_code=".$config->mercanet_currency_code;

/*
 * Path file file initialisation (change as required)
 *  ex :
 * -> Windows : $parm="$parm pathfile=c:/repertoire/pathfile";
 * -> Unix    : $parm="$parm pathfile=/home/repertoire/pathfile";
 */

$os = (preg_match('/Linux/i', $CFG->os)) ? 'linux' : 'win';
$parms[] = 'pathfile='.$this->get_pathfile($os);

/*
 * Si aucun transaction_id n'est affecté, request en génère
 * un automatiquement à partir de heure/minutes/secondes
 * Référez vous au Guide du Programmeur pour
 * les réserves émises sur cette fonctionnalité

 * Affectation dynamique des autres paramètres
 * Les valeurs proposées ne sont que des exemples
 * Les champs et leur utilisation sont expliqués dans le Dictionnaire des données
 */
$parms[] = 'cancel_return_url='.$portlet->cancelurl;
$parms[] = 'automatic_response_url='.$portlet->ipnurl;
$parms[] = "language=".strtolower(substr(current_language(), 0, 2));

if ($USER->id) {
    $parms[] = 'customer_id='.$USER->id;
} else {
    $parms[] = 'customer_id=';
}

$parms[] = 'customer_email='.$portlet->customer->email;
$parms[] = 'return_context='.$return_context;

/*
 * Les valeurs suivantes ne sont utilisables qu'en pré-production
 * Elles nécessitent l'installation de vos fichiers sur le serveur de paiement
 */
$parms[] = 'logo_id2='.$logo_id2;

/*
 * Insertion de la commande en base de données (optionnel)
 * A développer en fonction de votre système d'information
 * Initialisation du chemin de l'executable request (à modifier)
 * ex :
 * -> Windows : $path_bin = "c:/repertoire/bin/request";
 * -> Unix    : $path_bin = "/home/repertoire/bin/request";
 */
$path_bin = $this->get_request_bin($os);

if (!is_file($path_bin) || !is_executable($path_bin)) {
      $APIcallerrorstr = get_string('errorcallingAPI', 'shoppaymodes_mercanet', $path_bin);
      echo ("<br/><center>$APIcallerrorstr</center><br/>");
      return;
}

/*
 * Appel du binaire request
 * La fonction escapeshellcmd() est incompatible avec certaines options avancées
 * comme le paiement en plusieurs fois qui nécessite  des caractères spéciaux
 * dans le paramètre data de la requête de paiement.
 * Dans ce cas particulier, il est préférable d.exécuter la fonction escapeshellcmd()
 * sur chacun des paramètres que l.on veut passer à l.exécutable sauf sur le paramètre data.
 */
$parmstring = escapeshellcmd(implode(' ', $parms));
$result = exec("{$path_bin} $parmstring");

/*
 * sortie de la fonction : $result=!code!error!buffer!
 * - code=0    : la fonction génère une page html contenue dans la variable buffer
 * - code=-1     : La fonction retourne un message d'erreur dans la variable error
 */

// On separe les differents champs et on les met dans une variable tableau.

$mercanetanswer = explode ("!", "$result");

// Récupération des paramètres.

$code = $mercanetanswer[1];
$error = $mercanetanswer[2];
$message = $mercanetanswer[3];

// Analyse du code retour.

if (($code == '') && ($error == '') ) {
      $APIcallerrorstr = get_string('errorcallingAPI2', 'shoppaymodes_mercanet', $path_bin);
      echo ("<br/><center>$APIcallerrorstr</center><br/>");
      return;
} else if ($code != 0) {
    // Erreur, affiche le message d'erreur.
    $mercanetapierrorstr = get_string('mercanetapierror', 'shoppaymodes_mercanet');
    echo "<center><b>$mercanetapierrorstr</b></center>";
    echo '<br/><br/>';
    echo get_string('mercaneterror', 'shoppaymodes_mercanet', $error);
} else {
    echo '<br/><br/>';
    // OK, affichage du mode DEBUG si activé.
    echo $error.'<br/>';
    echo $message.'<br/>';
}