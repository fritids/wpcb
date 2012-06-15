<?php

/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://wpcb.fr
Description: Credit Card Payement Gateway for ATOS SIPS (Mercanet,...) (WP e-Commerce is required)
Version: 1.1.9
Author: 6WWW
Author URI: http://6www.net
*/

wp_deregister_script('admin-bar');
wp_deregister_style('admin-bar');
remove_action('wp_footer','wp_admin_bar_render',1000);

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
	delete_option('wpcb_general_options');
	delete_option('wpcb_cb_options');
	delete_option('wpcb_cheque_options');
	delete_option('wpcb_virement_options');
	delete_option('wpcb_paypal_options');
	delete_option('wpcb_systempaycyberplus_options');
	delete_option('wpcb_mailchimp_options');
	delete_option('wpcb_dev_options');
	wpcb_deactivate(); // Do the delete file
}


register_activation_hook(__FILE__, 'wpcb_activate');
function wpcb_activate() {
	copy(dirname(__FILE__). '/automatic_response.php',dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php');
	copy(dirname(__FILE__). '/wpcb.merchant.php', dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/wpcb.merchant.php');
	copy(dirname(__FILE__). '/cheque.merchant.php',dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/cheque.merchant.php');
	copy(dirname(__FILE__). '/virement.merchant.php', dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/virement.merchant.php');
	copy(dirname(__FILE__). '/simplepaypal.merchant.php', dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php');
	copy(dirname(__FILE__). '/systempaycyberplus.merchant.php', dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/systempaycyberplus.merchant.php');
}





function wpcb_plugin_menu() {  
  
    add_plugins_page(  
        'WPCB',           // The title to be displayed in the browser window for this page.  
        'WPCB',           // The text to be displayed for this menu item  
        'administrator',            // Which type of users can see this menu item  
        'wpcb_plugin_options',   // The unique ID - that is, the slug - for this menu item  
        'wpcb_plugin_display'    // The name of the function to call when rendering the page for this menu  
    );  
  
} // end wpcb_example_plugin_menu  
add_action('admin_menu', 'wpcb_plugin_menu'); 

function wpcb_plugin_display() {  
?>  
    <!-- Create a header in the default WordPress 'wrap' container -->  
    <div class="wrap">  
  
        <!-- Add the icon to the page -->  
        <div id="icon-themes" class="icon32"></div>  
        <h2>WPCB Plugin Options</h2>  
  
        <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->  
        <?php settings_errors(); ?>  
  
  		<?php  
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_options';
        ?>  
  
        <h2 class="nav-tab-wrapper">  
            <a href="?page=wpcb_plugin_options&tab=general_options" class="nav-tab <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>">Options générales</a>  
            <a href="?page=wpcb_plugin_options&tab=cb_options" class="nav-tab <?php echo $active_tab == 'cb_options' ? 'nav-tab-active' : ''; ?>">Carte Bancaire</a>
            <a href="?page=wpcb_plugin_options&tab=cheque_options" class="nav-tab <?php echo $active_tab == 'cheque_options' ? 'nav-tab-active' : ''; ?>">Chèque</a>
            <a href="?page=wpcb_plugin_options&tab=virement_options" class="nav-tab <?php echo $active_tab == 'virement_options' ? 'nav-tab-active' : ''; ?>">Virement</a>
            <a href="?page=wpcb_plugin_options&tab=paypal_options" class="nav-tab <?php echo $active_tab == 'paypal_options' ? 'nav-tab-active' : ''; ?>">Paypal</a>
			<a href="?page=wpcb_plugin_options&tab=systempaycyberplus_options" class="nav-tab <?php echo $active_tab == 'systempaycyberplus' ? 'nav-tab-active' : ''; ?>">Systempay Cyberplus</a>
			<a href="?page=wpcb_plugin_options&tab=mailchimp_options" class="nav-tab <?php echo $active_tab == 'mailchimp_options' ? 'nav-tab-active' : ''; ?>">Mailchimp</a>
            <a href="?page=wpcb_plugin_options&tab=dev_options" class="nav-tab <?php echo $active_tab == 'dev_options' ? 'nav-tab-active' : ''; ?>">Dev</a>
        </h2>  
  

  		<?php $wpcb_general_options = get_option( 'wpcb_general_options' ); ?>  
        <?php $wpcb_cb_options = get_option ( 'wpcb_cb_options' ); ?>  
        <?php
        //print_r($wpcb_general_options);
        //print_r($wpcb_cb_options);
  		?>
        <!-- Create the form that will be used to render our options -->  
        <form method="post" action="options.php"> 
        <?php
    if( $active_tab == 'general_options' ) {settings_fields( 'wpcb_general_options' );do_settings_sections( 'wpcb_general_options' );}
   elseif( $active_tab == 'cb_options' ) {settings_fields( 'wpcb_cb_options' );do_settings_sections( 'wpcb_cb_options' );}
	elseif( $active_tab == 'cheque_options' ) {  
            settings_fields( 'wpcb_cheque_options' );  
            do_settings_sections( 'wpcb_cheque_options' );  
        }
     elseif( $active_tab == 'virement_options' ) {  
            settings_fields( 'wpcb_virement_options' );  
            do_settings_sections( 'wpcb_virement_options' );  
        }
        elseif( $active_tab == 'paypal_options' ) {  
            settings_fields( 'wpcb_paypal_options' );  
            do_settings_sections( 'wpcb_paypal_options' );  
        }
		elseif( $active_tab == 'systempaycyberplus_options' ) {  
            settings_fields( 'wpcb_systempaycyberplus_options' );  
            do_settings_sections( 'wpcb_systempaycyberplus_options' );  
        }
		elseif( $active_tab == 'mailchimp_options' ) {  
            settings_fields( 'wpcb_mailchimp_options' );  
            do_settings_sections( 'wpcb_mailchimp_options' );  
        }
        elseif( $active_tab == 'dev_options' ) {  
            settings_fields( 'wpcb_dev_options' );  
            do_settings_sections( 'wpcb_dev_options' );  
        }
                
        ?>
    <?php submit_button(); ?>  
	</form> 
  
    </div><!-- /.wrap -->  
<?php  
} // end wpcb_display  

/* ------------------------------------------------------------------------ * 
 * Setting Registration 
 * ------------------------------------------------------------------------ */  
 
function wpcb_initialize_general_options() { 
 if( false == get_option( 'wpcb_general_options' ) ) {  add_option( 'wpcb_general_options' );  }
     add_settings_section('general_settings_section','General Options','wpcb_general_options_callback','wpcb_general_options');
 
    // Next, we'll introduce the fields for toggling the visibility of content elements.  
  add_settings_field('apiKey','Clé API','wpcb_apiKey_callback','wpcb_general_options','general_settings_section',array('description' ));  
  add_settings_field('emailapiKey','Email associé à la Clé API','wpcb_emailapiKey_callback','wpcb_general_options','general_settings_section',array('description' ));  
  add_settings_field('googleemail','Email Google Drive (ou Google App)','wpcb_googleemail_callback','wpcb_general_options','general_settings_section',array('description' ));  
  add_settings_field('googlepassword','Password associé Gmail ou Google Apps','wpcb_googlepassword_callback','wpcb_general_options','general_settings_section',array('description' ));  
  add_settings_field('spreadsheetKey','spreadsheetKey','wpcb_spreadsheetKey_callback','wpcb_general_options','general_settings_section',array('description' ));  
  register_setting('wpcb_general_options','wpcb_general_options'); 
} 
add_action('admin_init', 'wpcb_initialize_general_options');  
  
/* ------------------------------------------------------------------------ * 
 * Section Callbacks 
 * ------------------------------------------------------------------------ */   
  
function wpcb_general_options_callback() {
	global  $wpdb;
	$wpcb_general_options = get_option( 'wpcb_general_options' );
    $wpcb_cb_options = get_option ( 'wpcb_cb_options' );
        
    echo '<ol>';
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
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copyautomaticresponse'));
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
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copywpcbmerchant'));
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
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copychequemerchant'));
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
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copyvirementmerchant'));
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
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copysimplepaypalmerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/simplepaypal.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien au bon endroit -> OK!</span></li>';
		}
		// Fin copy Paypal
		// Copy systempaycyberplus :
		$sourceFile = dirname(__FILE__).'/systempaycyberplus.merchant.php';
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/systempaycyberplus.merchant.php';
		if (
		(!file_exists($destinationFile)) ||
		((isset($_GET['action'])) && ($_GET['action']=='copysystempaycyberplusmerchant'))
		){
			copy($sourceFile, $destinationFile);
		}
		if(!file_exists($destinationFile)) {
			$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=general_options&action=copysystempaycyberplusmerchant'));
			echo '<li><span style="color:red;">Copier le fichier '.dirname(__FILE__).'/systempaycyberplus.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></span></li>';
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
		$post_data['apiKey']=$wpcb_general_options['apiKey'];
		$post_data['emailapiKey']=$wpcb_general_options['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			echo '<li><span style="color:green">Votre clé API est valide -> OK!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Optionel : Vous pouvez débloquer l\'assistance et des <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">fonctions supplémentaires</a> en <a href="http://wpcb.fr/api-key/" target="_blank">achetant une clé API</a></span> C\'est pas cher et ça m\'aide à améliorer mes plugins.</li>';
		}
		// END OF API
		if (WP_ZEND_FRAMEWORK){
			echo '<li><span style="color:green">Zend is installed -> Ok !</span></li>';
			$GoogleConnection=true;
			$SpreadSheetConnection=true;
		try {$client = Zend_Gdata_ClientLogin::getHttpClient($wpcb_general_options['googleemail'],$wpcb_general_options['googlepassword']);}
		catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
		if ($GoogleConnection){
			echo '<li><span style="color:green">Your google connection is living-> Ok!</span></li>';
			if ($wpcb_general_options['spreadsheetKey']){
			// Test 
			$service=Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
			$client=Zend_Gdata_ClientLogin::getHttpClient($wpcb_general_options['googleemail'],$wpcb_general_options['googlepassword'], $service);
			// On va chercher le numéro de la feuille :
			$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery(); // todo pour pas de client ici ?
			$query_worksheet->setSpreadsheetKey($wpcb_general_options['spreadsheetKey']);
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
			else{
				echo '<li><span style="color:red">Entrer un numéro de feuille google drive !</span></li>';
			}
		}
		else {
			echo '<li><span style="color:red">Your google connection is not ok, check email and pass below</span></li>';
		}
		// Todo : catch error if spreadsheetKey is wrong
		}
		else{
		echo '<li><span style="color:red">Install Zend first : http://h6e.net/wiki/wordpress/plugins/zend-framework and buy an api key to have acces to <a href="http://wordpress.org/extend/plugins/wpcb/" target="_blank">new features</a></span></li>';	
		}
		
		echo "<li>Remplissez les autres onglets d'options.</li>";
		echo "</ol>";
} // end wpcb_general_options_callback  
  
/* ------------------------------------------------------------------------ * 
 * Field Callbacks 
 * ------------------------------------------------------------------------ */   
  
function wpcb_apiKey_callback() {  
    $options = get_option('wpcb_general_options');  
    $val='mykey'; 
    if(isset($options['apiKey'])){$val = $options['apiKey'];}
    echo '<input type="text"  size="75"id="apiKey" name="wpcb_general_options[apiKey]" value="' . $val . '" />';  
}
function wpcb_emailapiKey_callback() {  
    $options = get_option('wpcb_general_options');  
    $val='your@email.com'; 
    if(isset($options['emailapiKey'])){$val = $options['emailapiKey'];}
    echo '<input type="text"  size="75"id="emailapiKey" name="wpcb_general_options[emailapiKey]" value="' . $val . '" />';  
}
function wpcb_googleemail_callback() {  
    $options = get_option('wpcb_general_options');  
    $val='your@gmail.com'; 
    if(isset($options['googleemail'])){$val = $options['googleemail'];}
    echo '<input type="text"  size="75"id="googleemail" name="wpcb_general_options[googleemail]" value="' . $val . '" />';  
}
function wpcb_googlepassword_callback() {  
    $options = get_option('wpcb_general_options');  
    $val='your@email.com'; 
    if(isset($options['googlepassword'])){$val = $options['googlepassword'];}
    echo '<input type="password" id="googlepassword" name="wpcb_general_options[googlepassword]" value="' . $val . '" />';  
}
function wpcb_spreadsheetKey_callback(){  
    $options = get_option( 'wpcb_general_options');  
    $val = '0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc'; 
    if(isset($options['spreadsheetKey'])){$val = $options['spreadsheetKey'];}
        echo '<input type="text"  size="75"id="spreadsheetKey" name="wpcb_general_options[spreadsheetKey]" value="' . $val . '" />';
}  
  

/** 
* CB options
*/  
function wpcb_intialize_cb_options() {  
    if(false == get_option( 'wpcb_cb_options' )){add_option( 'wpcb_cb_options' );}
	add_settings_section('cb_settings_section','CB Options','wpcb_cb_options_callback','wpcb_cb_options');
	// Add the fields :
	add_settings_field('merchant_id','Merchant ID','wpcb_merchant_id_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('pathfile','Pathfile','wpcb_pathfile_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('pathfile','Pathfile','wpcb_pathfile_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('path_bin_request','path_bin_request','wpcb_path_bin_request_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('path_bin_response','path_bin_response','wpcb_path_bin_response_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('merchant_country','merchant_country (fr)','wpcb_merchant_country_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('currency_code','currency_code (978=€)','wpcb_currency_code_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('normal_return_url','normal_return_url','wpcb_normal_return_url_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('cancel_return_url','cancel_return_url','wpcb_cancel_return_url_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('automatic_response_url','automatic_response_url','wpcb_automatic_response_url_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('language','language (fr)','wpcb_language_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('payment_means','payment_means','wpcb_payment_means_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('header_flag','header_flag (no)','wpcb_header_flag_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('advert','advert','wpcb_advert_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('logo_id','logo_id','wpcb_logo_id_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('logo_id2','logo_id2','wpcb_logo_id2_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('wpec_cb_display_name','wpec_cb_display_name','wpcb_wpec_cb_display_name_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('wpec_cb_gateway_image','wpec_cb_gateway_image','wpcb_wpec_cb_gateway_image_callback','wpcb_cb_options','cb_settings_section');
	add_settings_field('logfile','logfile','wpcb_logfile_callback','wpcb_cb_options','cb_settings_section');
	

	register_setting('wpcb_cb_options','wpcb_cb_options','wpcb_sanitize_cb_options');

	function wpcb_sanitize_cb_options( $input ) {  
  
    // Define the array for the updated options  
    //$output = array();  
  $output=$input;
    // Loop through each of the options sanitizing the data  
    //foreach( $input as $key => $val ) {  
  
      //  if( isset ( $input[$key] ) ) {  
       //     $output[$key] = esc_url_raw( strip_tags( stripslashes( $input[$key] ) ) );  
       // } // end if   
  
//    } // end foreach  
  
    // Return the new collection  
    return apply_filters( 'wpcb_sanitize_cb_options', $output, $input );  
  
	} // end wpcb_sanitize_cb_options

} // end wpcb_intialize_cb_options  
add_action( 'admin_init', 'wpcb_intialize_cb_options' );  



function wpcb_cb_options_callback() {  
    echo '<p>Réglage des options Carte bancaire Atos</p>';  
} // end wpcb_general_options_callback  

function wpcb_merchant_id_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $merchant_id = '082584341411111'; 
    if(isset($options['merchant_id'])){$merchant_id = $options['merchant_id'];}
    echo '<input type="text"  size="75"id="merchant_id" name="wpcb_cb_options[merchant_id]" value="' . $options['merchant_id'] . '" />';  
}
function wpcb_pathfile_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile"; 
    if(isset($options['pathfile'])){$val = $options['pathfile'];}
    echo '<input type="text"  size="75"id="pathfile" name="wpcb_cb_options[pathfile]" value="' . $val . '" />';  
}
function wpcb_path_bin_request_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request"; 
    if(isset($options['path_bin_request'])){$val = $options['path_bin_request'];}
    echo '<input type="text"  size="75"id="path_bin_request" name="wpcb_cb_options[path_bin_request]" value="' . $val . '" />';  
}
function wpcb_path_bin_response_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/response"; 
    if(isset($options['path_bin_response'])){$val = $options['path_bin_response'];}
    echo '<input type="text"  size="75"id="path_bin_response" name="wpcb_cb_options[path_bin_response]" value="' . $val . '" />';  
}
function wpcb_merchant_country_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'fr'; 
    if(isset($options['merchant_country'])){$val = $options['merchant_country'];}
    echo '<input type="text"  size="75"id="merchant_country" name="wpcb_cb_options[merchant_country]" value="' . $val . '" />';  
}
function wpcb_currency_code_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = '978'; 
    if(isset($options['currency_code'])){$val = $options['currency_code'];}
    echo '<input type="text"  size="75"id="currency_code" name="wpcb_cb_options[currency_code]" value="' . $val . '" />';  
}
function wpcb_normal_return_url_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = site_url(); 
    if(isset($options['normal_return_url'])){$val = $options['normal_return_url'];}
    echo '<input type="text"  size="75"id="normal_return_url" name="wpcb_cb_options[normal_return_url]" value="' . $val . '" />';  
}
function wpcb_cancel_return_url_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = site_url(); 
    if(isset($options['cancel_return_url'])){$val = $options['cancel_return_url'];}
    echo '<input type="text"  size="75"id="cancel_return_url" name="wpcb_cb_options[cancel_return_url]" value="' . $val . '" />';  
}
function wpcb_automatic_response_url_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = site_url()."/automatic_response.php"; 
    if(isset($options['automatic_response_url'])){$val = $options['automatic_response_url'];}
    echo '<input type="text"  size="75"id="automatic_response_url" name="wpcb_cb_options[automatic_response_url]" value="' . $val . '" />';  
}
function wpcb_language_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'fr'; 
    if(isset($options['language'])){$val = $options['language'];}
    echo '<input type="text"  size="75"id="language" name="wpcb_cb_options[language]" value="' . $val . '" />';  
}
function wpcb_payment_means_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'CB,2,VISA,2,MASTERCARD,2'; 
    if(isset($options['payment_means'])){$val = $options['payment_means'];}
    echo '<input type="text"  size="75"id="payment_means" name="wpcb_cb_options[payment_means]" value="' . $val . '" />';  
}
function wpcb_header_flag_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'no'; 
    if(isset($options['header_flag'])){$val = $options['header_flag'];}
    echo '<input type="text"  size="75"id="header_flag" name="wpcb_cb_options[header_flag]" value="' . $val . '" />';  
}
function wpcb_advert_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'advert.jpg'; 
    if(isset($options['advert'])){$val = $options['advert'];}
    echo '<input type="text"  size="75"id="advert" name="wpcb_cb_options[advert]" value="' . $val . '" />';  
}
function wpcb_logo_id_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'logo_id.jpg'; 
    if(isset($options['logo_id'])){$val = $options['logo_id'];}
    echo '<input type="text"  size="75"id="logo_id" name="wpcb_cb_options[logo_id]" value="' . $val . '" />';  
}
function wpcb_logo_id2_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'logo_id2.jpg'; 
    if(isset($options['logo_id2'])){$val = $options['logo_id2'];}
    echo '<input type="text"  size="75"id="logo_id2" name="wpcb_cb_options[logo_id2]" value="' . $val . '" />';  
}
function wpcb_wpec_cb_display_name_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = 'Cartes bancaires'; 
    if(isset($options['wpec_cb_display_name'])){$val = $options['wpec_cb_display_name'];}
    echo '<input type="text"  size="75"id="wpec_cb_display_name" name="wpcb_cb_options[wpec_cb_display_name]" value="' . $val . '" />';  
}
function wpcb_wpec_cb_gateway_image_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = "http://wpcb.fr/dev/wp-content/plugins/wpcb/logo/LogoMercanetBnpParibas.gif"; 
    if(isset($options['wpec_cb_gateway_image'])){$val = $options['wpec_cb_gateway_image'];}
    echo '<input type="text"  size="75"id="wpec_cb_gateway_image" name="wpcb_cb_options[wpec_cb_gateway_image]" value="' . $val . '" />';  
}
function wpcb_logfile_callback() {  
    $options = get_option( 'wpcb_cb_options');  
    $val = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request"; 
    if(isset($options['logfile'])){$val = $options['logfile'];}
    echo '<input type="text"  size="75"id="logfile" name="wpcb_cb_options[logfile]" value="' . $val . '" />';  
}



