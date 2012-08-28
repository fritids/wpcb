<?php
include('../../../wp-load.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
global $wpdb;
$sessionid=$_GET['id'];
$options = get_option( 'wpcb_misc');  

echo '<p>'.$options['facture_header'].'</p>';
echo "Session :".$sessionid.'<br/>';

$purchase_log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= %s LIMIT 1", $sessionid ), ARRAY_A );
$purchase_log_id=$purchase_log['id'];
 // print_r($purchase_log);
echo "Numéro de commande :".$purchase_log['id'].'<br/>';
$q="SELECT * FROM `".WPSC_TABLE_META."` WHERE `object_id` ='". $purchase_log['id']."' AND `object_type`='wpcb_purchase' AND `meta_key`='wpcb_billing_number' LIMIT 1";

$billing_number = $wpdb->get_row($q);
$billing_number = $billing_number->meta_value;
echo 'Facture #'.$options['facture_prefixe'].str_pad($billing_number, 10, "0", STR_PAD_LEFT).'<br/>';
echo '<br/><p>Détail</p>';
$cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = '{$purchase_log_id}'" , ARRAY_A );
	$detail='';
	$totaltotal=0;
	if ( $cart != null) {
		foreach ( $cart as $row ) {
			$total=$row['quantity']*$row['price'];
			$totaltotal+=$total;
			$detail.=$row['quantity'].'x '.$row['name'].'('.$row['price'].' p.u.) Total HT = '.$total.' <br/> ';
		}
			//$row['name'] 		$row['price']
	}
	$detail=substr($detail, 0, -7);
echo $detail;

echo '<p><strong>Total :'.$totaltotal.'€</strong></p>'
?>
</body>
</html>