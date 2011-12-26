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
	function submit(){
	global $wpdb,$purchase_log;
	$sessionid=$this->cart_data['session_id'];
	// Trouver la page où le shortcode [atos] se situe.
	// Bug si plusieurs fois le shortcode [atos], à résoudre
	$atos_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ('on'==get_option('atos_test'))
		{
		// Mode test, on considère que la CB a été acceptée automatiquement.
		// Affiche la page de la fin de transaction et on met à jour la base de donnée avec un vente réussie
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
		// redirection is inside transaction result :
		transaction_results($sessionid,false);
		}
	else // Affiche les icônes des cartes bancaires :
		{
			$action='CB';
			wp_redirect(site_url('?p='.$atos_checkout_page->ID.'&sessionid='.$sessionid.'&action='.$action));
		}
	exit;
} // end of submit function
} // end of class.

	
	

function submit_atos(){
	if($_POST['atos_merchantid']!=null) {update_option('atos_merchantid',$_POST['atos_merchantid']);}
	if($_POST['atos_currency_code']!=null) {update_option('atos_currency_code',$_POST['atos_currency_code']);}
	if($_POST['atos_merchant_country']!=null) {update_option('atos_merchant_country',$_POST['atos_merchant_country']);}
	if($_POST['atos_language']!=null) {update_option('atos_language',$_POST['atos_language']);}
	if($_POST['atos_header_flag']!=null) {update_option('atos_header_flag',$_POST['atos_header_flag']);}
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
	
	$output='<tr><td>'.__('Url of image displayed during payement method choice','wpcb').'</td><td>';
	$output.='<input name="atos_gateway_image" type="text"';
	if ($atos_gateway_image = get_option('atos_gateway_image')){$output.='value="'.$atos_gateway_image .'"';}
	$output.='/></td></tr>';
	
	$output.='<tr><td><pre>merchant_id</pre></td><td>'; 
	$output.='<input name="atos_merchantid" type="text"';
	if ($atos_merchantid = get_option('atos_merchantid')){$output.='value="'.$atos_merchantid .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>currency_code</pre></td><td>';
	$output.='<input name="atos_currency_code" type="text"';
	if ($atos_currency_code = get_option('atos_currency_code')){$output.='value="'.$atos_currency_code .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('978 -> €','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>merchant_country</pre></td><td>';
	$output.='<input name="atos_merchant_country" type="text"';
	if ($atos_merchant_country = get_option('atos_merchant_country')){$output.='value="'.$atos_merchant_country .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('fr -> France','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>language</pre></td><td>';
	$output.='<input name="atos_language" type="text"';
	if ($atos_language = get_option('atos_language')){$output.='value="'.$atos_language .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('fr -> French','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>header_flag</pre></td><td>';
	$output.='<input name="atos_header_flag" type="text"';
	if ($atos_header_flag = get_option('atos_header_flag')){$output.='value="'.$atos_header_flag .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('ex : no','wpcb').'</span><td></tr>';

	$output.='<tr><td>normal_return_url</td><td>';
	$output.='<input name="atos_normal_return_url" type="text"';
	if ($atos_normal_return_url = get_option('atos_normal_return_url')){$output.='value="'.$atos_normal_return_url .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';
	
	// Cancel return url :
	$output.='<tr><td>cancel_return_url</td><td>';
	$output.='<input name="atos_cancel_return_url" type="text"';
	if ($atos_cancel_return_url = get_option('atos_cancel_return_url')){$output.='value="'.$atos_cancel_return_url .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';

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
	$output.='<tr><td colspan=2><span class="small description">'.__('Leave empty to stop saving log','wpcb').'</span></td></tr>';

	$output.='<tr><td><pre>advert</pre></td><td>';
	$output.='<input name="atos_advert" type="text"';
	if ($atos_advert = get_option('atos_advert')){$output.='value="'.$atos_advert .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>logo_id2</pre></td><td>';
	$output.='<input name="atos_logo_id2" type="text"';
	if ($atos_logo_id2 = get_option('atos_logo_id2')){$output.='value="'.$atos_logo_id2 .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';

	$output.='<tr><td><pre>payment_means</pre></td><td>';
	$output.='<input name="atos_payment_means" type="text"';
	if ($atos_payment_means = get_option('atos_payment_means')){$output.='value="'.$atos_payment_means .'"';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('see dictionnaire des données Atos','wpcb').'</span><td></tr>';

	$output.='<tr><td>Mode test</td><td>';
	$output.='<input name="atos_test" type="checkbox"';
	if ('on'== get_option('atos_test')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('Atos payement is automaticaly accepted for debug purpose.','wpcb').'</span></td></tr>';
	
	$output.='<tr><td>Debug</td><td>';
	$output.='<input name="atos_debug" type="checkbox"';
	if ('on'== get_option('atos_debug')){$output.='CHECKED';}
	$output.='/></td></tr>';
	$output.='<tr><td colspan=2><span class="small description">'.__('Show debug infos.','wpcb').'</span></td></tr>';

	$output.='<tr><td colspan=2>'.__('Informations on your installation : ','wpcb').'</td></tr>';
	$output.='<tr><td>'.__('Wordpress root','wpcb').'</td><td><pre>'.__WPRoot__.'</pre></td></tr>';
	$output.='<tr><td>'.__('Site root','wpcb').'</td><td><pre>'.__ServerRoot__.'</pre></td></tr>';
	

	$atos_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[atos]%' AND `post_status`='publish'");
	if ($atos_checkout_page!=NULL)
	{
	$output.='<tr><td colspan=2><span class="small description">'.__('Atos shortcode [atos] is on page :','wpcb').'<a href="'.site_url('?page_id='.$atos_checkout_page->ID).'">'.$atos_checkout_page->ID.'</a></span></td></tr>';
}
else
{
	$output.='<tr><td colspan=2><span class="small description" style="color:red">'.__('You should place Atos shortcode [atos] somewhere in a page of your site!','wpcb').'</span></td></tr>';
}
	
	$output.='<tr><td colspan=2><span class="small description">'.__('Documentation','wpcb').' : <a href="http://wpcb.fr/doc">http://wpcb.fr/doc</a></span></td></tr>';
	return $output;
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
		$parm="$parm merchant_country=".get_option('atos_merchant_country');
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount=number_format($purchase_log->totalprice,2)*100;
		$parm="$parm amount=".str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm currency_code=".get_option('atos_currency_code');
		$parm="$parm pathfile=".get_option('atos_pathfile');
		$parm="$parm normal_return_url=".get_option('atos_normal_return_url');
		$parm="$parm cancel_return_url=".get_option('atos_cancel_return_url');
		$parm="$parm automatic_response_url=".site_url('Pointeur_automatic_response.php');
		$parm="$parm language=".get_option('atos_language');
		$parm="$parm payment_means=".get_option('atos_payment_means');
		$parm="$parm header_flag=".get_option('atos_header_flag');
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
			$message="<p>".__('Error calling the atos api : exec request not found','wpcb')."  $path_bin</p>";
			if (get_option('atos_debug')=='on'){ $message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";}
		}
		elseif ($code != 0) {
			$message="<p>".__('Atos API error : ','wpcb')." $error</p>";
			if (get_option('atos_debug')=='on'){ $message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";}
		}
		else
		{
			// Affiche le formulaire avec le choix des cartes bancaires :
			$message = $tableau[3];
		}
		// End of atos
	}
	else
	{
		$message='<p>'.__('Direct call to this page not allowed','wpcb').'</p>';
	}
	return $message;
}
add_shortcode( 'atos', 'shortcode_atos_handler' );