/** 
* Cheque options
*/  
function wpcb_intialize_cheque_options() {  
    if(false == get_option( 'wpcb_cheque_options' )){add_option( 'wpcb_cheque_options' );}
	add_settings_section('cheque_settings_section','Chèque Options','wpcb_cheque_options_callback','wpcb_cheque_options');
	add_settings_field('displaycheque','Afficher à l\'acheteur','wpcb_displaycheque_callback','wpcb_cheque_options','cheque_settings_section');
	register_setting('wpcb_cheque_options','wpcb_cheque_options','');
}
add_action( 'admin_init', 'wpcb_intialize_cheque_options' );  



function wpcb_cheque_options_callback() {  
    echo '<p>Réglage des options pour le paiement par chèque</p>';  
}

function wpcb_displaycheque_callback() {  
    $options = get_option( 'wpcb_cheque_options');  
    $displaycheque = "Merci de libéller vos chèques à l'ordre de Thomas et de les faire parvenir vos chèque à l'adresse postale : Lyon, France."; 
    if(isset($options['displaycheque'])){$displaycheque = $options['displaycheque'];}
    echo '<textarea type="textarea" id="displaycheque" name="wpcb_cheque_options[displaycheque]" rows="7" cols="50">'.$options['displaycheque'] .'</textarea>';  
}

