<?php
$options = get_option('wpcb_paypal');
$nzshpcrt_gateways[$num] = array(
'name' => 'Paypal (WPCB)',
'api_version' => 2.0,
'class_name' => 'wpsc_merchant_simplepaypal',
'has_recurring_billing' => true,
'display_name' => 'Paypal Sécurisé',
'wp_admin_cannot_cancel' => false,
'requirements' => array(),'form' => 'form_simplepaypal',
'internalname' => 'simplepaypal',
'submit_function' => 'submit_simplepaypal',
'image' => $options['wpec_gateway_image']
);

class wpsc_merchant_simplepaypal extends wpsc_merchant {
	function submit(){
		global $wpdb,$purchase_log;
		$sessionid=$this->cart_data['session_id'];
		wp_redirect(site_url('?action=securepayment&gateway=paypal&sessionid='.$sessionid));
		exit;
	}// end of submit
} //end of class

function form_simplepaypal() {
	$output='<a href="'.admin_url( 'plugins.php?page=wpcb&tab=paypal').'">Cliquez ici pour les réglages</a>';
	return $output;
}

function submit_simplepaypal(){return true;}
?>