<!--
-------------------------------------------------------------
 Topic	 : Exemple PHP traitement de l'autoréponse de paiement
 Version : P617

 		Dans cet exemple, les données de la transaction	sont
		décryptées et sauvegardées dans un fichier log.

-------------------------------------------------------------
-->

<?php

	// Récupération de la variable cryptée DATA
        $message="message=$_POST[DATA]";

	// Initialisation du chemin du fichier pathfile (à modifier)
	    //   ex :
	    //    -> Windows : $pathfile="pathfile=c:/repertoire/pathfile"
	    //    -> Unix    : $pathfile="pathfile=/home/repertoire/pathfile"
	    
	$pathfile="pathfile=chemin_du_fichier_pathfile";

	//Initialisation du chemin de l'executable response (à modifier)
	//ex :
	//-> Windows : $path_bin = "c:/repertoire/bin/response"
	//-> Unix    : $path_bin = "/home/repertoire/bin/response"
	//

	$path_bin = "chemin_du_fichier_response";

	// Appel du binaire response
  	$message = escapeshellcmd($message);
  	$result=exec("$path_bin $pathfile $message");

	//	Sortie de la fonction : !code!error!v1!v2!v3!...!v29
	//		- code=0	: la fonction retourne les données de la transaction dans les variables v1, v2, ...
	//				: Ces variables sont décrites dans le GUIDE DU PROGRAMMEUR
	//		- code=-1 	: La fonction retourne un message d'erreur dans la variable error


	//	on separe les differents champs et on les met dans une variable tableau

	$tableau = explode ("!", $result);

	$code = $tableau[1];
	$error = $tableau[2];
	$merchant_id = $tableau[3];
	$merchant_country = $tableau[4];
	$amount = $tableau[5];
	$transaction_id = $tableau[6];
	$payment_means = $tableau[7];
	$transmission_date= $tableau[8];
	$payment_time = $tableau[9];
	$payment_date = $tableau[10];
	$response_code = $tableau[11];
	$payment_certificate = $tableau[12];
	$authorisation_id = $tableau[13];
	$currency_code = $tableau[14];
	$card_number = $tableau[15];
	$cvv_flag = $tableau[16];
	$cvv_response_code = $tableau[17];
	$bank_response_code = $tableau[18];
	$complementary_code = $tableau[19];
	$complementary_info= $tableau[20];
	$return_context = $tableau[21];
	$caddie = $tableau[22];
	$receipt_complement = $tableau[23];
	$merchant_language = $tableau[24];
	$language = $tableau[25];
	$customer_id = $tableau[26];
	$order_id = $tableau[27];
	$customer_email = $tableau[28];
	$customer_ip_address = $tableau[29];
	$capture_day = $tableau[30];
	$capture_mode = $tableau[31];
	$data = $tableau[32];
	$order_validity = $tableau[33];
	$transaction_condition = $tableau[34];
	$statement_reference = $tableau[35];
	$card_validity = $tableau[36];
	$score_value = $tableau[37];
	$score_color = $tableau[38];
	$score_info = $tableau[39];
	$score_threshold = $tableau[40];
	$score_profile = $tableau[41];
	$threed_ls_code = $tableau[43];
	$threed_relegation_code = $tableau[44];


	// Initialisation du chemin du fichier de log (à modifier)
    //   ex :
    //    -> Windows : $logfile="c:\\repertoire\\log\\logfile.txt";
    //    -> Unix    : $logfile="/home/repertoire/log/logfile.txt";
    //

	$logfile="chemin_du_fichier_de_log";

	// Ouverture du fichier de log en append

	$fp=fopen($logfile, "a");

	//  analyse du code retour

  if (( $code == "" ) && ( $error == "" ) )
 	{
  	fwrite($fp, "erreur appel response\n");
  	print ("executable response non trouve $path_bin\n");
 	}

	//	Erreur, sauvegarde le message d'erreur

	else if ( $code != 0 ){
        fwrite($fp, " API call error.\n");
        fwrite($fp, "Error message :  $error\n");
 	}
	else {

	// OK, Sauvegarde des champs de la réponse

	fwrite( $fp, "merchant_id : $merchant_id\n");
	fwrite( $fp, "merchant_country : $merchant_country\n");
	fwrite( $fp, "amount : $amount\n");
	fwrite( $fp, "transaction_id : $transaction_id\n");
	fwrite( $fp, "transmission_date: $transmission_date\n");
	fwrite( $fp, "payment_means: $payment_means\n");
	fwrite( $fp, "payment_time : $payment_time\n");
	fwrite( $fp, "payment_date : $payment_date\n");
	fwrite( $fp, "response_code : $response_code\n");
	fwrite( $fp, "payment_certificate : $payment_certificate\n");
	fwrite( $fp, "authorisation_id : $authorisation_id\n");
	fwrite( $fp, "currency_code : $currency_code\n");
	fwrite( $fp, "card_number : $card_number\n");
	fwrite( $fp, "cvv_flag: $cvv_flag\n");
	fwrite( $fp, "cvv_response_code: $cvv_response_code\n");
	fwrite( $fp, "bank_response_code: $bank_response_code\n");
	fwrite( $fp, "complementary_code: $complementary_code\n");
	fwrite( $fp, "complementary_info: $complementary_info\n");
	fwrite( $fp, "return_context: $return_context\n");
	fwrite( $fp, "caddie : $caddie\n");
	fwrite( $fp, "receipt_complement: $receipt_complement\n");
	fwrite( $fp, "merchant_language: $merchant_language\n");
	fwrite( $fp, "language: $language\n");
	fwrite( $fp, "customer_id: $customer_id\n");
	fwrite( $fp, "order_id: $order_id\n");
	fwrite( $fp, "customer_email: $customer_email\n");
	fwrite( $fp, "customer_ip_address: $customer_ip_address\n");
	fwrite( $fp, "capture_day: $capture_day\n");
	fwrite( $fp, "capture_mode: $capture_mode\n");
	fwrite( $fp, "data: $data\n");
	fwrite( $fp, "order_validity: $order_validity\n");
	fwrite( $fp, "transaction_condition: $transaction_condition\n");
	fwrite( $fp, "statement_reference: $statement_reference\n");
	fwrite( $fp, "card_validity: $card_validity\n");
	fwrite( $fp, "score_value: $score_value\n");
	fwrite( $fp, "score_color: $score_color\n");
	fwrite( $fp, "score_info: $score_info\n");
	fwrite( $fp, "score_threshold: $score_threshold\n");
	fwrite( $fp, "score_profile: $score_profile\n");
	fwrite( $fp, "threed_ls_code: $threed_ls_code\n");
	fwrite( $fp, "threed_relegation_code: $threed_relegation_code\n");
	fwrite( $fp, "-------------------------------------------\n");
	}

	fclose ($fp);


?>