/** 
* Virement options
*/  
function wpcb_intialize_virement_options() {  
    if(false == get_option( 'wpcb_virement_options' )){add_option( 'wpcb_virement_options' );}
	add_settings_section('virement_settings_section','Virement Options','wpcb_virement_options_callback','wpcb_virement_options');
	add_settings_field('displayvirement','Afficher à l\'acheteur','wpcb_displayvirement_callback','wpcb_virement_options','virement_settings_section');
	register_setting('wpcb_virement_options','wpcb_virement_options','');
} 
add_action( 'admin_init', 'wpcb_intialize_virement_options' );  



function wpcb_virement_options_callback() {  
    echo '<p>Réglage des options pour le paiement par virement bancaire</p>';  
}

function wpcb_displayvirement_callback() {  
    $options = get_option( 'wpcb_virement_options');  
    $displayvirement = "Merci d'envoyer vos virement à ce RIB 45461 24161654 (téléchargeable également à l'adresse : http://monsite.com/rib"; 
    if(isset($options['displayvirement'])){$displayvirement = $options['displayvirement'];}
    echo '<textarea type="textarea" id="displayvirement" name="wpcb_virement_options[displayvirement]" rows="7" cols="50">'.$options['displayvirement'] .'</textarea>';  
}

/** 
* Paypalpaypal options
*/  
function wpcb_intialize_paypal_options() {  
    if(false == get_option( 'wpcb_paypal_options' )){add_option( 'wpcb_paypal_options' );}
	add_settings_section('paypal_settings_section','paypal Options','wpcb_paypal_options_callback','wpcb_paypal_options');
	// Add the fields :
	add_settings_field('business','Business (adresse paypal)','wpcb_business_callback','wpcb_paypal_options','paypal_settings_section');
	add_settings_field('return','Return url','wpcb_return_callback','wpcb_paypal_options','paypal_settings_section');
	add_settings_field('cancel_return','Cancel Return url','wpcb_cancel_return_callback','wpcb_paypal_options','paypal_settings_section');
	add_settings_field('wpec_gateway_image_paypal','Image to be displayed','wpcb_wpec_gateway_image_paypal_callback','wpcb_paypal_options','paypal_settings_section');
	add_settings_field('notify_url','Url de notification auto (ipn)','wpcb_notify_url_callback','wpcb_paypal_options','paypal_settings_section');
	add_settings_field('sandbox_paypal','Sandbox','wpcb_sandbox_paypal_callback','wpcb_paypal_options','paypal_settings_section');
	// Register the fields :
	register_setting('wpcb_paypal_options','wpcb_paypal_options',''); //sanitize
}
add_action( 'admin_init', 'wpcb_intialize_paypal_options' );  
function wpcb_paypal_options_callback() {  
    echo '<p>Réglage des options pour le paiement par Paypal</p>';  
}

