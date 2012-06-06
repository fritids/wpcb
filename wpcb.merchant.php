<?php
$options = get_option('wpcb_options');

$nzshpcrt_gateways[$num] = array(
'name' => 'Cartes Bancaires',
'api_version' => 2.0,
'has_recurring_billing' => true,
'display_name' => $options['wpec_display_name'],
'wp_admin_cannot_cancel' => false,
'requirements' => array(),
'form' => 'form_wpcb',
'internalname' => 'wpcb',
'class_name' => 'wpsc_merchant_wpcb',
'submit_function' => 'submit_wpcb',
'image' => $options['wpec_gateway_image'] // Image lors de la selection du mode de paiement
);

class wpsc_merchant_wpcb extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log,$wpsc_cart;
		$sessionid=$this->cart_data['session_id'];
		$options = get_option('wpcb_options');
		// Trouver la page o� le shortcode [wpcb] se situe. Bug si plusieurs fois le shortcode [wpcb], � r�soudre todo
		$wpcb_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish'");
		if ((array_key_exists('test', $options)) && ($options['test'])){
			// Mode test, on consid�re que la CB a �t� accept�e automatiquement.
			// Affiche la page de la fin de transaction et on met � jour la base de donn�e avec un vente r�ussie
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
			// redirection is inside transaction result :
			transaction_results($sessionid,false);
		}
		else {// Affiche les ic�nes des cartes bancaires :
			$action='CB';
			// On va vers la page ou se trouve le shortcode
			wp_redirect(site_url('?p='.$wpcb_checkout_page->ID.'&sessionid='.$sessionid.'&action='.$action));
		}
		exit;
	} // end of submit function
} // end of class.

	
function submit_wpcb(){return true;}


function form_wpcb() {
	// Les r�glages se font ailleurs car les r�glages de wpec sont trop pourris...
	$output='<a href="'.site_url().'/wp-admin/options-general.php?page=wpcb/wpcb.php">Cliquez ici pour les r�glages</a>';
	return $output;
}


?>