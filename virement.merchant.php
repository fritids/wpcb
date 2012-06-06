<?php
// Virements
$nzshpcrt_gateways[$num] = array(
	'name' => 'Virement',
	'api_version' => 2.0,
	'has_recurring_billing' => true,
	'display_name' => 'Paiement par Ch�que',	
	'wp_admin_cannot_cancel' => false,
	'requirements' => array(),
	'submit_function' => 'submit_virement',
	'form' => 'form_virement',
	'internalname' => 'virement',
	'class_name' => 'wpsc_merchant_virement',
);

class wpsc_merchant_virement extends wpsc_merchant {
	function submit(){
		global $wpdb;
			$this->set_purchase_processed_by_purchid(2);
			$this->go_to_transaction_results($this->cart_data['session_id']);
		}// end of submit
} // end of class

// This function add special message to the transaction result page and report ->
function virement_custom_message($text) {
			$options = get_option('wpcb_options');
			if ($_SESSION['wpsc_previous_selected_gateway']=='virement')	{
				$text = $text.'
				'.$options['textarea_virement'].'
				';
			}
			return $text;
}

add_filter("wpsc_transaction_result_report", "virement_custom_message");
add_filter("wpsc_transaction_result_message_html", "virement_custom_message");
add_filter("wpsc_transaction_result_message", "virement_custom_message");

function form_virement() {
	// Les r�glages se font ailleurs car les r�glages de wpec sont trop pourris...
	$output='<a href="'.admin_url().'/options-general.php?page=wpcb/wpcb.php">Cliquez ici pour les r�glages</a>';
	return $output;
}
function submit_virement(){return true;}
?>