function wpcb_business_callback(){  
    $options = get_option( 'wpcb_paypal_options');  
    $val = 'thomas@6www.net'; 
    if(isset($options['business'])){$val = $options['business'];}
        echo '<input type="text"  size="75"id="business" name="wpcb_paypal_options[business]" value="' . $val . '" />';
}
function wpcb_return_callback(){  
    $options = get_option( 'wpcb_paypal_options');  
    $val = site_url(); 
    if(isset($options['return'])){$val = $options['return'];}
        echo '<input type="text"  size="75"id="return" name="wpcb_paypal_options[return]" value="' . $val . '" />';
}
function wpcb_cancel_return_callback(){  
    $options = get_option( 'wpcb_paypal_options');  
    $val = site_url(); 
    if(isset($options['cancel_return'])){$val = $options['cancel_return'];}
        echo '<input type="text"  size="75"id="cancel_return" name="wpcb_paypal_options[cancel_return]" value="' . $val . '" />';
}
function wpcb_wpec_gateway_image_paypal_callback(){  
    $options = get_option( 'wpcb_paypal_options');  
    $val = "http://wpcb.fr/dev/wp-content/plugins/wpcb/logo/paypal.jpg"; 
    if(isset($options['wpec_gateway_image_paypal'])){$val = $options['wpec_gateway_image_paypal'];}
        echo '<input type="text" size="75" id="wpec_gateway_image_paypal" name="wpcb_paypal_options[wpec_gateway_image_paypal]" value="' . $val . '" />';
}

