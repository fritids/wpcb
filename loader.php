<?php
/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://6www.net/blog/plugin-atos-sips-wp-ecommerce.html
Description: Plugin de paiement par CB ATOS SIPS (Mercanet,...) (Plugin requis : WP e-Commerce)
Version: 1.0
Author: thomas@6www.net
Author URI: http://6www.net
*/

$nzshpcrt_gateways[$num] = array(
'name' => 'Atos','api_version' => 0.9,'class_name' => 'wpsc_merchant_atos',
'has_recurring_billing' => true,'display_name' => 'Atos','wp_admin_cannot_cancel' => false,
'requirements' => array(),'form' => 'form_atos','internalname' => 'wpsc_merchant_atos','function' => 'gateway_atos','submit_function' => 'submit_atos',
'image' => get_option('atos_gateway_image')
);

function form_atos() {
	global $wpdb;
	$output ='<tr><td>Merchant Id</td><td>'; //Attention pas de .= sur la première ligne !
	$output.='<input name="atos_merchantid" type="text"';
	if ($atos_merchantid = get_option('atos_merchantid')){$output.='value="'.$atos_merchantid .'"';}
	$output.='/></td></tr>';
	$output .='<tr><td colspan=2><span class="small description">Fournit par Atos (ex : 00500946144xx11)</span><td></tr>';

	$output .='<tr><td>Url de la page de retour en cas de vente réussie</td><td>';
	$output.='<input name="atos_normal_return_url" type="text"';
	if ($atos_normal_return_url = get_option('atos_normal_return_url')){$output.='value="'.$atos_normal_return_url .'"';}
	$output.='/></td></tr>';
	
	// Cancel return url :
	$output .='<tr><td>Url de la page de retour en cas de vente annulée</td><td>';
	$output.='<input name="atos_cancel_return_url" type="text"';
	if ($atos_cancel_return_url = get_option('atos_cancel_return_url')){$output.='value="'.$atos_cancel_return_url .'"';}
	$output.='/></td></tr>';
	
	$atos_gateway_image_example=plugins_url('wp-e-commerce-atos/logo/LogoMercanetBnpParibas.gif');
	$output .='<tr><td colspan=2><span class="small description">Fournit par Atos (ex : '.$atos_gateway_image_example.')</span><td></tr>';
	
	$output .='<tr><td>Url de l\'image pour la méthode Atos</td><td>';
	$output.='<input name="atos_gateway_image" type="text"';
	if ($atos_gateway_image = get_option('atos_gateway_image')){$output.='value="'.$atos_gateway_image .'"';}
	$output.='/></td></tr>';
	$atos_gateway_image_example=plugins_url('wp-e-commerce-atos/logo/LogoMercanetBnpParibas.gif');
	$output .='<tr><td colspan=2><span class="small description">Fournit par Atos (ex : '.$atos_gateway_image_example.')</span><td></tr>';

	$serverroot=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

	$output .='<tr><td>Path File</td><td>';
	$output.='<input name="atos_pathfile" type="text"';
	if ($atos_pathfile = get_option('atos_pathfile')){$output.='value="'.$atos_pathfile .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Chemin vers le dossier de l exec (pathfile ex : '.$serverroot.'/cgi-bin/pathfile)</span></td></tr>';

	$output .='<tr><td>Chemin vers le dossier request</td><td>';
	$output.='<input name="atos_path_bin" type="text"';
	if ($atos_path_bin = get_option('atos_path_bin')){$output.='value="'.$atos_path_bin .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description"> (atos_path_bin ex : '.$serverroot.'/cgi-bin/request)</span></td></tr>';

	$output .='<tr><td>Chemin vers le dossier response</td><td>';
	$output.='<input name="atos_path_bin_response" type="text"';
	if ($atos_path_bin_response = get_option('atos_path_bin_response')){$output.='value="'.$atos_path_bin_response .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">(atos_path_bin_response ex : '.$serverroot.'/cgi-bin/response)</span></td></tr>';

	$output .='<tr><td>Logfile</td><td>';
	$output.='<input name="atos_logfile" type="text"';
	if ($atos_logfile = get_option('atos_logfile')){$output.='value="'.$atos_logfile .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Chemin vers le fichier log (atos_logfile ex : '.$serverroot.'/cgi-bin/logfilewp.txt)</span></td></tr>';

	$output .='<tr><td>Advert</td><td>';
	$output.='<input name="atos_advert" type="text"';
	if ($atos_advert = get_option('atos_advert')){$output.='value="'.$atos_advert .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Image en haut (ex : advert.jpg)</span></td></tr>';

	$output .='<tr><td>Logo_id2</td><td>';
	$output.='<input name="atos_logo_id2" type="text"';
	if ($atos_logo_id2 = get_option('atos_logo_id2')){$output.='value="'.$atos_logo_id2 .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Image droite ( ex : logo_id2.jpg)</span></td></tr>';

	$output .='<tr><td>Moyens de paiement</td><td>';
	$output.='<input name="atos_payment_means" type="text"';
	if ($atos_payment_means = get_option('atos_payment_means')){$output.='value="'.$atos_payment_means .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Logo des cb ( ex : CB,2,VISA,2,MASTERCARD,2)</span></td></tr>';


	$output .='<tr><td>Mode test</td><td>';
	$output.='<input name="atos_test" type="checkbox"';
	if ('on'== get_option('atos_test')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">le paiement CB est automatique validé</span></td></tr>';
	
	$output .='<tr><td>Mode Debug</td><td>';
	$output.='<input name="atos_debug" type="checkbox"';
	if ('on'== get_option('atos_debug')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Affiche des informations à l\'écran à différents stades et envoie des emails</span></td></tr>';

	$atos_checkout_page_id=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ($atos_checkout_page_id!=NULL)
	{
	$output.='<tr><td colspan=2><span class="small description">Le short code [atos] se trouve sur la page : <a href="'.site_url('?page_id='.$atos_checkout_page_id->ID).'">'.$atos_checkout_page_id->ID.'</a></span></td></tr>';
}
else
{
	$output.='<tr><td colspan=2><span class="small description">Vous devez placer le short code [atos] sur une page de votre site ! </span></td></tr>';
}
	
	$output.='<tr><td colspan=2><span class="small description">Documentation : <a href="http://wpcb.fr/doc">http://wpcb.fr/doc</a></span></td></tr>';

	return $output;
}

function submit_atos(){
	if($_POST['atos_merchantid'] != null) {update_option('atos_merchantid',$_POST['atos_merchantid']);}
	if($_POST['atos_normal_return_url'] != null) {update_option('atos_normal_return_url',$_POST['atos_normal_return_url']);}
	if($_POST['atos_cancel_return_url'] != null) {update_option('atos_cancel_return_url',$_POST['atos_cancel_return_url']);}
	if($_POST['atos_gateway_image'] != null) {update_option('atos_gateway_image',$_POST['atos_gateway_image']);}
	if($_POST['atos_pathfile'] != null) {update_option('atos_pathfile',$_POST['atos_pathfile']);}
	if($_POST['atos_path_bin'] != null) {update_option('atos_path_bin',$_POST['atos_path_bin']);}
	if($_POST['atos_path_bin_response'] != null) {update_option('atos_path_bin_response',$_POST['atos_path_bin_response']);}
	if($_POST['atos_logfile'] != null) {update_option('atos_logfile',$_POST['atos_logfile']);}
	if(!empty($_POST["atos_test"])) {update_option('atos_test','on');} else { 	update_option('atos_test','off'); 	}
	if($_POST['atos_advert'] != null) {update_option('atos_advert',$_POST['atos_advert']);}
	if($_POST['atos_logo_id2'] != null) {update_option('atos_logo_id2',$_POST['atos_logo_id2']);}
	if($_POST['atos_payment_means'] != null) {update_option('atos_payment_means',$_POST['atos_payment_means']);}
	if(!empty($_POST["atos_debug"])) {update_option('atos_debug','on');} else { 	update_option('atos_debug','off'); 	}
	return true;
}


function gateway_atos($seperator, $sessionid){
	global $wpdb,$purchase_log;
	//This grabs the purchase log id from the database that refers to the $sessionid
	//$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
	// Redirect to page
	// Find page where post code [atos] is inserted
	$atos_checkout_page_id=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ('on'==get_option('atos_test'))
		{
		// Mode test, on considère que la CB a été acceptée automatiquement.
		//redirect to  transaction page and store in DB as a order with accepted payment
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
		// Update sale in db :
		//$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
		transaction_results($sessionid,true);
					$atos_callback='true';
		}
	else // Send to choose-cb-page :
		{
			$atos_callback='CB';
			}
	$url=site_url('?page_id='.$atos_checkout_page_id->ID.'&sessionid='.$sessionid.'&atos_callback='.$atos_callback);
	wp_redirect($url);
	exit;
}

if (!class_exists('atosLoader')) {
	class atosLoader {
		function atosLoader() {
			// Init options & tables during activation & deregister init option
			register_activation_hook( __file__, array(&$this, 'activate' ));
			register_deactivation_hook( __file__, array(&$this, 'deactivate' ));
			if(get_option('atos_msg')) {
				add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>'.get_option('atos_msg').'</strong></p></div>\';') );
				delete_option('atos_msg');
			}
		}
		// activate the plugin
		function activate() {
			$pluginDir = dirname(dirname(__file__)).'/wp-e-commerce';
			if(file_exists($pluginDir)) {
					//Move AutoResponse file out of the plugin directory because it causes problems :
					define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__)))));
					$AtosAutoResponsesourceFile = dirname(__file__).'/PathToAtosAutoResponse.php';
					$AtosAutoResponsedestinationFile = __ROOT__.'/PathToAtosAutoResponse.php';
					copy($AtosAutoResponsesourceFile, $AtosAutoResponsedestinationFile);
					if(!file_exists($AtosAutoResponsedestinationFile))
					{update_option('atos_msg', 'Déplacer manuellement les fichiers vers '.$AtosAutoResponsedestinationFile);}
					else
					{
						// Set default values for options :
						update_option('atos_merchantid','005009461440411'); 
						$atos_normal_return_url=site_url();
						update_option('atos_normal_return_url',$atos_normal_return_url);
						$atos_cancel_return_url=site_url();
						update_option('atos_cancel_return_url',$atos_cancel_return_url);
						$atos_gateway_image=plugins_url('wp-e-commerce-atos/logo/LogoMercanetBnpParibas.gif');
						update_option('atos_gateway_image',$atos_gateway_image);
						$serverpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
						update_option('atos_pathfile',$serverpath.'/cgi-bin/pathfilewp');
						update_option('atos_path_bin',$serverpath.'/cgi-bin/request');
						update_option('atos_path_bin_response',$serverpath.'/cgi-bin/response');
						update_option('atos_logfile',$serverpath.'/cgi-bin/logfile.txt');
						update_option('atos_test','off');
						update_option('atos_advert','advert.jpg');
						update_option('atos_logo_id2','logo_id2.jpg');
						update_option('atos_payment_means','CB,2,VISA,2,MASTERCARD,2');
						update_option('atos_debug','on');
					}
			}
			else
			{update_option('atos_msg', 'WP-Ecommerce Plugin should be installed first.'.$pluginDir);}
		}
		/**
		* deactivate the plugin
		*/
		function deactivate() {
			// Delete auto-response file from the root of the website
			define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__)))));
			$AtosAutoResponsedestinationFile = __ROOT__.'/PathToAtosAutoResponse.php';
			if(file_exists($AtosAutoResponsedestinationFile)) {unlink($AtosAutoResponsedestinationFile);}

			//delete config
			delete_option('atos_merchantid');
			delete_option('atos_normal_return_url');
			delete_option('atos_cancel_return_url');
			delete_option('atos_gateway_image');
			delete_option('atos_pathfile');
			delete_option('atos_path_bin');
			delete_option('atos_path_bin_response');
			delete_option('atos_logfile');
			delete_option('atos_test');
			delete_option('atos_msg');
			delete_option('atos_advert');
			delete_option('atos_logo_id2');
			delete_option('atos_payment_means');
			delete_option('atos_debug');
		}
	}
	$atosLoad = new atosLoader();
}

