<?php
// Trello :

/** 
* Trello options
*/  
function wpcb_intialize_trello_options() {  
    if(false == get_option( 'wpcb_trello' )){add_option( 'wpcb_trello' );}
	add_settings_section('trello_settings_section','trello Options','wpcb_trello_callback','wpcb_trello');
	add_settings_field('add_to_trello','Ajouter les contacts à trello','wpcb_add_to_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('apiKey','Clé API trello','wpcb_apiKey_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('token','Token trello','wpcb_token_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('boardid','Board','wpcb_boardid_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('listid','List','wpcb_listid_trello_callback','wpcb_trello','trello_settings_section');
	register_setting('wpcb_trello','wpcb_trello','');
} 
add_action( 'admin_init', 'wpcb_intialize_trello_options' );  



function wpcb_trello_callback() {  
    echo '<p>Réglage des options pour Trello</p>';
	echo '<p>Sur certains site, il faut cliquer plusieurs fois sur "Sauvegarder"</p>';
	$options = get_option( 'wpcb_trello'); 
	$connectionInfos=wpcb_trello_checkConnection($options['apiKey'],$options['token']);
	$memberInfos=getMembersInfos($options['apiKey'],$options['token'],$connectionInfos->idMember);
	if ($memberInfos->fullName){
		echo '<p>Connected as '.$memberInfos->fullName.'</p>';
	}
	else {
		echo '<p>Not Connected</p>';
		$nonce_url=wp_nonce_url(add_query_arg( array('tab' => 'trello', 'action' => 'updatetoken'), admin_url( 'plugins.php?page=wpcb')));
		echo '<a href="https://trello.com/1/authorize?key='.$options['apiKey'].'&return_url='.$nonce_url.'&name=WPCB&expiration=never&response_type=token&scope=read,write">Connect</a>';
	}
}


function wpcb_add_to_trello_callback($args){  
    $options = get_option( 'wpcb_trello');  
	$html = '<input type="checkbox" id="add_to_trello" name="wpcb_trello[add_to_trello]" value="1" ' . checked(1, $options['add_to_trello'], false) . '/>';  
    $html .= '<label for="add_to_trello"> '  . $args[0] . '</label>';   
    echo $html;
}

function wpcb_boardid_trello_callback(){  
    $options = get_option( 'wpcb_trello');
	
	$connectionInfos=wpcb_trello_checkConnection($options['apiKey'],$options['token']);
	$memberInfos=getMembersInfos($options['apiKey'],$options['token'],$connectionInfos->idMember);

	if ($memberInfos->fullName){
		echo '<SELECT name="wpcb_trello[boardid]" id="wpcb_trello[boardid]">';
		foreach ($memberInfos->boards as $board){
			echo '<OPTION VALUE="'.$board->id.'"'; 
			if ($options['boardid']==$board->id){echo ' SELECTED ';}
			echo '>'.$board->name.'</OPTION>';
		}
		echo '</SELECT>';
	}
	else {
		echo 'Connect first using the link above';
	}
}


function wpcb_listid_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val ="";
	if ($options['boardid']){
		$lists=cURL_GET_trello('','https://api.trello.com/1/boards/'.$options['boardid'].'/lists',$options['apiKey'],$options['token']);
		if ($lists){
		echo '<SELECT name="wpcb_trello[listid]" id="wpcb_trello[listid]">';
		foreach ($lists as $list){
			echo '<OPTION VALUE="'.$list->id.'"';
			if ($options['listid']==$list->id){echo ' SELECTED ';}
			echo '>'.$list->name.'</OPTION>';
		}
		echo '</select>';
		} //end of if lists
		else {
			echo '<p>No list has been found in the boardid : '.$boardid.'</p>';
		}
	}
	else {
		echo '<p>Choose a board before</p>';
	}
}

function wpcb_apiKey_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val =''; 
    if(isset($options['apiKey'])){$val = $options['apiKey'];}
        echo '<input type="text"  size="75" id="apiKey" name="wpcb_trello[apiKey]" value="' . $val . '" />';
		echo '(voir ici : <a href="https://trello.com/docs/gettingstarted/index.html#getting-an-application-key" target="_blank">https://trello.com/docs/gettingstarted/index.html#getting-an-application-key</a>)';
}