function wpcb_notify_url_callback(){  
    $options = get_option( 'wpcb_paypal_options');  
    $val =site_url().'/wp-content/plugins/wpcb/ipn.php'; 
    if(isset($options['notify_url'])){$val = $options['notify_url'];}
        echo '<input type="text"  size="75"id="notify_url" name="wpcb_paypal_options[notify_url]" value="' . $val . '" />';
}

function wpcb_sandbox_paypal_callback($args){  
    $options = get_option( 'wpcb_paypal_options');  
	$html = '<input type="checkbox" id="sandbox_paypal" name="wpcb_paypal_options[sandbox_paypal]" value="1" ' . checked(1, $options['sandbox_paypal'], false) . '/>';  
    $html .= '<label for="sandbox_paypal"> '  . $args[0] . '</label>';   
    echo $html;
}


/** 
* Systempay Cyberplus options
*/  
function wpcb_intialize_systempaycyberplus_options() {  
    if(false == get_option( 'wpcb_systempaycyberplus_options' )){add_option( 'wpcb_systempaycyberplus_options' );}
	add_settings_section('systempaycyberplus_settings_section','Systempay Cyberplus Options','wpcb_systempaycyberplus_options_callback','wpcb_systempaycyberplus_options');
	// Add the fields :
	add_settings_field('identifiant','Identifiant','wpcb_identifiant_callback','wpcb_systempaycyberplus_options','systempaycyberplus_settings_section');
	add_settings_field('certificat','Certificat','wpcb_certificat_callback','wpcb_systempaycyberplus_options','systempaycyberplus_settings_section');
	add_settings_field('wpec_gateway_image_paypal','Image sur la page de choix du paiement','wpcb_wpec_gateway_image_systempaycyberplus_callback','wpcb_systempaycyberplus_options','systempaycyberplus_settings_section');
	// Register the fields :
	register_setting('wpcb_systempaycyberplus_options','wpcb_systempaycyberplus_options',''); //sanitize
}
add_action( 'admin_init', 'wpcb_intialize_systempaycyberplus_options' );  
function wpcb_systempaycyberplus_options_callback() {  
    echo '<p>Réglage des options pour le paiement par Systempay Cyberplus (Banque populaire)</p>';  
}

