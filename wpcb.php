<?php
/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://wpcb.fr
Description: Credit Card Payement Gateway for ATOS SIPS (Mercanet,...) (WP e-Commerce is required)
Version: 1.1.8.1
Author: 6WWW
Author URI: http://6www.net
*/

if (!defined('__WPRoot__')){define('__WPRoot__',dirname(dirname(dirname(dirname(__FILE__)))));}
if (!defined('__ServerRoot__')){define('__ServerRoot__',dirname(dirname(dirname(dirname(dirname(__FILE__))))));}
if (!defined('__WPUrl__')){define('__WPUrl__',site_url());}


// Actions lors de la desactivation du plugin :
register_deactivation_hook( __FILE__, 'wpcb_deactivate' );
function wpcb_deactivate(){
	// On deactivate, remove files :
	unlink(__WPRoot__.'/automatic_response.php');
	unlink(__WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php');
}

// Actions lors de la mise en jour du plugin :
add_action( 'admin_init', 'wpcb_update' );
function wpcb_update(){
	$wp_version_required="3.0";
	global $wp_version;
	$plugin=plugin_basename( __FILE__ );
	$plugin_data=get_plugin_data( __FILE__,false);
	if ( version_compare($wp_version,$wp_version_required,"<")){
		if(is_plugin_active($plugin)){
			deactivate_plugins($plugin);
			wp_die( "'".$plugin_data['Name']."' requires WordPress ".$wp_version_required." or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
	// Check if it is a plugin update :
	$options = get_option('wpcb_options');
	if (version_compare($options['version'],$plugin_data['Version'],"<")){
		wpcb_activate(); // So that the 2 files wpcb.merchant.php & automatic_response.php are copied again
	}
		 // if the ZF plugin is successfully loaded this constant is set to true
  if (defined('WP_ZEND_FRAMEWORK') && constant('WP_ZEND_FRAMEWORK')) {
    return true;
  }
  // you can also check if ZF is available on the system
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (file_exists("$path/Zend/Loader.php")) {
      define('WP_ZEND_FRAMEWORK', true);
      return true;
    }
  }
  // nothing found, you may advice the user to install the ZF plugin
  define('WP_ZEND_FRAMEWORK', false);
}



// Lors de la desinstallation : 
register_uninstall_hook(__FILE__, 'wpcb_delete_plugin_options');
function wpcb_delete_plugin_options() {
	delete_option('wpcb_options');
	unlink(__WPRoot__.'/automatic_response.php');
	unlink(__WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php');
}

register_activation_hook(__FILE__, 'wpcb_activate');
function wpcb_activate() {
	$options = get_option('wpcb_options');
	$sourceFile = dirname(__FILE__). '/automatic_response.php';
	$destinationFile = __WPRoot__.'/automatic_response.php';			
	copy($sourceFile, $destinationFile);
	$sourceFile = dirname(__FILE__). '/wpcb.merchant.php';
	$destinationFile = __WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
	copy($sourceFile, $destinationFile);
    if(!is_array($options)) {
		delete_option('wpcb_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$options = array("merchant_id" => "082584341411111","pathfile" => __ServerRoot__."cgi-bin/demo/pathfile",
					"path_bin_request" => __ServerRoot__."cgi-bin/demo/request",
					"path_bin_response" => __ServerRoot__."cgi-bin/demo/response" ,
					"merchant_country" => "fr","currency_code" => "978",
					"normal_return_url" => __WPUrl__,"cancel_return_url" => __WPUrl__,
					"language" => "fr","payment_means" => "CB,2,VISA,2,MASTERCARD,2",
					"header_flag" => "no","logfile" => "/homez.136/littlebii/cgi-bin/demo/log.txt",
					"advert" => "advert.jpg","logo_id" => "logo_id.jpg","logo_id2" => "logo_id2.jpg",
					"wpec_gateway_image" => __WPUrl__."/wp-content/plugins/wpcb/logo/LogoMercanetBnpParibas.gif",
					"wpec_display_name" => "Cartes bancaires (Visa, Master Card,...)",
					"test"=>"0","demo"=>"0","version"=>$plugin_data['Version'],
					"emailapiKey"=>"salut@yop.com","apiKey"=>"***",
					"googleemail"=>"salut@gmail.com","googlepassword"=>"***","spreadsheetKey"=>"jLJDjj");
		update_option('wpcb_options',$options);
	}
}

add_action('admin_init', 'wpcb_init' );
function wpcb_init(){
	register_setting('wpcb_plugin_options','wpcb_options','wpcb_validate_options');
}

add_filter( 'plugin_action_links', 'wpcb_plugin_action_links',10,2);
add_action('admin_menu', 'wpcb_add_options_page');
function wpcb_add_options_page() {
	add_options_page('wpcb Options Page', 'Wpcb', 'manage_options', __FILE__, 'wpcb_render_form');
}
function wpcb_render_form() {
	global $wpdb;
	$options = get_option('wpcb_options');
	$plugin_data = get_plugin_data( __FILE__,false);
	?>
	<div class="wrap">
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Options WP e-Commerce Cartes Bancaires par 6WWW</h2>
		<ol>
		<?php
		$sourceFile=dirname(__FILE__).'/automatic_response.php';
		$destinationFile = __WPRoot__.'/automatic_response.php';			
		if (
		(!file_exists($destinationFile)) || 
		( (isset($_GET['action'])) && ($_GET['action']=='copyautomaticresponse')) 
			){
				copy($sourceFile, $destinationFile);
		}
		if(file_exists(!$destinationFile)){
			$nonce_url=wp_nonce_url(__WPUrl__.'/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse');
			echo '<li><span style="color:red;">Copier le fichier automatic_response.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		$sourceFile = dirname(__FILE__).'/wpcb.merchant.php';
		$destinationFile = __WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copywpcbmerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(__WPUrl__.'/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant');
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/wpcb.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		$wpcb_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish'");
		if ($wpcb_checkout_page!=NULL){
			echo '<li><span style="color:green">Le shortcode [wpcb] est sur la page : <a href="'.site_url('?page_id='.$wpcb_checkout_page->ID).'">'.$wpcb_checkout_page->ID.'</a> -> OK!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Vous devez placer le shortcode [wpcb] quelque part sur votre site</span></li>';
		}
		// API
		$post_data['apiKey']=$options['apiKey'];
		$post_data['emailapiKey']=$options['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			echo '<li><span style="color:green">Votre clé API est valide -> OK!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Optionel : Vous pouvez débloquer l\'assistance et des fonctions supplémentaires en <a href="http://wpcb.fr/api-key/" target="_blank">achetant une clé API</a></span> C\'est pas cher et ça m\'aide à améliorer mes plugins.</li>';
		}
		// END OF API
		?>
		
		<?php if (WP_ZEND_FRAMEWORK){
			echo '<li>Zend is installed -> Ok !</li>';
			$GoogleConnection=true;
		try {$client = Zend_Gdata_ClientLogin::getHttpClient($options['googleemail'],$options['googlepassword']);}
		catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
		if ($GoogleConnection){
			echo '<li>Your google connection is living-> Ok!</li>';
		}
		else {
			echo '<li>Your google connection is not ok, check email and pass below</li>';
		}
		// Todo : catch error if spreadsheetKey is wrong
		}
		else{
		echo 'Install Zend first : http://h6e.net/wiki/wordpress/plugins/zend-framework to have acces to new features';	
		}?>
		
		
		<li>Remplissez les informations ci dessous. Reportez vous au Dictionnaire Atos fournit par votre banque.</li>
		</ol>
		<form method="post" action="options.php">
			<?php settings_fields('wpcb_plugin_options'); ?>
			<table class="form-table">
				<tr>
				<th scope="row">Merchant id</th>
				<td><input type="text" size="57" name="wpcb_options[merchant_id]" value="<?php echo $options['merchant_id']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Pathfile</th>
				<td><input type="text" size="57" name="wpcb_options[pathfile]" value="<?php echo $options['pathfile']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Request Pathbin</th>
				<td><input type="text" size="57" name="wpcb_options[path_bin_request]" value="<?php echo $options['path_bin_request']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">bin_response</th>
				<td><input type="text" size="57" name="wpcb_options[path_bin_response]" value="<?php echo $options['path_bin_response']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">merchant_country (fr pour france)</th>
				<td><input type="text" size="3" name="wpcb_options[merchant_country]" value="<?php echo $options['merchant_country']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">currency_code (978 pour €)</th>
				<td><input type="text" size="3" name="wpcb_options[currency_code]" value="<?php echo $options['currency_code']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">normal_return_url (là où les gens sont redirigés lorsqu'ils cliquent sur retour à la boutique)</th>
				<td><input type="text" size="57" name="wpcb_options[normal_return_url]" value="<?php echo $options['normal_return_url']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">cancel_return_url (là ou les gens sont redirigés s'ils annulent leur paiement)</th>
				<td><input type="text" size="57" name="wpcb_options[cancel_return_url]" value="<?php echo $options['cancel_return_url']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">language (fr pour français)</th>
				<td><input type="text" size="3" name="wpcb_options[language]" value="<?php echo $options['language']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">payment_means</th>
				<td><input type="text" size="57" name="wpcb_options[payment_means]" value="<?php echo $options['payment_means']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">header_flag (par défaut: no)</th>
				<td><input type="text" size="57" name="wpcb_options[header_flag]" value="<?php echo $options['header_flag']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">advert</th>
				<td><input type="text" size="57" name="wpcb_options[advert]" value="<?php echo $options['advert']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">logo_id</th>
				<td><input type="text" size="57" name="wpcb_options[logo_id]" value="<?php echo $options['logo_id']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">logo_id2</th>
				<td><input type="text" size="57" name="wpcb_options[logo_id2]" value="<?php echo $options['logo_id2']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Nom qui s'affiche avant l'image sur la page de choix du mode de paiement</th>
				<td><input type="text" size="57" name="wpcb_options[wpec_display_name]" value="<?php echo $options['wpec_display_name']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Image qui s'affiche juste à coté du nom (Actuellement : <img src="<?php echo $options['wpec_gateway_image']; ?>"></th>
				<td><input type="text" size="57" name="wpcb_options[wpec_gateway_image]" value="<?php echo $options['wpec_gateway_image']; ?>" /></td>
				</tr>				
				<tr>
				<th scope="row">Log file (Optionel)</th>
				<td><input type="text" size="57" name="wpcb_options[logfile]" value="<?php echo $options['logfile']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Clé API (Optionel: permet de débloquer des options et donne accès à l'assistance et aux mises à jour de sécurité)</th>
				<td><input type="text" size="57" name="wpcb_options[apiKey]" value="<?php echo $options['apiKey']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Email paypal utilisé pour l'achat de votre clé API (Optionel)</th>
				<td><input type="text" size="57" name="wpcb_options[emailapiKey]" value="<?php echo $options['emailapiKey']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Clé de la feuille de calcul Google Drive (Optionel : pour l'ajout des ventes dans Google Drive)</th>
				<td><input type="text" size="57" name="wpcb_options[spreadsheetKey]" value="<?php echo $options['spreadsheetKey']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Email Gmail ou Google App (Optionel : pour l'ajout des ventes dans Google Drive)</th>
				<td><input type="text" size="57" name="wpcb_options[googleemail]" value="<?php echo $options['googleemail']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Mot de passe Gmail (Optionel : pour l'ajout des ventes dans Google Drive)</th>
				<td><input type="password" size="57" name="wpcb_options[googlepassword]" value="<?php echo $options['googlepassword']; ?>" /></td>
				</tr>
				<!-- Checkbox Buttons -->
				<tr valign="top">
				<th scope="row">Pour les developpeur</th>
				<td>
				<label><input name="wpcb_options[test]" type="checkbox" value="1" <?php if (isset($options['test'])) { checked('1', $options['test']); } ?> /> Mode test</em></label><br />
<label><input name="wpcb_options[demo]" type="checkbox" value="1" <?php if (isset($options['demo'])) { checked('1', $options['demo']); } ?> /> Mode demo</em></label><br />
				</td>
				</tr>
			</table>
			<input type="hidden" name="wpcb_options[version]" value="<?php echo $plugin_data['Version']; ?>" />
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<p style="margin-top:15px;">
		
		<?php
		echo '<p>Infos:</p>';
		echo '<ul>';
		echo '<li><p>Plugin version : '.$options['version'].'</li>';
		echo '<li><p>Racine wordpress : '.__WPRoot__.'</p></li>';
		echo '<li>Racine site : '.__ServerRoot__.'</li>';
		$nonce_url=wp_nonce_url(__WPUrl__.'/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse');
		echo '<li>Developpeur : Copier le fichier automatic_response.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		$nonce_url=wp_nonce_url(__WPUrl__.'/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant');
		echo '<li>Developpeur : Copier le fichier '.dirname(__FILE__).'/wpcb.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		?>
			<li><a href="http://www.seoh.fr" target="_blank">Référencer votre site e-commerce avec l'agence SEOh</a></li>
			<li><a href="http://profiles.wordpress.org/6www">Les autres plugins de 6WWW</a></li>
			</ul>
		</p>
	</div>
	<?php	
	
	// Debug google doc : 
	if (WP_DEBUG){
	// Create an rand tableau 32 size
	$tableau_debug=array('NULL','1','2','5566','fr','100','8755900','CB','10-02-2012','11:50','10-02-2012','004','certif','22','978','4974','545','1','22','Comp','CompInfo','return','caddie','Merci','fr','fr','001','8787084074894','my@email.com','1.10.21.192','30',	'direct','data');
	$tableau=$tableau_debug;
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
	//Add to Sales in google doc using Zend : (require a plugin Zend)
	$service=Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
	$client=Zend_Gdata_ClientLogin::getHttpClient($options['googleemail'],$options['googlepassword'], $service);
	// On va chercher le numéro de la feuille :
	$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery(); // todo pour pas de client ici ?
	$query_worksheet->setSpreadsheetKey($options['spreadsheetKey']);
	$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
	$feed = $spreadsheetService->getWorksheetFeed($query_worksheet);
	foreach($feed->entries as $entry){
		$worksheetId_PremiereFeuille=basename($entry->id);
		break; // on arrete la boucle, donc on écrit dans la première feuille !!
	}
	$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
	// Insert row in google spreadsheet :
	$insertedListEntry = $spreadsheetService->insertRow($response,$options['spreadsheetKey'],$worksheetId_PremiereFeuille);	

	
	} // Fin du debug
	
} // Fin de la fonction des réglages

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpcb_validate_options($input) {
	foreach ($input as $key=>$value){$input[$key]=wp_filter_nohtml_kses($input[$key]);}
	return $input;
}

// Display a Settings link on the main Plugins page
function wpcb_plugin_action_links( $links, $file ) {
	if ($file==plugin_basename( __FILE__ )){
		$wpcb_links = '<a href="'.get_admin_url().'options-general.php?page=wpcb/wpcb.php">'.__('Settings').'</a>';
		array_unshift( $links, $wpcb_links );
	}
	return $links;
}

add_shortcode( 'wpcb', 'shortcode_wpcb_handler' );
function shortcode_wpcb_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log, $wpsc_cart;
	$sessionid=$_GET['sessionid'];
	$options = get_option('wpcb_options');
	$purch_log_email=get_option('purch_log_email');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	if ($_GET['action']=='CB'){
		// cf. Dictionnaire des Données Atos :
		if ($options['demo']){
			$merchant_id="082584341411111";
			$pathfile=__ServerRoot__."/cgi-bin/demo/pathfile";
			$path_bin_request =__ServerRoot__."/cgi-bin/demo/request";
		}
		else{
			$merchant_id=$options['merchant_id'];	
			$pathfile=$options['pathfile'];
			$path_bin_request=$options['path_bin_request'];
		}
		$parm="merchant_id=". $merchant_id;
		$parm="$parm merchant_country=".$options['merchant_country'];
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount=number_format($purchase_log->totalprice,2)*100;
		$parm="$parm amount=".str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm currency_code=".$options['currency_code'];
		$parm="$parm pathfile=". $pathfile;
		$parm="$parm normal_return_url=".$options['normal_return_url'];
		$parm="$parm cancel_return_url=".$options['cancel_return_url'];
		$parm="$parm automatic_response_url=".site_url('automatic_response.php');
		$parm="$parm language=".$options['language'];
		$parm="$parm payment_means=".$options['payment_means'];
		$parm="$parm header_flag=".$options['header_flag'];
		$parm="$parm order_id=$sessionid";
		$parm="$parm logo_id2=".$options['logo_id2'];
		$parm="$parm advert=".$options['advert'];
		if (WP_DEBUG){
			//Va afficher sur la page ou se trouve le shortcode les parametres.
			$parm_pretty=str_replace(' ','<br/>',$parm);echo '<p>You see this because you are in debug mode :</p><pre>'.$parm_pretty.'</pre><p>End of debug mode</p>';
		}
		$result=exec("$path_bin_request $parm");
		$tableau=explode ("!","$result");
		$code=$tableau[1];
		$error=$tableau[2];
		if (($code=="") && ($error=="")){
			$message="<p>".__('Error calling the atos api : exec request not found','wpcb')."  $path_bin_request</p>";
			$message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";
		}
		elseif ($code != 0){
			$message="<p>".__('Atos API error : ','wpcb')." $error</p>";
			$message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";
		}
		else{
			// Affiche le formulaire avec le choix des cartes bancaires :
			$message = $tableau[3];
		}
		// End of atos
	}
	elseif ($_GET['action']=='normal_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='cancel_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	else{
		//Appel direct à cette page
		$message='<p>'.__('Direct call to this page not allowed','wpcb').'</p>';
		// Add here some code if you want to test some php for wpec :
		$wpsc_cart->empty_cart();
	}
	return $message;
} // Fin de la fonction d'affichage du shortcode


// A venir, ajout de vos clients dans mailchimp pour leur envoyer des emails ensuite.

?>