// add the hook for [atos] shortcode :
function shortcode_atos_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log;
	$sessionid=$_GET['sessionid'];
	if ($_GET['atos_callback']=='CB')
	{
		// Create the atos CB Form :
		$parm="merchant_id=".get_option('atos_merchantid');
		$parm="$parm merchant_country=fr";
		$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
		$amount=number_format($purchase_log['totalprice'],2)*100;
		$parm="$parm amount=".str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm currency_code=978";
		$parm="$parm pathfile=".get_option('atos_pathfile');
		//$parm="$parm normal_return_url=".site_url('PathToAtosAutoResponse.php?p=true-'.urlencode($sessionid));
		$parm="$parm normal_return_url=".get_option('atos_normal_return_url');
		//$parm="$parm cancel_return_url=".site_url('PathToAtosAutoResponse.php?p=cancel-'.urlencode($sessionid));
		$parm="$parm cancel_return_url=".get_option('atos_cancel_return_url');
		$parm="$parm automatic_response_url=".site_url('PathToAtosAutoResponse.php');
		$parm="$parm language=fr";
		$parm="$parm payment_means=".get_option('atos_payment_means');
		$parm="$parm header_flag=no";
		$parm="$parm order_id=$sessionid";
		$parm="$parm logo_id2=".get_option('atos_logo_id2');
		$parm="$parm advert=".get_option('atos_advert');
		if (get_option('atos_debug')=='on'){$parm_pretty=str_replace  ( ' '  , '<br/>'  , $parm);echo $parm_pretty;}
		//Now we have the information we want to send to the gateway in a nicely formatted string we can setup the atos exec
		$path_bin = get_option('atos_path_bin');
		$result=exec("$path_bin $parm");
		$tableau = explode ("!", "$result");
		$code = $tableau[1];
		$error = $tableau[2];
		$message = $tableau[3];
		if (( $code == "" ) && ( $error == "" ) )
		{
			$message="Erreur appel request mercanet.";
			
		$purch_log_email=get_option('purch_log_email');
		if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
				
		if ( ($purch_log_email=get_option( 'purch_log_email' ) != null) ){ $message.= "Merci de rapporter cette erreur à".$purch_log_email;}
		$message.= "executable request non trouve $path_bin";
		}
		//	Erreur, affiche le message d'erreur
		else if ($code != 0) {
			$message="<center><b><h2>Erreur appel API de paiement.</h2></center></b><br><br><br> message erreur : $error <br>";
		}
		//	OK, affiche le formulaire HTML
		else
		{
			// leave message as it is ! message content all cb img
		}
		// End of atos
	}
	elseif ($_GET['atos_callback']=='true')
	{
		// la page Autoresponse renvoi ici avec en Get l'id de session, donc :
		$message='Merci pour votre achat!';
		if (isset($_GET['sessionid']))
		{
			// La mise à jour de la bd est faite dans AutoResponse.php mais on peut le refiare ici au cas ou. Ca ne renvoie pas de second email car email_sent à été mis à 1
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid;
			$wpdb->query($sql);
			$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
			transaction_results($_GET['sessionid'],true);
		}
	}
	elseif ($_GET['atos_callback']=='cancel')
	{
		$message='Votre paiement n\'a pas abouti<br/>';
		if (isset($_GET['sessionid']))
		{
			$message.= "Si vous pensez que c'est une erreur technique, envoyez nous un email avec cette information : sessionid=".$_GET['sessionid']."<br/>";
		}
	}
	else
	{
		$message='Appel direct interdit';
		if (isset($_GET['sessionid']))
		{
			$message.= "Si vous pensez que c'est une erreur technique, envoyez nous un email avec cette information : sessionid=".$_GET['sessionid'];
		}
	}
	// Output the message : it can be an error or the different CB logos if everything goes right !
	return $message;
}
add_shortcode( 'atos', 'shortcode_atos_handler' );