function wpcb_identifiant_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus_options');  
    $val = '54020139'; 
    if(isset($options['identifiant'])){$val = $options['identifiant'];}
        echo '<input type="text"  size="75"id="identifiant" name="wpcb_systempaycyberplus_options[identifiant]" value="' . $val . '" />';
}
function wpcb_certificat_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus_options');  
    $val = '7639056200685146'; 
    if(isset($options['certificat'])){$val = $options['certificat'];}
        echo '<input type="text"  size="75"id="certificat" name="wpcb_systempaycyberplus_options[certificat]" value="' . $val . '" />';
}

function wpcb_wpec_gateway_image_systempaycyberplus_callback(){  
    $options = get_option( 'wpcb_systempaycyberplus_options');  
    $val = plugins_url()."/wpcb/logo/logo_systempaycyberplus.gif"; 
    if(isset($options['wpec_gateway_image_systempaycyberplus'])){$val = $options['wpec_gateway_image_systempaycyberplus'];}
        echo '<input type="text" size="75" id="wpec_gateway_image_systempaycyberplus" name="wpcb_systempaycyberplus_options[wpec_gateway_image_systempaycyberplus]" value="' . $val . '" />';
}





/** 
* Developper options
*/  
function wpcb_intialize_dev_options() {  
    if(false == get_option( 'wpcb_dev_options' )){add_option( 'wpcb_dev_options' );}
	add_settings_section('dev_settings_section','dev Options','wpcb_dev_options_callback','wpcb_dev_options');
	// Add the fields :
	add_settings_field('version','','wpcb_version_callback','wpcb_dev_options','dev_settings_section');
	add_settings_field('mode_demo','Mode Démo','wpcb_mode_demo_callback','wpcb_dev_options','dev_settings_section');
	add_settings_field('mode_test','Mode Test','wpcb_mode_test_callback','wpcb_dev_options','dev_settings_section');
	register_setting('wpcb_dev_options','wpcb_dev_options','');
} // end wpcb_intialize_cb_options  
add_action( 'admin_init', 'wpcb_intialize_dev_options' );  



function wpcb_dev_options_callback() {
	 $wpcb_general_options = get_option( 'wpcb_general_options' );
     $wpcb_cb_options = get_option ( 'wpcb_cb_options' );
	 $wpcb_dev_options = get_option ( 'wpcb_dev_options' );
        //print_r($wpcb_general_options);
        //print_r($wpcb_cb_options);
    echo '<p>Options pour developper</p>';
    echo '<ul>';
		echo '<li><p>Plugin version : '.$wpcb_dev_options['version'].'</li>';
		echo '<li><p>Dossier Plugin : '.dirname(__FILE__).'</p></li>';
		echo '<li><p>Racine wordpress : '.dirname(dirname(dirname(dirname(__FILE__)))).'</p></li>';
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copyautomaticresponse'));
		$destinationFile = dirname(dirname(dirname(dirname(__FILE__)))).'/automatic_response.php';
		echo '<li>Copier automatic_response.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copywpcbmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		echo '<li>Copier wpcb.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// ChÃ¨que :
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copychequemerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/cheque.merchant.php';
		echo '<li>Copier cheque.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of ChÃ¨ques
		// Virement :
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copyvirementmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/virement.merchant.php';
		echo '<li>Copier virement.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of Virement
		// Paypal :
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copysimplepaypalmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/simplepaypal.merchant.php';
		echo '<li>Copier simplepaypal.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of Paypal
		// systempaycyberplus :
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=copysystempaycyberplusmerchant'));
		$destinationFile = dirname(dirname(__FILE__)).'/wp-e-commerce/wpsc-merchants/systempaycyberplus.merchant.php';
		echo '<li>Copier systempaycyberplus.merchant.php vers '.$destinationFile.' <a href="'.$nonce_url.'">en cliquant ici</a></li>';
		// End of systempaycyberplus
		$nonce_url=wp_nonce_url(admin_url( 'plugins.php?page=wpcb_plugin_options&tab=dev_options&action=sandbox'));
		echo '<li>Tester votre fichier automatic_response.php <a href="'.$nonce_url.'">en cliquant ici</a> (Cela va mettre Ã  jour log.txt et google drive)</li>';
		echo '<li>'.$wpcb_cb_options['automatic_response_url'].'</li>';
		if ((isset($_GET['action'])) && ($_GET['action']=='sandbox')){
			$post_data['DATA']='Dummy'; //Needed
			$post_data['sandbox']='NULL!1!2!'.$wpcb_cb_options['merchant_id'].'!fr!100!8755900!CB!10-02-2012!11:50!10-02-2012!004!certif!22!978!4974!545!1!22!Comp!CompInfo!return!caddie!Merci!fr!fr!001!8787084074894!my@email.com!1.10.21.192!30!direct!data';
			$response=wp_remote_post($wpcb_cb_options['automatic_response_url'],array('body' =>$post_data));
			print_r($response);
		}
}

function wpcb_version_callback(){  
    $options = get_option( 'wpcb_dev_options');  
    $val = $plugin_data['Version']; 
    if(isset($options['version'])){$val = $options['version'];}
        echo '<input type="hidden" id="version" name="wpcb_dev_options[version]" value="' . $val. '" />';
}
function wpcb_mode_demo_callback($args){  
    $options = get_option( 'wpcb_dev_options');  
	$html = '<input type="checkbox" id="mode_demo" name="wpcb_dev_options[mode_demo]" value="1" ' . checked(1, $options['mode_demo'], false) . '/>';  
    $html .= '<label for="mode_demo"> '  . $args[0] . '</label>';   
    echo $html;
}
function wpcb_mode_test_callback($args){  
    $options = get_option( 'wpcb_dev_options');  
	$html = '<input type="checkbox" id="mode_test" name="wpcb_dev_options[mode_test]" value="1" ' . checked(1, $options['mode_test'], false) . '/>';  
    $html .= '<label for="mode_test"> '  . $args[0] . '</label>';   
    echo $html;
}



