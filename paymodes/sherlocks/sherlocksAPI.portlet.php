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

// Regenerate the sherlocks pathfile from template.

/**
 * API Portlet.
 *
 * @package    shoppaymodes_sherlocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (has_capability('moodle/site:config', context_system::instance())) {
    /*
     * avoid normal customers to regenerate pathfile. This should be reserved to administrator when
     * setting up the shop.
     * this lowers write permission service breaking
     */
    $this->generate_pathfile();
}

$returncontext = 'sherlocksback' . '-' .$this->theshop->id.'-'.$portlet->transactionid;

// Mandatory parameters.

$parms[] = "merchant_id=".$config->sherlocks_merchant_id;
$parms[] = "merchant_country=".strtolower(substr($config->sellerbillingcountry, 0, 2));
$parms[] = "amount=".floor($portlet->amount * 100);
$parms[] = "currency_code=".$config->sherlocks_currency_code;

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
$parms[] = 'return_context='.$returncontext;

/*
 * Les valeurs suivantes ne sont utilisables qu'en pré-production
 * Elles nécessitent l'installation de vos fichiers sur le serveur de paiement
 */
if (!empty($config->sherlocks_logo_filename)) {
    $parms[] = 'logo_id2='.$config->sherlocks_logo_filename;
}

/*
 * Insertion de la commande en base de données (optionnel)
 * A développer en fonction de votre système d'information
 * Initialisation du chemin de l'executable request (à modifier)
 * ex :
 * -> Windows : $path_bin = "c:/repertoire/bin/request";
 * -> Unix    : $path_bin = "/home/repertoire/bin/request";
 */
$pathbin = $this->get_request_bin($os);

if (!is_file($pathbin) || !is_executable($pathbin)) {
    if (!is_file($pathbin)) {
        $code = '404';
    }
    if (!is_executable($pathbin)) {
        $code = '400';
    }
    $apicallerrorstr = get_string('errorcallingAPI', 'shoppaymodes_sherlocks', $pathbin);
    echo ("<br/><center>$apicallerrorstr Error code : $code</center><br/>");
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
$cmd = "{$pathbin} {$parmstring}";
shop_debug_trace($cmd);
$result = exec("{$pathbin} $parmstring");
shop_debug_trace("Result : $result");


/*
 * sortie de la fonction : $result=!code!error!buffer!
 * - code=0    : la fonction génère une page html contenue dans la variable buffer
 * - code=-1     : La fonction retourne un message d'erreur dans la variable error
 */

// On separe les differents champs et on les met dans une variable tableau.

$sherlocksanswer = explode ("!", "$result");
// Récupération des paramètres.

// Weird behaviour of the executables in mode test do not match the doc.
if ($config->test) {
    $code = $sherlocksanswer[2];
    $error = $sherlocksanswer[0];
    $message = $sherlocksanswer[1];
} else {
    $code = $sherlocksanswer[1];
    $error = $sherlocksanswer[2];
    $message = $sherlocksanswer[3];
}

// Analyse du code retour.

if (($code == '') && ($error == '') ) {
      $apicallerrorstr = get_string('errorcallingAPI2', 'shoppaymodes_sherlocks', $pathbin);
      echo ("<br/><center>581 $apicallerrorstr</center><br/>");
      return;
} else if ($code != 0) {
    // Erreur, affiche le message d'erreur.
    $sherlocksapierrorstr = get_string('sherlocksapierror', 'shoppaymodes_sherlocks');
    echo "<center><b>582 $sherlocksapierrorstr</b></center>";
    echo '<br/><br/>';
    echo get_string('sherlockserror', 'shoppaymodes_sherlocks', $error);
} else {
    echo '<br/><br/>';
    // OK, affichage du mode DEBUG si activé.
    echo $error.'<br/>';
    echo $message.'<br/>';
}
