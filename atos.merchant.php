<?php


define('__WPRoot__',dirname(dirname(dirname(dirname(__FILE__)))));
define('__ServerRoot__',dirname(dirname(dirname(dirname(dirname(__FILE__))))));


$nzshpcrt_gateways[$num] = array(
'name' => 'Atos',
'api_version' => 2.0,
'has_recurring_billing' => true,
'display_name' => 'Atos',
'wp_admin_cannot_cancel' => false,
'requirements' => array(),
'form' => 'form_atos',
'internalname' => 'atos',
'class_name' => 'wpsc_merchant_atos',
'submit_function' => 'submit_atos',
'image' => get_option('atos_gateway_image')
);


class wpsc_merchant_atos extends wpsc_merchant {
	function submit($seperator, $sessionid){
	global $wpdb,$purchase_log;
	// Trouver la page où le shortcode [atos] se situe.
	// Bug si plusieurs fois le shortcode [atos], à résoudre
	$atos_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ('on'==get_option('atos_test'))
		{
		// Mode test, on considère que la CB a été acceptée automatiquement.
		// Affiche la page de la fin de transaction et on met à jour la base de donnée avec un vente réussie
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
		transaction_results($sessionid,true);
		$action='test';
		}
	else // Affiche les icônes des cartes bancaires :
		{
			$action='CB';
		}
	wp_redirect(site_url('?p='.$atos_checkout_page->ID.'&sessionid='.$sessionid.'&action='.$action));
	exit;
} // end of submit function
} // end of class.

	
	

function submit_atos(){
	if($_POST['atos_merchantid']!=null) {update_option('atos_merchantid',$_POST['atos_merchantid']);}
	if($_POST['atos_normal_return_url']!=null) {update_option('atos_normal_return_url',$_POST['atos_normal_return_url']);}
	if($_POST['atos_cancel_return_url']!=null) {update_option('atos_cancel_return_url',$_POST['atos_cancel_return_url']);}
	if($_POST['atos_gateway_image']!=null) {update_option('atos_gateway_image',$_POST['atos_gateway_image']);}
	if($_POST['atos_pathfile']!=null) {update_option('atos_pathfile',$_POST['atos_pathfile']);}
	if($_POST['atos_path_bin']!=null) {update_option('atos_path_bin',$_POST['atos_path_bin']);}
	if($_POST['atos_path_bin_response']!=null) {update_option('atos_path_bin_response',$_POST['atos_path_bin_response']);}
	if($_POST['atos_logfile']!=null) {update_option('atos_logfile',$_POST['atos_logfile']);}
	if(!empty($_POST["atos_test"])) {update_option('atos_test','on');} else { 	update_option('atos_test','off'); 	}
	if($_POST['atos_advert']!=null) {update_option('atos_advert',$_POST['atos_advert']);}
	if($_POST['atos_logo_id2']!=null) {update_option('atos_logo_id2',$_POST['atos_logo_id2']);}
	if($_POST['atos_payment_means']!=null) {update_option('atos_payment_means',$_POST['atos_payment_means']);}
	if(!empty($_POST["atos_debug"])) {update_option('atos_debug','on');} else { 	update_option('atos_debug','off'); 	}
	return true;
}