add_filter( 'plugin_action_links', 'wpcb_plugin_action_links',10,2);
// Display a Settings link on the main Plugins page
function wpcb_plugin_action_links( $links, $file ) {
	if ($file==plugin_basename( __FILE__ )){
		$wpcb_links = '<a href="'.get_admin_url().'plugins.php?page=wpcb_plugin_options">'.__('Settings').'</a>';
		array_unshift( $links, $wpcb_links );
	}
	return $links;
}
// FIN DES REGLAGES :


// SHORTCODE :


add_shortcode( 'wpcb', 'shortcode_wpcb_handler' );
function shortcode_wpcb_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log, $wpsc_cart;
	$sessionid=$_GET['sessionid'];
	$wpcb_general_options = get_option( 'wpcb_general_options' );
	$wpcb_dev_options=get_option( 'wpcb_dev_options' );
    
	$purch_log_email=get_option('purch_log_email');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	if ($_GET['action']=='CB'){
		$wpcb_cb_options = get_option ( 'wpcb_cb_options' );
		// cf. Dictionnaire des Données Atos :
		if ((array_key_exists('mode_demo', $wpcb_dev_options)) && ($wpcb_dev_options['mode_demo'])){
			$merchant_id="082584341411111";
			$pathfile=dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/pathfile";
			$path_bin_request =dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/cgi-bin/demo/request";
		}
		else{
			$merchant_id=$wpcb_cb_options['merchant_id'];	
			$pathfile=$wpcb_cb_options['pathfile'];
			$path_bin_request=$wpcb_cb_options['path_bin_request'];
		}
		$parm="merchant_id=". $merchant_id;
		$parm="$parm merchant_country=".$wpcb_cb_options['merchant_country'];
		$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		$amount= ($purchase_log->totalprice)*100;
		$amount=str_pad($amount,3,"0",STR_PAD_LEFT);
		$parm="$parm amount=".$amount;
		$parm="$parm currency_code=".$wpcb_cb_options['currency_code'];
		$parm="$parm pathfile=". $pathfile;
		$parm="$parm normal_return_url=".$wpcb_cb_options['normal_return_url']."?sessionid=".$sessionid;
		$parm="$parm cancel_return_url=".$wpcb_cb_options['cancel_return_url'];
		$parm="$parm automatic_response_url=".$wpcb_cb_options['automatic_response_url'];
		$parm="$parm language=".$wpcb_cb_options['language'];
		$parm="$parm payment_means=".$wpcb_cb_options['payment_means'];
		$parm="$parm header_flag=".$wpcb_cb_options['header_flag'];
		$parm="$parm order_id=$sessionid";
		$parm="$parm logo_id2=".$wpcb_cb_options['logo_id2'];
		$parm="$parm advert=".$wpcb_cb_options['advert'];
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
	$wpcb_paypal_options = get_option ( 'wpcb_paypal_options' );
	$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
		if ($wpcb_paypal_options['sandbox_paypal']){
			$message='<form action="https://sandbox.paypal.com/cgi-bin/webscr" method="post">';
		}
		else{
			$message='<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		}
		$message.='<input type="hidden" name="cmd" value="_xclick">';
		$message.='<input type="hidden" name="business" value="'.$wpcb_paypal_options['business'].'">';
		$message.='<input type="hidden" name="lc" value="FR">';
		$message.='<input type="hidden" name="item_name" value="Commande #'.$purchase_log->id.'">';
		$message.='<input type="hidden" name="item_number" value="'.$sessionid.'">';
		$amount=number_format($purchase_log->totalprice,2);
		$message.='<input type="hidden" name="amount" value="'.$amount.'">';
		$message.='<input type="hidden" name="no_note" value="1">';
		$message.='<input type="hidden" name="return" value="'.$wpcb_paypal_options['return'].'">';
		$message.='<input type="hidden" name="cancel_return" value="'.$wpcb_paypal_options['cancel_return'].'">';
		$message.='<input type="hidden" name="notify_url" value="'.$wpcb_paypal_options['notify_url'].'">';
		$message.='<input type="hidden" name="no_shipping" value="1"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="button_subtype" value="services"><input type="hidden" name="no_note" value="0"><input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHostedGuest"><input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !"><img alt="" border="0" src="https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif" width="1" height="1"></form>';
	}
	elseif ($_GET['action']=='systempaycyberplus'){
	$wpcb_systempaycyberplus_options = get_option ( 'wpcb_systempaycyberplus_options' );
	$purchase_log=$wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1") ;
	
	$systempay_cyberplus_args = array(
                'vads_site_id' => $wpcb_systempaycyberplus_options['identifiant'],
				'vads_ctx_mode' => 'TEST',
				'vads_version' => 'V2',
				'vads_language' => 'fr',
				'vads_currency' => '978',
				'vads_amount' => $purchase_log->totalprice*100,
				'vads_page_action' => 'PAYMENT',
				'vads_action_mode' => 'INTERACTIVE',
				'vads_payment_config' => 'SINGLE',
				'vads_capture_delay' => '',
				'vads_order_id' =>$purchase_log->id,
				'vads_cust_id' => $purchase_log->id,
				'vads_redirect_success_timeout' => '5',
				'vads_redirect_success_message' => 'Redirection vers la boutique dans quelques instants',
				'vads_redirect_error_timeout' => '5',
				'vads_redirect_error_message' => 'Redirection vers la boutique dans quelques instants',
				'vads_trans_id' => str_pad(rand(0, 8999).date('d'), 6, "0", STR_PAD_LEFT),
				'vads_trans_date' => gmdate("YmdHis")
				);
			$signature=get_Signature($systempay_cyberplus_args,$wpcb_systempaycyberplus_options['certificat']);
			$systempay_cyberplus_args['signature']= $signature;
				
            $message='<form action="https://paiement.systempay.fr/vads-payment/" method="post">';
            foreach($systempay_cyberplus_args as $key => $value){
                $message.= "<input type='hidden' name='$key' value='$value'/>";
            }
			$message.='<input type="image" src="'.$wpcb_systempaycyberplus_options['wpec_gateway_image_systempaycyberplus'].'" border="0" name="submit" alt="Payer"><img alt="" border="0" src="'.$wpcb_systempaycyberplus_options['wpec_gateway_image_systempaycyberplus'].'" >';
			$message.='</form>';	
	}
	elseif ($_GET['action']=='normal_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='cancel_return'){
		// Pas utilisé pour l'instant
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='sandbox'){
	
	
	
	}
	else{
		//Appel direct à cette page
		$message='<p>'.__('Direct call to this page not allowed','wpcb').'</p>';
		// Add here some code if you want to test some php for wpec :
		$wpsc_cart->empty_cart();
	}
	return $message;
} // Fin de la fonction d'affichage du shortcode




