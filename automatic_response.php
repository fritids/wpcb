<?php
// This file is beeing copied to root of your website when activating the plugin. So it is not used when in production !
// To have access to wp variables and functions :
require_once('wp-load.php');
global $wpdb, $purchase_log;

$purch_log_email=get_option('purch_log_email');
if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
// Initialisation du chemin du fichier de log :
$logfile=get_option('atos_logfile');

$data=$_POST['DATA']; 
$message="message=$data";
$pathfile="pathfile=".get_option('atos_pathfile');
$path_bin =get_option('atos_path_bin_response');
$result=exec("$path_bin $pathfile $message");
//	Sortie de la fonction : !code!error!v1!v2!v3!...!v29
//		- code=0	: la fonction retourne les données de la transaction dans les variables v1, v2, ...
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

//Session id used by wp ecommerce :
$sessionid=$order_id;

//  analyse du code retour
if (($code=="") && ($error==""))
	{
		$message="erreur appel response\n executable response non trouve $path_bin\n Session Id : $sessionid";
		if ($logfile){
			$fp=fopen($logfile,"a");			// Ouverture du fichier de log en append
			fwrite($fp,$message);
			fclose ($fp);
 			}
		if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Debug Email',$message);}
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
	}
else if ($code!=0)
	{
		$message=" API call error.\n Error message :  $error\n Session Id : $sessionid";	
		if ($logfile){
			// Ouverture du fichier de log en append
			$fp=fopen($logfile, "a");
			fwrite($fp,$message);
			fclose ($fp); 
		}
		if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Debug Email',$message);}
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
 	}
else
	{
	// Ok, Sauvegarde dans la base de donnée du shop.
	if ($response_code==00) {
		$message="merchant_id : $merchant_id\n";
		$message.="merchant_country : $merchant_country\n";
		$message.="amount : $amount\n";
		$message.="transaction_id : $transaction_id\n";
		$message.="transmission_date: $transmission_date\n";
		$message.="payment_means: $payment_means\n";
		$message.="payment_time : $payment_time\n";
		$message.="payment_date : $payment_date\n";
		$message.="response_code : $response_code\n";
		$message.="payment_certificate : $payment_certificate\n";
		$message.="authorisation_id : $authorisation_id\n";
		$message.="currency_code : $currency_code\n";
		$message.="card_number : $card_number\n";
		$message.="cvv_flag: $cvv_flag\n";
		$message.="cvv_response_code: $cvv_response_code\n";
		$message.="bank_response_code: $bank_response_code\n";
		$message.="complementary_code: $complementary_code\n";
		$message.="complementary_info: $complementary_info\n";
		$message.="return_context: $return_context\n";
		$message.="caddie : $caddie\n";
		$message.="receipt_complement: $receipt_complement\n";
		$message.="merchant_language: $merchant_language\n";
		$message.="language: $language\n";
		$message.="customer_id: $customer_id\n";
		$message.="order_id: $order_id\n";
		$message.="customer_email: $customer_email\n";
		$message.="customer_ip_address: $customer_ip_address\n";
		$message.="capture_day: $capture_day\n";
		$message.="capture_mode: $capture_mode\n";
		$message.="data: $data\n";
		$message.="-------------------------------------------\n";
		if ($logfile){	
			$fp=fopen($logfile, "a");
			fwrite( $fp,$message);
			fclose ($fp);
		}
		if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Debug Email',$message);}
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
		$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
		transaction_results($sessionid,false); // false -> no echo !
	}
}
?>