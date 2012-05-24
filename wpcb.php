<?php
/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://wpcb.fr
Description: Credit Card Payement Gateway for ATOS SIPS (Mercanet,...) (WP e-Commerce is required)
Version: 1.1.5
Author: 6WWW
Author URI: http://6www.net
*/

define('__WPRoot__',dirname(dirname(dirname(dirname(__FILE__)))));
define('__ServerRoot__',dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('__SiteUrl__',site_url());
define('__wpcbDir__',dirname(__file__));

register_deactivation_hook( __FILE__, 'wpcb_deactivate' );
function wpcb_deactivate(){
		$destinationFile = __WPRoot__.'/automatic_response.php';			
		unlink($destinationFile);
		$destinationFile = __WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		unlink($destinationFile);
}

function wpcb_update() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );
	if ( version_compare($wp_version, "3.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
	// Check if it is a plugin update :
	$options = get_option('wpcb_options');
	if ( version_compare($plugin_data['Version'],$options['version'], ">" ) ) {
		wpcb_activate(); // So that the file are copied again !
	}
}
add_action( 'admin_init', 'wpcb_update' );
register_activation_hook(__FILE__, 'wpcb_activate');
register_uninstall_hook(__FILE__, 'wpcb_delete_plugin_options');
add_action('admin_init', 'wpcb_init' );
add_action('admin_menu', 'wpcb_add_options_page');
add_filter( 'plugin_action_links', 'wpcb_plugin_action_links', 10, 2 );
function wpcb_delete_plugin_options() {
	delete_option('wpcb_options');
}
function wpcb_activate() {
	$tmp = get_option('wpcb_options');
	$sourceFile = __wpcbDir__. '/automatic_response.php';
	$destinationFile = __WPRoot__.'/automatic_response.php';			
	copy($sourceFile, $destinationFile);
	$sourceFile = __wpcbDir__. '/wpcb.merchant.php';
	$destinationFile = __WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
	copy($sourceFile, $destinationFile);
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('wpcb_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"merchant_id" => "082584341411111","pathfile" => __ServerRoot__."cgi-bin/demo/pathfile",
						"path_bin_request" => __ServerRoot__."cgi-bin/demo/request",
						"path_bin_response" => __ServerRoot__."cgi-bin/demo/response" ,
						"merchant_country" => "fr","currency_code" => "978",
						"normal_return_url" => __SiteUrl__,"cancel_return_url" => __SiteUrl__,
						"language" => "fr","payment_means" => "CB,2,VISA,2,MASTERCARD,2",
						"header_flag" => "no","logfile" => "/homez.136/littlebii/cgi-bin/demo/log.txt",
						"advert" => "advert.jpg","logo_id" => "logo_id.jpg","logo_id2" => "logo_id2.jpg",
						"wpec_gateway_image" => __SiteUrl__."/wp-content/plugins/wpcb/logo/LogoMercanetBnpParibas.gif",
						"wpec_display_name" => "Cartes bancaires (Visa, Master Card,...)",
						"debug"=>"1","test"=>"1","demo"=>"1","version"=>$plugin_data['Version']);
		update_option('wpcb_options', $arr);
	}
}
function wpcb_init(){
	register_setting( 'wpcb_plugin_options', 'wpcb_options', 'wpcb_validate_options' );
}
function wpcb_add_options_page() {
	add_options_page('wpcb Options Page', 'Wpcb', 'manage_options', __FILE__, 'wpcb_render_form');
}
function wpcb_render_form() {
	global $wpdb;
	$options = get_option('wpcb_options');
	$plugin_data = get_plugin_data( __FILE__, false );
	?>
	<div class="wrap">
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>wpcb Options</h2>
		<ol>
		<?php
		$wpcb_Dir=dirname(__file__);
		$sourceFile=$wpcb_Dir.'/automatic_response.php';
		$destinationFile = __WPRoot__.'/automatic_response.php';			
		if(!file_exists($destinationFile)) {copy($sourceFile, $destinationFile);}
		if ($_GET['action']=='copyautomaticresponse'){copy($sourceFile, $destinationFile);}
		//				<?php copy($sourceFile, $destinationFile);
		if(file_exists(!$destinationFile)) {
			echo '<li><span style="color:red;">Copier le fichier automatic_response.php vers '.$destinationFile.' <a href="http://wpcb.fr/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien là -> Ok!</span></li>';					
		}
		$sourceFile = $wpcb_Dir . '/wpcb.merchant.php';
		$destinationFile = __WPRoot__.'/wp-content/plugins/wp-e-commerce/wpsc-merchants/wpcb.merchant.php';
		if(!file_exists($destinationFile)) {copy($sourceFile, $destinationFile);}
		if ($_GET['action']=='copywpcbmerchant'){copy($sourceFile, $destinationFile);}
		if(!file_exists($destinationFile)) {
			echo '<li><span style="color:red;">Copier le fichier '.$wpcb_Dir.'/wpcb.merchant.php vers '.$destinationFile.' <a href="http://wpcb.fr/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant">en cliquant ici</a></span></li>';
		} 
		else {
			echo '<li><span style="color:green">Le fichier '.$destinationFile.' est bien là -> Ok!</span></li>';					
		}
		$wpcb_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish'");
		if ($wpcb_checkout_page!=NULL){
			echo '<li><span style="color:green">wpcb shortcode [wpcb] is on page : <a href="'.site_url('?page_id='.$wpcb_checkout_page->ID).'">'.$wpcb_checkout_page->ID.'</a> -> Ok!</span></li>';
		}
		else {
			echo '<li><span style="color:red">You should place wpcb shortcode [wpcb] somewhere in a page of your site!</span></li>';
		}
		
		$post_data['apiKey']=$options['apiKey'];
		$post_data['emailapiKey']=$options['emailapiKey'];
		$valid=api_curl('http://wpcb.fr/api/wpcb/valid.php',$post_data);
		if ($valid[0]){
			echo '<li><span style="color:green">Your API Key is valid -> Ok!</span></li>';
		}
		else {
			echo '<li><span style="color:red">Optionel : Vous pouvez débloquer l\'assistance en <a href="http://wpcb.fr/api-key/" target="_blank">achetant une clé API</a></span></li>';
		}
	?>
		
		</li>
		<?php $plugin_dir_path = dirname(__FILE__);?>
		<li>Fill in the form below</li>
		</ol>
		<!-- Beginning of the Plugin Options Form -->
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
					<th scope="row">merchant_country</th>
					<td><input type="text" size="3" name="wpcb_options[merchant_country]" value="<?php echo $options['merchant_country']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">currency_code</th>
					<td><input type="text" size="3" name="wpcb_options[currency_code]" value="<?php echo $options['currency_code']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">normal_return_url</th>
					<td><input type="text" size="57" name="wpcb_options[normal_return_url]" value="<?php echo $options['normal_return_url']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">cancel_return_url</th>
					<td><input type="text" size="57" name="wpcb_options[cancel_return_url]" value="<?php echo $options['cancel_return_url']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">language</th>
					<td><input type="text" size="57" name="wpcb_options[language]" value="<?php echo $options['language']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">payment_means</th>
					<td><input type="text" size="57" name="wpcb_options[payment_means]" value="<?php echo $options['payment_means']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">header_flag</th>
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
					<th scope="row">wpec_gateway_image</th>
					<td><input type="text" size="57" name="wpcb_options[wpec_gateway_image]" value="<?php echo $options['wpec_gateway_image']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">wpec_display_name</th>
					<td><input type="text" size="57" name="wpcb_options[wpec_display_name]" value="<?php echo $options['wpec_display_name']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">Log file (Optionel)</th>
					<td><input type="text" size="57" name="wpcb_options[logfile]" value="<?php echo $options['logfile']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">apiKey (Optionel)</th>
					<td><input type="text" size="57" name="wpcb_options[apiKey]" value="<?php echo $options['apiKey']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">emailapiKey (Optionel)</th>
					<td><input type="text" size="57" name="wpcb_options[emailapiKey]" value="<?php echo $options['emailapiKey']; ?>" /></td>
				</tr>
				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Developpeur</th>
					<td>
						<label><input name="wpcb_options[debug]" type="checkbox" value="1" <?php if (isset($options['debug'])) { checked('1', $options['debug']); } ?> /> Mode debug</label><br />
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
echo '<li><p>Plugin version : '.$options['version'].'</li>';
echo '<li><p>Racine wordpress : '.__WPRoot__.'</p></li>';
echo '<li>Racine site <pre>'.__ServerRoot__.'</pre></li>';
echo '<li>Developpeur : Copier le fichier automatic_response.php vers '.$destinationFile.' <a href="http://wpcb.fr/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copyautomaticresponse">en cliquant ici</a></li>';
echo '<li>Developpeur : Copier le fichier '.$wpcb_Dir.'/wpcb.merchant.php vers '.$destinationFile.' <a href="http://wpcb.fr/wp-admin/options-general.php?page=wpcb/wpcb.php&action=copywpcbmerchant">en cliquant ici</a></li>';
?>
			<p><a href="http://www.seoh.fr" target="_blank">Référencement avec SEOh</a></p>
			<p>Additional help : <a href="http://wpcb.fr" target="_blank">http://wpcb.fr</a> (will open in a new tab)</p>
		</p>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpcb_validate_options($input) {
	 // strip html from textboxes
	$input['merchant_id'] =  wp_filter_nohtml_kses($input['merchant_id']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['pathfile'] =  wp_filter_nohtml_kses($input['pathfile']); // Sanitize textbox input (strip html tags, and escape characters)
	$input['path_bin_request'] =  wp_filter_nohtml_kses($input['path_bin_request']);
	$input['path_bin_response'] =  wp_filter_nohtml_kses($input['path_bin_response']);
	$input['logfile'] =  wp_filter_nohtml_kses($input['logfile']);
	$input['merchant_country'] =  wp_filter_nohtml_kses($input['merchant_country']);
	$input['currency_code'] =  wp_filter_nohtml_kses($input['currency_code']);
	$input['normal_return_url'] =  wp_filter_nohtml_kses($input['normal_return_url']);
	$input['cancel_return_url'] =  wp_filter_nohtml_kses($input['cancel_return_url']);
	$input['language'] =  wp_filter_nohtml_kses($input['language']);
	$input['payment_means'] =  wp_filter_nohtml_kses($input['payment_means']);
	$input['header_flag'] =  wp_filter_nohtml_kses($input['header_flag']);
	$input['advert'] =  wp_filter_nohtml_kses($input['advert']);
	$input['logo_id'] =  wp_filter_nohtml_kses($input['logo_id']);
	$input['logo_id2'] =  wp_filter_nohtml_kses($input['logo_id2']);
	$input['wpec_gateway_image'] =  wp_filter_nohtml_kses($input['wpec_gateway_image']);
	$input['wpec_display_name'] =  wp_filter_nohtml_kses($input['wpec_display_name']);
	$input['apiKey'] =  wp_filter_nohtml_kses($input['apiKey']);
	$input['emailapiKey'] =  wp_filter_nohtml_kses($input['emailapiKey']);	
	return $input;
}

// Display a Settings link on the main Plugins page
function wpcb_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$wpcb_links = '<a href="'.get_admin_url().'options-general.php?page=wpcb/wpcb.php">'.__('Settings').'</a>';
		array_unshift( $links, $wpcb_links );
	}
	return $links;
}