// Mailchimp :

/** 
* Mailchimp options
*/  
function wpcb_intialize_mailchimp_options() {  
    if(false == get_option( 'wpcb_mailchimp_options' )){add_option( 'wpcb_mailchimp_options' );}
	add_settings_section('mailchimp_settings_section','mailchimp Options','wpcb_mailchimp_options_callback','wpcb_mailchimp_options');
	add_settings_field('add_to_mailchimp','Ajouter les clients à Mailchimp','wpcb_add_to_mailchimp_callback','wpcb_mailchimp_options','mailchimp_settings_section');
	add_settings_field('listid','List ID','wpcb_listid_mailchimp_callback','wpcb_mailchimp_options','mailchimp_settings_section');
	add_settings_field('apikey','Clé API MailChimp','wpcb_apikey_mailchimp_callback','wpcb_mailchimp_options','mailchimp_settings_section');
	register_setting('wpcb_mailchimp_options','wpcb_mailchimp_options','');
} 
add_action( 'admin_init', 'wpcb_intialize_mailchimp_options' );  



function wpcb_mailchimp_options_callback() {  
    echo '<p>Réglage des options pour le paiement par mailchimp bancaire</p>';  
}


function wpcb_add_to_mailchimp_callback($args){  
    $options = get_option( 'wpcb_mailchimp_options');  
	$html = '<input type="checkbox" id="add_to_mailchimp" name="wpcb_mailchimp_options[add_to_mailchimp]" value="1" ' . checked(1, $options['add_to_mailchimp'], false) . '/>';  
    $html .= '<label for="add_to_mailchimp"> '  . $args[0] . '</label>';   
    echo $html;
}

function wpcb_listid_mailchimp_callback(){  
    $options = get_option( 'wpcb_mailchimp_options');  
    $val ="b2c48b296a"; 
    if(isset($options['listid'])){$val = $options['listid'];}
        echo '<input type="text"  size="75"id="listid" name="wpcb_mailchimp_options[listid]" value="' . $val . '" />';
}

function wpcb_apikey_mailchimp_callback(){  
    $options = get_option( 'wpcb_mailchimp_options');  
    $val ='g0ffbb747d15113611308102b53601ff-us2'; 
    if(isset($options['apikey'])){$val = $options['apikey'];}
        echo '<input type="text"  size="75"id="apikey" name="wpcb_mailchimp_options[apikey]" value="' . $val . '" />';
}






add_action('wpsc_submit_checkout','add_to_mailchimp');
function add_to_mailchimp($a){
	global $wpdb;
	$wpcb_mailchimp_options = get_option ( 'wpcb_mailchimp_options' );
	if ($wpcb_mailchimp_options['add_to_mailchimp']){
	$listid = $wpcb_mailchimp_options['listid'];
	$apikey=$wpcb_mailchimp_options['apiKey'];
	$log_id=$a['purchase_log_id'];
	$email_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=9 LIMIT 1",ARRAY_A);
	$lastname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=3 LIMIT 1",ARRAY_A) ;
	$firstname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=2 LIMIT 1",ARRAY_A) ;
	$email=$email_a['value'];$firstname=$firstname_a['value'];$lastname=$lastname_a['value'];
	if($email){   
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)){
			require_once('MCAPI.class.php');	
			$api = new MCAPI($apikey);		
			$merge_vars = array('FNAME'=>$firstname,'LNAME'=>$lastname); 
			$api->listSubscribe($listid, $email,$merge_vars,'',false,true);
		}
	}
	}
}
function get_Signature($field,$key) {

		ksort($field); // tri des paramétres par ordre alphabétique
		$contenu_signature = "";
		foreach ($field as $nom => $valeur){
			if(substr($nom,0,5) == 'vads_') {
			$contenu_signature .= $valeur."+";
		}
		}
		$contenu_signature .= $key;	// On ajoute le certificat à la fin de la chaîne.
		$signature = sha1($contenu_signature);
		return($signature);
	}
	function Check_Signature($field,$key) {
		$result='false';
		$signature=get_Signature($field,$key);
		if(isset($field['signature']) && ($signature == $field['signature']))
		{	$result='true';}
		return ($result);
	}
	function uncharm($potentiallyMagicallyQuotedData) {
			if (get_magic_quotes_gpc()) {
				$sane = array();
				foreach ($potentiallyMagicallyQuotedData as $k => $v) {
					$saneKey = stripslashes($k);
					$saneValue = is_array($v) ? SystempayApi::uncharm($v) : stripslashes($v);
					$sane[$saneKey] = $saneValue;
				}
			} else {
				$sane = $potentiallyMagicallyQuotedData;
			}
			return $sane;
		}




?>