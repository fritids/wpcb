<?php
$wpcb_systempaycyberplus_options = get_option ( 'wpcb_systempaycyberplus_options' );
$nzshpcrt_gateways[$num] = array(
'name' => 'CB Systempay Cyberplus (WPCB)',
'api_version' => 2.0,
'class_name' => 'wpsc_merchant_systempaycyberplus',
'has_recurring_billing' => true,
'display_name' => 'Cartes Bancaires (Systempay Cyberplus) ',
'wp_admin_cannot_cancel' => false,
'requirements' => array(),'form' => 'form_systempaycyberplus',
'internalname' => 'systempaycyberplus',
'submit_function' => 'submit_systempaycyberplus',
'image' => $wpcb_systempaycyberplus_options['wpec_gateway_image']
);

class wpsc_merchant_systempaycyberplus extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log;
		$sessionid=$this->cart_data['session_id'];
		wp_redirect(site_url('?action=securepayment&gateway=systempaycyberplus&sessionid='.$sessionid));
		exit;
	}// end of submit
} //end of class

function form_systempaycyberplus() {
	$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=systempaycyberplus').'">Cliquez ici pour les réglages</a>';
	return $output;
}

function submit_systempaycyberplus(){return true;}
?>