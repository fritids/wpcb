<?php
require_once('wp-load.php'); // Necessaire pour aller chercher les optiosn
global $wpdb, $purchase_log, $wpsc_cart;
$wpcb_cb_options = get_option( 'wpcb_atos'); 
$wpcb_general_options = get_option( 'wpcb_general' );
$wpcb_dev_options=get_option( 'wpcb_dev' );
if (!$wpcb_general_options){
	echo 'Vous n\'avez pas renseigné les options atos dans les réglages du plugin wpcb<br/>';
}
if ($_GET['debug']==1){
	echo '$wpcb_general_options -> <br/>';
	print_r($wpcb_general_options);
	echo '<br/>$wpcb_dev_options -> <br/>';
	print_r($wpcb_dev_options);
}
$purch_log_email=get_option('purch_log_email');
if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}

$pathfile=$wpcb_cb_options['pathfile'];
$path_bin_response=$wpcb_cb_options['path_bin_response'];
$logfile=$wpcb_cb_options['logfile'];

if ($wpcb_dev_options['mode_debugatos']){
	$log.='$pathfile'.$pathfile."\n";
	$log.='$path_bin_response'.$path_bin_response."\n";
	wp_mail($purch_log_email,'Automatic Response was called',$log);
}
// Initialisation du chemin du fichier de log :
if (isset($_POST['DATA'])){
	$data=escapeshellcmd($_POST['DATA']);
	$message="message=$data";
	$pathfile="pathfile=".$pathfile;
	if ( isset($_POST['sandbox']) ){
		$result=$_POST['sandbox'];
	}
	else{
		$result=exec("$path_bin_response $pathfile $message");
	}
	$tableau = explode ("!", $result);
	$response=array(
	'code'=>$tableau[1],
	'error'=>$tableau[2],
	'merchantid'=>$tableau[3],
	'merchantcountry'=>$tableau[4],
	'amount'=>$tableau[5],
	'transactionid'=>$tableau[6],
	'paymentmeans'=>$tableau[7],
	'transmissiondate'=>$tableau[8],
	'paymenttime'=>$tableau[9],
	'paymentdate'=>$tableau[10],
	'responsecode'=>$tableau[11],
	'paymentcertificate'=>$tableau[12],
	'authorisationid'=>$tableau[13],
	'currencycode'=>$tableau[14],
	'cardnumber'=>$tableau[15],
	'cvvflag'=>$tableau[16],
	'cvvresponsecode'=>$tableau[17],
	'bankresponsecode'=>$tableau[18],
	'complementarycode'=>$tableau[19],
	'complementaryinfo'=>$tableau[20],
	'returncontext'=>$tableau[21],
	'caddie'=>$tableau[22],
	'receiptcomplement'=>$tableau[23],
	'merchantlanguage'=>$tableau[24],
	'language'=>$tableau[25],
	'customerid'=>$tableau[26],
	'orderid'=>$tableau[27],
	'customeremail'=>$tableau[28],
	'customeripaddress'=>$tableau[29],
	'captureday'=>$tableau[30],
	'capturemode'=>$tableau[31],
	'data'=>$tableau[32],
	);

	//Session id used by wp ecommerce :
	$sessionid=$response['orderid'];

if ($wpcb_dev_options['mode_debugatos']){
	$log.="\n\n"."-----------SALES (You receive this email because mode debug atos is checked----------------------------\n";
	foreach ($response as $k => $v) {
		$log.= $k." = ".$v."\n";
	}
}

	//  analyse du code retour
	if ( isset($_POST['sandbox']) ){
			$log="-----------SANDBOX-------------------------\n";
			foreach ($response as $k => $v) {
				$log.= $k." = ".$v."\n";
			}
			$log.="-------------------------------------------\n";
			if ($logfile){	
				$fp=fopen($logfile, "a");
				fwrite($fp,$log);
				fclose ($fp);
			}
			echo $log;
			wp_mail($purch_log_email,'Email pour vous dire qu\'un paiement SANDBOX est arrivé !',$log);
	} // Fin de l'achat sandbox
	else{ //Vrai achat !
		if (($response['code']=="") && ($response['error']=="")){
			$log.="-----------ERROR----------------------------\n";
			$log.="erreur appel response\n executable response non trouve ".$path_bin_response."\n". "Session Id : ".$sessionid."\n";
			$log.="-------------------------------------------\n";
			if ($logfile){
				$fp=fopen($logfile,"a");			// Ouverture du fichier de log en append
				fwrite($fp,$log);
				fclose ($fp);
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
			$wpsc_cart->empty_cart();
		}
		elseif ($response['code']!=0){
			$log.="-----------ERROR----------------------------\n";
			$log.=" API call error.\n Error log :  $error\n Session Id : $sessionid";	
			$log.="-------------------------------------------\n";
			if ($logfile){
				// Ouverture du fichier de log en append
				$fp=fopen($logfile, "a");
				fwrite($fp,$log);
				fclose ($fp); 
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
			$wpsc_cart->empty_cart();
		 }
		else{
			// Ok, Sauvegarde dans la base de donnée du shop.
			if ($response['code']==00) {
				$log.="-----------SALES----------------------------\n";
				foreach ($response as $k => $v) {
					$log.= $k." = ".$v."\n";
				}
				$log.="-------------------------------------------\n";
				if ($logfile){	
					$fp=fopen($logfile, "a");
					fwrite($fp,$log);
					fclose($fp);
				}
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
				$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ; // Ne pas enlever car global !
				$wpsc_cart->empty_cart();
				// Peut-être faut-il ici decrease stock ???
				// redirect ->
				transaction_results($sessionid,false);
				// false -> no echo ! // The cart is emptied in this function a condition d'avoir la global $wpsc_cart !
			}// Fin du  if response_code==0
		}// fin des test d'errer
	}// Fin du vrai achat
}// Fin du isset POST DATA
else{
	// si pas post data do nothing !
}
//$url = site_url();
//wp_redirect($url);
//Exit;
//wp_mail($purch_log_email,'Automatic Response was called',$log);
?>