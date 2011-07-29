<?php
// This file is beeing copied to root of your website when activating the plugin. So it is not used when in production !
// To have access to wp variables and functions :
require_once('wp-load.php');
global $wpdb, $purchase_log;

$purch_log_email=get_option('purch_log_email');
if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}


if (isset($_GET['p']))
{
	
	$p = explode ("-", $_GET['p']);
	if (get_option('atos_debug')=='on'){
	wp_mail($purch_log_email,'Auto response trouvé avec parametres p','Auto response trouvé avec les parametres : '.$_GET['p'].' -> ?atos_callback='.$p[0].'&sessionid='.$p[1]);}
$page_id = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'",ARRAY_A);
$query='?page_id='.$page_id['ID'].'&atos_callback='.$p[0].'&sessionid='.$p[1];
$url=site_url($query);
wp_redirect($url);
}
else
{
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Auto response trouvé','Auto response trouvé, Début test vente');}
// Récupération de la variable cryptée DATA
$data=$_POST['DATA']; 
$message="message=$data";
// Initialisation du chemin du fichier pathfile (à modifier)
$pathfile="pathfile=".get_option('atos_pathfile');
//Initialisation du chemin de l'executable response (à modifier)
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

// Initialisation du chemin du fichier de log :
$logfile=get_option('atos_logfile');
// Le chemin est juste.

//  analyse du code retour
if (( $code == "" ) && ( $error == "" ) )
	{
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Erreur code vide','Erreur code vide');}
	// Ouverture du fichier de log en append
	$fp=fopen($logfile, "a");
  	fwrite($fp, "erreur appel response\n");
  	print ("executable response non trouve $path_bin\n");
	fclose ($fp);
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Vente echec executable response non trouve','Vente echec executable response non trouve');}
	$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid;
	$wpdb->query($sql);
 	}
	//	Erreur, sauvegarde le message d'erreur
else if ( $code != 0 )
	{
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Erreur code non egal a zero','Error='.$error);}
	// Ouverture du fichier de log en append
	$fp=fopen($logfile, "a");
	fwrite($fp, " API call error.\n");
    fwrite($fp, "Error message :  $error\n");
	fclose ($fp); 
	if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Vente echec','Vente echec');}
	// Update wp e-commerce database with an error
	$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid;
	$wpdb->query($sql);
 	}
else
	{
	// Ok, Sauvegarde dans la base de donnée du shop.
	if ($response_code==00)
		{
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Debug Vente ok','Session id :'.$sessionid);}
		// OK, Sauvegarde des champs de la réponse
		$fp=fopen($logfile, "a");
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
	fwrite( $fp, "-------------------------------------------\n");
	fclose ($fp);
	// Debug : 
	//if ( ($purch_log_email=get_option( 'purch_log_email' ) != null) ){mail($purch_log_email, 'Debug Vente ok','Debug Vente ok');}
	// aller chercher les custom fields pour envoyer une facture si besoin
	//redirect to  transaction page and store in DB as a order with accepted payment
	$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid;
	$wpdb->query($sql);
	$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
	transaction_results($sessionid,false); // false -> no echo !
if (get_option('atos_debug')=='on'){wp_mail($purch_log_email,'Vente ok, Phrase sql','Vente ok, Sql :Session id :'.$sql);}
		}
}
}
?>