function shortcode_wpcb_handler( $atts, $content=null, $code="" ) {
	global $wpdb, $purchase_log, $wpsc_cart;
	$sessionid=$_GET['sessionid'];
	$purch_log_email=get_option('purch_log_email');
	$options = get_option('wpcb_options');
	if (!$purch_log_email){$purch_log_email=get_bloginfo('admin_email');}
	if ($_GET['action']=='CB')
	{
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
		if ($options['debug']){$parm_pretty=str_replace(' ','<br/>',$parm);echo '<p>You see this because you are in debug mode :</p><pre>'.$parm_pretty.'</pre><p>End of debug mode</p>';}
		$result=exec("$path_bin_request $parm");
		$tableau = explode ("!","$result");
		$code = $tableau[1];
		$error = $tableau[2];
		if (( $code=="") && ($error==""))
		{
			$message="<p>".__('Error calling the atos api : exec request not found','wpcb')."  $path_bin_request</p>";
			if ($options['debug']){ $message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";}
		}
		elseif ($code != 0) {
			$message="<p>".__('Atos API error : ','wpcb')." $error</p>";
			if ($options['debug']){ $message.= "<p>".__('Thank you for reporting this error to:','wpcb')." ".$purch_log_email."</p>";}
		}
		else
		{
			// Affiche le formulaire avec le choix des cartes bancaires :
			$message = $tableau[3];
		}
		// End of atos
	}
	elseif ($_GET['action']=='normal_return'){
		$wpsc_cart->empty_cart();
	}
	elseif ($_GET['action']=='cancel_return'){
		$wpsc_cart->empty_cart();
	}
	else
	{
		$message='<p>'.__('Direct call to this page not allowed','wpcb').'</p>';
		// Add here some code if you want to test some php for wpec :
		$wpsc_cart->empty_cart();
	}
	return $message;
}
add_shortcode( 'wpcb', 'shortcode_wpcb_handler' );


function api_curl($path,$post_data= null) {
	//traverse array and prepare data for posting (key1=value1)
	if($post_data){
		foreach ( $post_data as $key => $value) {$post_items[] = $key . '=' . $value;}
		$post_string = implode ('&', $post_items);
		//create cURL connection
		$curl_connection =curl_init($path);
		//set options
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($curl_connection);
		curl_close($curl_connection);
	}
	else{
		$result=false;
	}
	$result_a=unserialize($result);
	return $result_a;
}







?>