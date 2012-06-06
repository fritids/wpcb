<?php
require_once('wp-load.php'); // Necessaire pour aller chercher les optiosn
global $wpdb, $purchase_log, $wpsc_cart;
$options = get_option('wpcb_options');
$purch_log_email=get_option('purch_log_email');
if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
if ((array_key_exists('demo', $options)) && ($options['demo'])){ // Ce Kit de demo a du vous etre envoyé par la banque
	$pathfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile";
	$path_bin_response=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/response";
	$logfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/logfile.txt";
}
else{
	$pathfile=$options['pathfile'];
	$path_bin_response=$options['path_bin_response'];
	$logfile=$options['logfile'];
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
	$sessionid=$response['order_id'];

	// A venir : Ajout dans un google spreadsheet qui a toutes les entêtes précédentes (requis Zend)
	// A coler dans la page admin pour tester


		if (WP_ZEND_FRAMEWORK){
			$GoogleConnection=true;
			$SpreadSheetConnection=true;
			try {$client = Zend_Gdata_ClientLogin::getHttpClient($options['googleemail'],$options['googlepassword']);}
			catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
			if ($GoogleConnection){			
				// Test 
				$service=Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
				$client=Zend_Gdata_ClientLogin::getHttpClient($options['googleemail'],$options['googlepassword'], $service);
				// On va chercher le numéro de la feuille :
				$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery(); // todo pour pas de client ici ?
				$query_worksheet->setSpreadsheetKey($options['spreadsheetKey']);
				$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
				try {$feed = $spreadsheetService->getWorksheetFeed($query_worksheet);}
				catch (Zend_Gdata_App_HttpException $ae){echo $ae->exception();$SpreadSheetConnection=false;}
				if ($SpreadSheetConnection){
					// Tout bon on ajoute : 
					foreach($feed->entries as $entry){
						$worksheetId_PremiereFeuille=basename($entry->id);
						break; // on arrete la boucle, donc on écrit dans la première feuille !!
					}
					$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
					// Insert row in google spreadsheet :
					wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Mauvais Numero de Spreadsheet dans les options du plugin wpcb');
					$insertedListEntry = $spreadsheetService->insertRow($response,$options['spreadsheetKey'],$worksheetId_PremiereFeuille);
				}
				else{
					wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Mauvais Numero de Spreadsheet dans les options du plugin wpcb');
				}
			}
			else {
				if (WP_DEBUG){
				wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Mauvais login/mot de pass google dans les options du plugin wpcb');
				}
			}
		}
		else{
			wp_mail($purch_log_email,'Email envoyé depuis le auto_response','Installer Zend pour ajouter automatiquement les ventes à google drive !');
		}
	

	//  analyse du code retour
	if ( isset($_POST['sandbox']) ){
			$message="-----------SANDBOX-------------------------\n";
			foreach ($response as $k => $v) {
				$message.= $k." = ".$v."\n";
			}
			$message.="-------------------------------------------\n";
			if ($logfile){	
				$fp=fopen($logfile, "a");
				fwrite($fp,$message);
				fclose ($fp);
			}
			echo $message;
			wp_mail($purch_log_email,'Email pour vous dire qu\'un paiement SANDBOX est arrivé !',$message);
	} // Fin de l'achat sandbox
	else{ //Vrai achat !
	if (($response['code']=="") && ($response['error']=="")){
		$message="erreur appel response\n executable response non trouve $path_bin_response\n Session Id : $sessionid";
		if ($logfile){
			$fp=fopen($logfile,"a");			// Ouverture du fichier de log en append
			fwrite($fp,$message);
			fclose ($fp);
		}
		if (WP_DEBUG){
			wp_mail($purch_log_email,'Email envoyé depuis le auto_response car il y a une erreur avec un paiement Atos',$message);
		}
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
		$wpsc_cart->empty_cart();
	}
	elseif ($response['code']!=0){
		$message=" API call error.\n Error message :  $error\n Session Id : $sessionid";	
		if ($logfile){
			// Ouverture du fichier de log en append
			$fp=fopen($logfile, "a");
			fwrite($fp,$message);
			fclose ($fp); 
		}
		if (WP_DEBUG){
			wp_mail($purch_log_email,'Email envoyé depuis le auto_response car il y a une erreur avec un paiement Atos',$message);
		}
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid);
		$wpsc_cart->empty_cart();
	 }
	else{
		// Ok, Sauvegarde dans la base de donnée du shop.
		if ($response_code==00) {
			$message="-----------SALES----------------------------\n";
			foreach ($response as $k => $v) {
				$message.= $k." = ".$v."\n";
			}
			$message.="-------------------------------------------\n";
			if ($logfile){	
				$fp=fopen($logfile, "a");
				fwrite($fp,$message);
				fclose($fp);
			}
			if (WP_DEBUG){
				wp_mail($purch_log_email,'Email pour vous dire qu\'un paiement est arrivé !',$message);
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
			$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ; // Ne pas enlever car global !
			$wpsc_cart->empty_cart();
			// Peut-être faut-il ici decrease stock ???
			// redirect ->
			transaction_results($sessionid,false);
			// false -> no echo ! // The cart is emptied in this function a condition d'avoir la global $wpsc_cart !
		}
	}
}// Fin du vrai achat
	}// Fin du isset POST DATA
else{
	if (WP_DEBUG){
			wp_mail($purch_log_email,'Qqn a accéder à cette page sans utiliser le module de CB','Rien de grave, c\'est peut-etre un robot google !');
		}
}
?>