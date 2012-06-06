<?php
/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://wpcb.fr
Description: Credit Card Payement Gateway for ATOS SIPS (Mercanet,...) (WP e-Commerce is required)
Version: 1.1.9
Author: 6WWW
Author URI: http://6www.net
*/


// Actions lors de la desactivation du plugin :
register_deactivation_hook( __FILE__, 'wpcb_deactivate' );
function wpcb_deactivate(){
	// On deactivate, remove files :
	unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php');
	unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php');
	unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-content/plugins/wp-e-commerce/wpsc-merchants/cheque.merchant.php');
	unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-content/plugins/wp-e-commerce/wpsc-merchants/virement.merchant.php');
	unlink(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-content/plugins/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php');
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
	wpcb_deactivate(); // Do the delete file
}

register_activation_hook(__FILE__, 'wpcb_activate');
function wpcb_activate() {
	$options = get_option('wpcb_options');
	$sourceFile = dirname(__FILE__). '/automatic_response.php';
	$destinationFile = dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php';			
	copy($sourceFile, $destinationFile);
	$sourceFile = dirname(__FILE__). '/wpcb.merchant.php';
	$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
	copy($sourceFile, $destinationFile);
	$sourceFile = dirname(__FILE__). '/cheque.merchant.php';
	$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/cheque.merchant.php';
	copy($sourceFile, $destinationFile);
	$sourceFile = dirname(__FILE__). '/virement.merchant.php';
	$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/virement.merchant.php';
	copy($sourceFile, $destinationFile);
	$sourceFile = dirname(__FILE__). '/simplepaypal.merchant.php';
	$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php';
	copy($sourceFile, $destinationFile);
    if(!is_array($options)) {
		delete_option('wpcb_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$options = array("merchant_id" => "082584341411111","pathfile" => dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile",
					"path_bin_request" => dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request",
					"path_bin_response" => dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/response" ,
					"merchant_country" => "fr","currency_code" => "978","automatic_response_url"=>site_url()."/automatic_response.php",
					"normal_return_url" => site_url(),"cancel_return_url" => site_url(),
					"language" => "fr","payment_means" => "CB,2,VISA,2,MASTERCARD,2",
					"header_flag" => "no","logfile" => "/homez.136/littlebii/cgi-bin/demo/log.txt",
					"advert" => "advert.jpg","logo_id" => "logo_id.jpg","logo_id2" => "logo_id2.jpg",
					"wpec_gateway_image" => site_url()."/wp-content/plugins/wpcb/logo/LogoMercanetBnpParibas.gif",
					"wpec_display_name" => "Cartes bancaires (Visa, Master Card,...)",
					"test"=>"0","demo"=>"0","version"=>$plugin_data['Version'],
					"emailapiKey"=>"salut@yop.com","apiKey"=>"***",
					"googleemail"=>"salut@gmail.com","googlepassword"=>"***","spreadsheetKey"=>"jLJDjj",
					"textarea_cheque" => "Merci de libéller vos chèque à l'ordre de Thomas et de les faire parvenir vos chèque à l'adresse postale : Lyon, France.",
					"textarea_virement" => "Merci d'envoyer vos virement à ce RIB 45461 24161654 (téléchargeable également à l'adresse : http://monsite.com/rib",
					"business" => $purch_log_email,
						"return" => site_url(),
						"cancel_return" =>site_url(),
						"wpec_gateway_image_paypal" => site_url()."/wp-content/plugins/wp-e-commerce/images/paypal.gif",
						"wpec_display_name_paypal" => "Paypal",
						"notify_url" => site_url().'/wp-content/plugins/wp-e-commerce-paypal/ipn.php',
						"version"=>$plugin_data['Version'],"spreadsheetKeyPaypal"=>"LJhhjdl",
						"sandbox"=>"0");
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
		$destinationFile = dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php';
		if (
		(!file_exists($destinationFile)) || 
		( (isset($_GET['action'])) && ($_GET['action']=='copyautomaticresponse')) 
			){
				copy($sourceFile, $destinationFile);
				echo '<li>automatic_response.php has just been copied</li>';
		}
		if(!file_exists($destinationFile)){
			$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse'));
			echo '<li><span style="color:red;">Copier le fichier automatic_response.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		$sourceFile = dirname(__FILE__).'/wpcb.merchant.php';
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copywpcbmerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/wpcb.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		// Copy chèques :
		$sourceFile = dirname(__FILE__).'/cheque.merchant.php';
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/cheque.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copychequemerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copychequemerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/cheque.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		// Fin copy chèque
		// Copy Virement :
		$sourceFile = dirname(__FILE__).'/virement.merchant.php';
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/virement.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copyvirementmerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copyvirementmerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/virement.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		// Fin copy Virement
		// Copy Paypal :
		$sourceFile = dirname(__FILE__).'/simplepaypal.merchant.php';
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copysimplepaypalmerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copysimplepaypalmerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/simplepaypal.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		// Fin copy Paypal
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
			echo '<li><span style="color:red">Optionel : Vous pouvez débloquer l\'assistance et des <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">fonctions supplémentaires</a> en <a href="http://wpcb.fr/api-key/" target="_blank">achetant une clé API</a></span> C\'est pas cher et ça m\'aide à améliorer mes plugins.</li>';
		}
		// END OF API
		?>
		
		<?php if (WP_ZEND_FRAMEWORK){
			echo '<li><span style="color:green">Zend is installed -> Ok !</span></li>';
			$GoogleConnection=true;
			$SpreadSheetConnection=true;
		try {$client = Zend_Gdata_ClientLogin::getHttpClient($options['googleemail'],$options['googlepassword']);}
		catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
		if ($GoogleConnection){
			echo '<li><span style="color:green">Your google connection is living-> Ok!</span></li>';
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
				echo '<li><span style="color:green">Your Spreadsheet can be read -> Ok!</span></li>';
			}
			else{
				echo '<li><span style="color:red">Your Spreadsheet is not reachable</span></li>';
			}
			
		}
		else {
			echo '<li><span style="color:red">Your google connection is not ok, check email and pass below</span></li>';
		}
		// Todo : catch error if spreadsheetKey is wrong
		}
		else{
		echo '<li><span style="color:red">Install Zend first : http://h6e.net/wiki/wordpress/plugins/zend-framework and buy an api key to have acces to <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">new features</a></span></li>';	
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
				<th scope="row">normal_return_url (là où les gens sont redirigés lorsqu'ils cliquent sur retour à la boutique. Dev : sessionid est ajouté en GET)</th>
				<td><input type="text" size="57" name="wpcb_options[normal_return_url]" value="<?php echo $options['normal_return_url']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">cancel_return_url (là ou les gens sont redirigés s'ils annulent leur paiement Dev : sessionid est ajouté en GET)</th>
				<td><input type="text" size="57" name="wpcb_options[cancel_return_url]" value="<?php echo $options['cancel_return_url']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">automatic_response_url (Mettre à jour puis vérifier ensuite que le lien renvoi une page blanche <a href="<?php echo $options['automatic_response_url']?>" target="_blank">en cliquant ici)</a></th>
				<td><input type="text" size="57" name="wpcb_options[automatic_response_url]" value="<?php echo $options['automatic_response_url']; ?>" /></td>
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
				<tr>
				<th scope="row">** Paiement par chèque **</th>
				</tr>
				<tr>
				<th scope="row">Texte à afficher sur la page pour les gens qui choisisse le paiement par chèque</th>
				<td>
				<textarea name="wpcb_options[textarea_cheque]" rows="7" cols="50" type='textarea'><?php echo $options['textarea_cheque']; ?></textarea><br /><span style="color:#666666;margin-left:2px;">Texte à afficher sur la page pour les gens qui choisisse le paiement par chèque</span>
				</td>
				</tr>
				<tr>
				<th scope="row">** Paiement par Virement Bancaire**</th>
				</tr>
				<tr>
				<th scope="row">Texte à afficher sur la page pour les gens qui choisisse le paiement par virement</th>
				<td>
				<textarea name="wpcb_options[textarea_virement]" rows="7" cols="50" type='textarea'><?php echo $options['textarea_virement']; ?></textarea><br /><span style="color:#666666;margin-left:2px;">Texte à afficher sur la page pour les gens qui choisisse le paiement par virement</span>
				</td>
				</tr>
				<!-- Checkbox Buttons -->
				<tr valign="top">
				<th scope="row">Pour les developpeur</th>
				<td>
				<label><input name="wpcb_options[test]" type="checkbox" value="1" <?php if (isset($options['test'])) { checked('1', $options['test']); } ?> /> Mode test</em></label><br />
<label><input name="wpcb_options[demo]" type="checkbox" value="1" <?php if (isset($options['demo'])) { checked('1', $options['demo']); } ?> /> Mode demo</em></label><br />
				</td>
				</tr>
				<tr>
				<th scope="row">** Paiement par Paypal**</th>
				</tr>
				<tr>
				<th scope="row">Business (adresse email paypal)</th>
				<td><input type="text" size="57" name="wpcb_options[business]" value="<?php echo $options['business']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">Return</th>
				<td><input type="text" size="57" name="wpcb_options[return]" value="<?php echo $options['return']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">cancel_return</th>
				<td><input type="text" size="57" name="wpcb_options[cancel_return]" value="<?php echo $options['cancel_return']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">wpec_gateway_image_paypal</th>
				<td><input type="text" size="57" name="wpcb_options[wpec_gateway_image_paypal]" value="<?php echo $options['wpec_gateway_image_paypal']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">wpec_display_name_paypal</th>
				<td><input type="text" size="57" name="wpcb_options[wpec_display_name_paypal]" value="<?php echo $options['wpec_display_name_paypal']; ?>" /></td>
				</tr>
				<tr>
				<th scope="row">notify_url</th>
				<td><input type="text" size="57" name="wpcb_options[notify_url]" value="<?php echo $options['notify_url']; ?>" /></td>
				</tr>
				<tr>
				<tr>
				<th scope="row">Clé de la feuille de calcul Google Drive (Optionel : pour l'ajout des ventes dans Google Drive)</th>
				<td><input type="text" size="57" name="wpcb_options[spreadsheetKeyPaypal]" value="<?php echo $options['spreadsheetKeyPaypal']; ?>" /></td>
				</tr>
				<!-- Checkbox Buttons -->
				<tr valign="top">
				<th scope="row">Pour les developpeur</th>
				<td>
				<label><input name="simplepaypal_options[sandbox]" type="checkbox" value="1" <?php if (isset($options['sandbox'])) { checked('1', $options['sandbox']); } ?> /> Sandbox</em></label><br />
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
		echo '<p>Développeur :</p>';
		echo '<ul>';
		echo '<li><p>Plugin version : '.$options['version'].'</li>';
		echo '<li><p>Dossier Plugin : '.dirname(__FILE__).'</p></li>';
		echo '<li><p>Racine wordpress : '.dirname(dirname(dirname(dirname(__FILE__)))).'</p></li>';
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse'));
		$destinationFile = dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php';
		echo '<li>Copier automatic_response.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		echo '<li>Copier wpcb.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// Chèque :
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copychequemerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/cheque.merchant.php';
		echo '<li>Copier cheque.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of Chèques
		// Virement :
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copyvirementmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/virement.merchant.php';
		echo '<li>Copier virement.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of Virement
		// Paypal :
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=copysimplepaypalmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php';
		echo '<li>Copier simplepaypal.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of Paypal
		$nonce_url=wp_nonce_url(admin_url( 'options-general.php?page=wpcb/wpcb.php&action=sandbox'));
		echo '<li>Tester votre fichier automatic_response.php <a href="'.$nonce_url.'">en cliquant ici</a> (Cela va mettre à jour log.txt et google drive)</li>';
		echo '<li>'.$options['automatic_response_url'].'</li>';
		if ((isset($_GET['action'])) && ($_GET['action']=='sandbox')){
			$post_data['DATA']='Dummy'; //Needed
			$post_data['sandbox']='NULL!1!2!'.$options['merchant_id'].'!fr!100!8755900!CB!10-02-2012!11:50!10-02-2012!004!certif!22!978!4974!545!1!22!Comp!CompInfo!return!caddie!Merci!fr!fr!001!8787084074894!my@email.com!1.10.21.192!30!direct!data';
			
			$response=wp_remote_post($options['automatic_response_url'],array('body' =>$post_data));
			print_r($response);
		}
		?>
			<li><a href="http://www.seoh.fr" target="_blank">Référencer votre site e-commerce avec l'agence SEOh</a></li>
			<li><a href="http://profiles.wordpress.org/6www">Les autres plugins de 6WWW</a></li>
			</ul>
		</p>
	</div>
	<?php	
	
	
} // Fin de la fonction des réglages

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpcb_validate_options($input) {
	$exclude_from_filter_nohtml_kses=array('textarea_cheque','textarea_virement');
	foreach ($input as $key=>$value){
		if (!in_array($key,$exclude_from_filter_nohtml_kses)){
			$input[$key]=wp_filter_nohtml_kses($input[$key]);
		}
	}
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
		if ((array_key_exists('demo', $options)) && ($options['demo'])){
			$merchant_id="082584341411111";
			$pathfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile";
			$path_bin_request =dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request";
		}
		else{
			$merchant_id=$options['merchant_id'];	
			$pathfile=$options['pathfile'];
			$path_bin_request=$options['path_bin_request'];
		}
		$parm="merchant_id=". $merchant_id;
		$parm="$parm merchant_country=".$options['merchant_country'];
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount= ($purchase_log->totalprice)*100;
		$amount=str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm amount=".$amount;
		$parm="$parm currency_code=".$options['currency_code'];
		$parm="$parm pathfile=". $pathfile;
		$parm="$parm normal_return_url=".$options['normal_return_url']."?sessionid=".$sessionid;
		$parm="$parm cancel_return_url=".$options['cancel_return_url']."?sessionid=".$sessionid;
		$parm="$parm automatic_response_url=".$options['automatic_response_url'];
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
	elseif ($_GET['action']=='paypal'){
	$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		if ($options['sandbox']){
			$message='<form action="https://sandbox.paypal.com/cgi-bin/webscr" method="post">';
		}
		else{
			$message='<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		}
		$message.='<input type="hidden" name="cmd" value="_xclick">';
		$message.='<input type="hidden" name="business" value="'.$options['business'].'">';
		$message.='<input type="hidden" name="lc" value="FR">';
		$message.='<input type="hidden" name="item_name" value="Commande #'.$purchase_log->id.'">';
		$message.='<input type="hidden" name="item_number" value="'.$sessionid.'">';
		$amount=number_format($purchase_log->totalprice,2);
		$message.='<input type="hidden" name="amount" value="'.$amount.'">';
		$message.='<input type="hidden" name="no_note" value="1">';
		$message.='<input type="hidden" name="return" value="'.$options['return'].'">';
		$message.='<input type="hidden" name="cancel_return" value="'.$options['cancel_return'].'">';
		$message.='<input type="hidden" name="notify_url" value="'.$options['notify_url'].'">';
		$message.='<input type="hidden" name="no_shipping" value="1"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="button_subtype" value="services"><input type="hidden" name="no_note" value="0"><input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHostedGuest"><input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !"><img alt="" border="0" src="https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif" width="1" height="1"></form>';
	}
	elseif ($_GET['action']=='normal_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='cancel_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	if ($_GET['action']=='sandbox'){
	
	
	
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