<?php
// Cheques (forcement deux fichiers...)
$nzshpcrt_gateways[$num] = array(
	'name' => 'Cheque (WPCB)',
	'api_version' => 2.0,
	'has_recurring_billing' => true,
	'display_name' => 'Paiement par Ch√®que',	
	'wp_admin_cannot_cancel' => false,
	'requirements' => array(),
	'submit_function' => 'submit_cheque',
	'form' => 'form_cheque',
	'internalname' => 'cheque',
	'class_name' => 'wpsc_merchant_cheque',
);

class wpsc_merchant_cheque extends wpsc_merchant {
	function submit(){
		global $wpdb;
			$this->set_purchase_processed_by_purchid(2);
			$this->go_to_transaction_results($this->cart_data['session_id']);
		}// end of submit
} // end of class

// This function add special message to the transaction result page and report ->
function cheque_custom_message($text) {
			$wpcb_cheque_options = get_option ( 'wpcb_cheque_options' );
			if ($_SESSION['wpsc_previous_selected_gateway']=='cheque')	{
				$text = $text.'
				'.$wpcb_cheque_options['displaycheque'].'
				';
			}
			return $text;
}

add_filter("wpsc_transaction_result_report", "cheque_custom_message");
add_filter("wpsc_transaction_result_message_html", "cheque_custom_message");
add_filter("wpsc_transaction_result_message", "cheque_custom_message");

function form_cheque() {
<<<<<<< .mine
	// Les rÈglages se font ailleurs car les rÈglages de wpec sont trop pourris...
	$output='<a href="plugins.php?page=wpcb_plugin_options&tab=cheque_options">Cliquez ici pour les autres rÈglages</a>';
=======
	// Les r√©glages se font ailleurs car les r√©glages de wpec sont trop pourris...
	$output='<a href="'.admin_url().'/options-general.php?page=wpcb/wpcb.php">Cliquez ici pour les r√©glages</a>';
>>>>>>> .r558493
	return $output;
}
function submit_cheque(){return true;}
?>