function wpcb_token_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val =''; 
	if ($_GET['token']){
		$options['token']=$_GET['token'];
		update_option('wpcb_trello',$options);
		//$val = $_GET['token'];
	}
    if(isset($options['token'])){$val = $options['token'];}
        echo '<input type="text"  size="75" id="token" name="wpcb_trello[token]" value="' . $val . '" readonly/>';
}

add_action('wpsc_submit_checkout','add_to_trello');
function add_to_trello($a){
	global $wpdb;
	$options = get_option ( 'wpcb_trello' );
	if ($options['add_to_trello']){
	$log_id=$a['purchase_log_id'];
	
	//$purchase = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE id=".$log_id." LIMIT 1",ARRAY_A);
	$cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = '{$log_id}'" , ARRAY_A );
	$detail='Order #'.$log_id.' ';
	if ( $cart != null) {
		foreach ( $cart as $row ) {
		$detail.=$row['quantity'].'x '.$row['name'].'('.$row['price'].'€ p.u.) & ';
		}
	}
	$detail = substr($detail, 0, -3);
	
	
	$email_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=9 LIMIT 1",ARRAY_A);
	$lastname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=3 LIMIT 1",ARRAY_A) ;
	$firstname_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=2 LIMIT 1",ARRAY_A) ;
	$mobile_a = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE log_id=".$log_id." AND form_id=18 LIMIT 1",ARRAY_A);
	$email=$email_a['value'];
	$firstname=$firstname_a['value'];
	$lastname=$lastname_a['value'];
	$mobile=$mobile_a['value'];
	
	$data = array(
		'idList' => $options['listid'],
		'name' => $detail
	);
	$card=cURL_POST_trello($data,'https://trello.com/1/cards/',$options['apiKey'],$options['token']);
	// Add a comment to the card so that wo not send the message again
	$url='https://trello.com/1/cards/'.$card->id.'/actions/comments';
	$comments[] = array('text'=>'email:'.$email);
	$comments[]= array('text'=>'firstname:'.$firstname);
	$comments[] = array('text'=>'lastname:'.$lastname);
	$comments[] = array('text'=>'mobile:'.$mobile);
	foreach ($comments as $comment){
		$result=cURL_POST_trello($comment,$url,$options['apiKey'],$options['token']);
	}
	}
}


function getMembersInfos($key,$token,$idMember){
	$url='https://trello.com/1/members/'.$idMember.'?fields=fullName&boards=all&board_fields=name';
	$memberInfos=cURL_GET_trello('',$url,$key,$token);
	return $memberInfos;
}

function wpcb_trello_checkConnection($key,$token){
	$url='https://trello.com/1/tokens/'.$token;
	$connectionInfos=cURL_GET_trello('',$url,$key,$token);
	return $connectionInfos;
}

function cURL_POST_trello($data,$url,$key,$token){
$ch = curl_init();
$questionmarkishere=strpos($url,'?');
if ($questionmarkishere){$char='&';}else{$char='?';}
curl_setopt($ch, CURLOPT_URL, $url.$char.'key='.$key.'&token='.$token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HEADER, false);
$body = curl_exec($ch);
$headers = curl_getinfo($ch);
//$result=array('headers'=>$headers,'body'=>$body);
curl_close($ch);
$result=json_decode($body);
return $result;
}

function cURL_GET_trello($data,$url,$key,$token){
	$questionmarkishere=strpos($url,'?');
	if ($questionmarkishere){$char='&';}else{$char='?';}
	$headers = array(
		"GET /HTTP/1.1","User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
		"Content-type: text/xml;charset=\"utf-8\"","Keep-Alive: 300","Connection: keep-alive"
	);
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,$url.$char.'key='.$key.'&token='.$token);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$data = curl_exec($ch); 
	if (curl_errno($ch)) { print "Error: " . curl_error($ch);}
	else {  
		// Show me the result
		//var_dump($data);
	}
	curl_close($ch);
	$result=json_decode($data);
	return $result;
}



?>