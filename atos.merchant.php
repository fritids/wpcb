<?php
$options = get_option('wpcb_atos');

$nzshpcrt_gateways[$num] = array(
'name' => 'CB Atos (WPCB)',
'api_version' => 2.0,
'has_recurring_billing' => true,
'display_name' => $options['wpec_display_name'],
'wp_admin_cannot_cancel' => false,
'requirements' => array(),
'form' => 'form_atos',
'internalname' => 'wpcb',
'class_name' => 'wpsc_merchant_atos',
'submit_function' => 'submit_atos',
'image' => $options['wpec_gateway_image'] // Image lors de la selection du mode de paiement
);

class wpsc_merchant_atos extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log,$wpsc_cart;
		$sessionid=$this->cart_data['session_id'];
		$options = get_option('wpcb_dev');
		if ($options){
			if ((array_key_exists('test', $options)) && ($options['test'])){
				// Mode test, on considère que la CB a été acceptée automatiquement.
				// Affiche la page de la fin de transaction et on met à jour la base de donnée avec un vente réussie
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3' WHERE `sessionid`=".$sessionid);
				// redirection is inside transaction result :
				transaction_results($sessionid,false);
			}
			else {// Affiche les icônes des cartes bancaires :
			
				if ((array_key_exists('mode_shortcode', $options)) && ($options['mode_shortcode'])){
				// Trouver la page oÃ¹ le shortcode [wpcb] se situe. Bug si plusieurs fois le shortcode [wpcb], Ã  rÃ©soudre todo
				$wpcb_checkout_page=$wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE `post_content` LIKE '%[wpcb]%' AND `post_status`='publish'");
				wp_redirect(site_url('?p='.$wpcb_checkout_page->ID.'&sessionid='.$sessionid.'&action=CB'));
				}
				else {
					wp_redirect(site_url('?action=securepayment&gateway=atos&sessionid='.$sessionid));
				}
			}
		}
		else {
			// C'est une erreur, il faut régler les options avant. Ne doit pas se produire normalement.
			wp_redirect(site_url('?action=ReglerLesOptionsAvantTout'));			
		}
		exit;
	} // end of submit function
} // end of class.

	
function submit_atos(){return true;}


function form_atos() {
	// Les réglages se font ailleurs car les réglages de wpec sont trop pourris...
	$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=atos').'">Cliquez ici pour les réglages</a>';
	return $output;
}


?>