function form_atos() {
	global $wpdb;
	
	$output='<tr><td>Url de l\'image affichée lors du choix de la méthode de paiement</td><td>';
	$output.='<input name="atos_gateway_image" type="text"';
	if ($atos_gateway_image = get_option('atos_gateway_image')){$output.='value="'.$atos_gateway_image .'"';}
	$output.='/></td></tr>';
	
	$output.='<tr><td><pre>merchant_id</pre></td><td>'; //Attention pas de .= sur la première ligne !
	$output.='<input name="atos_merchantid" type="text"';
	if ($atos_merchantid = get_option('atos_merchantid')){$output.='value="'.$atos_merchantid .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';

	$output.='<tr><td>normal_return_url</td><td>';
	$output.='<input name="atos_normal_return_url" type="text"';
	if ($atos_normal_return_url = get_option('atos_normal_return_url')){$output.='value="'.$atos_normal_return_url .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';
	
	// Cancel return url :
	$output.='<tr><td>cancel_return_url</td><td>';
	$output.='<input name="atos_cancel_return_url" type="text"';
	if ($atos_cancel_return_url = get_option('atos_cancel_return_url')){$output.='value="'.$atos_cancel_return_url .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';

	$output.='<tr><td><pre>pathfile</pre></td><td>';
	$output.='<input name="atos_pathfile" type="text"';
	if ($atos_pathfile = get_option('atos_pathfile')){$output.='value="'.$atos_pathfile .'"';}
	$output.='/></td></tr>';

	$output.='<tr><td><pre>request</pre></td><td>';
	$output.='<input name="atos_path_bin" type="text"';
	if ($atos_path_bin = get_option('atos_path_bin')){$output.='value="'.$atos_path_bin .'"';}
	$output.='/></td></tr>';

	$output.='<tr><td><pre>response</pre></td><td>';
	$output.='<input name="atos_path_bin_response" type="text"';
	if ($atos_path_bin_response = get_option('atos_path_bin_response')){$output.='value="'.$atos_path_bin_response .'"';}
	$output.='/></td></tr>';

	$output.='<tr><td><pre>logfile</pre> (optionel)</td><td>';
	$output.='<input name="atos_logfile" type="text"';
	if ($atos_logfile = get_option('atos_logfile')){$output.='value="'.$atos_logfile .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Laisser vide pour ne pas enregistrer de log</span></td></tr>';

	$output.='<tr><td><pre>advert</pre></td><td>';
	$output.='<input name="atos_advert" type="text"';
	if ($atos_advert = get_option('atos_advert')){$output.='value="'.$atos_advert .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';

	$output.='<tr><td><pre>logo_id2</pre></td><td>';
	$output.='<input name="atos_logo_id2" type="text"';
	if ($atos_logo_id2 = get_option('atos_logo_id2')){$output.='value="'.$atos_logo_id2 .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';

	$output.='<tr><td><pre>payment_means</pre></td><td>';
	$output.='<input name="atos_payment_means" type="text"';
	if ($atos_payment_means = get_option('atos_payment_means')){$output.='value="'.$atos_payment_means .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">cf. dictionnaire des données Atos</span><td></tr>';

	$output.='<tr><td>Mode test</td><td>';
	$output.='<input name="atos_test" type="checkbox"';
	if ('on'== get_option('atos_test')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">le paiement CB est automatique validé</span></td></tr>';
	
	$output.='<tr><td>Mode debug</td><td>';
	$output.='<input name="atos_debug" type="checkbox"';
	if ('on'== get_option('atos_debug')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">Affiche des informations de debug et envoie des emails de debug</span></td></tr>';

	$output.='<tr><td colspan=2>Informations utiles sur votre installation : </td></tr>';
	$output.='<tr><td>Racine Wordpress</td><td><pre>'.__WPRoot__.'</pre></td></tr>';
	$output.='<tr><td>Racine Site</td><td><pre>'.__ServerRoot__.'</pre></td></tr>';
	

	$atos_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ($atos_checkout_page!=NULL)
	{
	$output.='<tr><td colspan=2><span class="small description">Le short code [atos] se trouve sur la page : <a href="'.site_url('?page_id='.$atos_checkout_page->ID).'">'.$atos_checkout_page->ID.'</a></span></td></tr>';
}
else
{
	$output.='<tr><td colspan=2><span class="small description" style="color:red">Vous devez placer le short code [atos] sur une page de votre site ! </span></td></tr>';
}
	
	$output.='<tr><td colspan=2><span class="small description">Documentation : <a href="http://wpcb.fr/doc">http://wpcb.fr/doc</a></span></td></tr>';
	return $output;
}


if (!class_exists('atosLoader')) {
	class atosLoader {
		function atosLoader() {
			register_activation_hook( __file__, array(&$this, 'activate' ));
			register_deactivation_hook( __file__, array(&$this, 'deactivate' ));
			if(get_option('atos_msg')) {
				add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>'.get_option('atos_msg').'</strong></p></div>\';') );
				delete_option('atos_msg');
			}
		}
		// activate the plugin
		function activate() {
			$wpecommercePluginDir = dirname(dirname(__file__)).'/wp-e-commerce';
			if(file_exists($wpecommercePluginDir)) {
					//On déplace un pointeur vers automatic_response.php à l'extérieur du dossier de plugin car sinon ça ne marche pas, bug à résoudre:
					if(!copy(dirname(__file__).'/Pointeur_automatic_response.php',__WPRoot__.'/Pointeur_automatic_response.php'))
					{update_option('atos_msg', 'Déplacer manuellement le pointeur (Pointeur_automatic_response_url) à l\'exterieur du dossier du plugin');}
					else
					{
						// Set default values for options :
						update_option('atos_merchantid','005009461440411'); 
						update_option('atos_normal_return_url',site_url());
						update_option('atos_cancel_return_url',site_url());
						update_option('atos_gateway_image',plugins_url('wpcb/logo/LogoMercanetBnpParibas.gif'));
						update_option('atos_pathfile',__ServerRoot__.'/cgi-bin/pathfile');
						update_option('atos_path_bin',__ServerRoot__.'/cgi-bin/request');
						update_option('atos_path_bin_response',__ServerRoot__.'/cgi-bin/response');
						update_option('atos_logfile',__ServerRoot__.'/cgi-bin/logfile.txt');
						update_option('atos_test','off');
						update_option('atos_advert','advert.jpg');
						update_option('atos_logo_id2','logo_id2.jpg');
						update_option('atos_payment_means','CB,2,VISA,2,MASTERCARD,2');
						update_option('atos_debug','on');
					}
			}
			else
			{update_option('atos_msg', 'Le plugin WP-eCommerce doit être installé. ('.$wpecommercePluginDir.')');}
		}
		/**
		* deactivate the plugin
		*/
		function deactivate() {
			// Supprimer le pointeur de la racine de Wordpress
			unlink( __WPRoot__.'/PointeurPointeur_automatic_response.php');
			// Supprimer les options enregistrées par le plugin
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

function shortcode_atos_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log;
	$sessionid=$_GET['sessionid'];
	$purch_log_email=get_option('purch_log_email');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	if ($_GET['action']=='CB')
	{
		// cf. Dictionnaire des Données Atos :
		$parm="merchant_id=".get_option('atos_merchantid');
		$parm="$parm merchant_country=fr"; // A mettre dans les options, todo
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount=number_format($purchase_log->totalprice,2)*100;
		$parm="$parm amount=".str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm currency_code=978"; // A mettre dans les options, todo
		$parm="$parm pathfile=".get_option('atos_pathfile');
		$parm="$parm normal_return_url=".get_option('atos_normal_return_url');
		$parm="$parm cancel_return_url=".get_option('atos_cancel_return_url');
		$parm="$parm automatic_response_url=".site_url('Pointeur_automatic_response.php');
		$parm="$parm language=fr";// A mettre dans les options, todo
		$parm="$parm payment_means=".get_option('atos_payment_means');
		$parm="$parm header_flag=no";
		$parm="$parm order_id=$sessionid";
		$parm="$parm logo_id2=".get_option('atos_logo_id2');
		$parm="$parm advert=".get_option('atos_advert');
		if (get_option('atos_debug')=='on'){$parm_pretty=str_replace(' ','<br/>',$parm);echo $parm_pretty;}
		$path_bin = get_option('atos_path_bin');
		$result=exec("$path_bin $parm");
		$tableau = explode ("!","$result");
		$code = $tableau[1];
		$error = $tableau[2];
		if (( $code=="") && ($error==""))
		{
			$message="<p>Erreur appel request mercanet : executable request non trouve $path_bin</p>";
			if (get_option('atos_debug')=='on'){ $message.= "<p>Merci de rapporter cette erreur à".$purch_log_email."</p>";}
		}
		elseif ($code != 0) {
			$message="<p>Erreur appel API de paiement, message erreur : $error</p>";
			if (get_option('atos_debug')=='on'){ $message.= "<p>Merci de rapporter cette erreur à".$purch_log_email."</p>";}
		}
		else
		{
			// Affiche le formulaire avec le choix des cartes bancaires :
			$message = $tableau[3];
		}
		// End of atos
	}
	elseif ($_GET['action']=='test')
	{
		// la page Autoresponse renvoi ici avec en Get l'id de session, donc :
		$message='<p>Merci pour votre achat en mode test !</p>';
		if (isset($_GET['sessionid']))
		{
			// La mise à jour de la bd est faite dans AutoResponse.php mais on peut le refiare ici au cas ou. Ca ne renvoie pas de second email car email_sent à été mis à 1
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$_GET['sessionid']);
			$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$_GET['sessionid']." LIMIT 1",ARRAY_A) ;
			transaction_results($_GET['sessionid'],true);
		}
	}
	else
	{
		$message='<p>Accès direct à cette page interdit</p>';
	}
	return $message;
}
add_shortcode( 'atos', 'shortcode_atos_handler' );