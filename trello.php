<?php
// Trello :

/** 
* Trello options
*/  
function wpcb_intialize_trello_options() {  
    if(false == get_option( 'wpcb_trello' )){add_option( 'wpcb_trello' );}
	add_settings_section('trello_settings_section','trello Options','wpcb_trello_callback','wpcb_trello');
	add_settings_field('add_to_trello','Ajouter les contacts à trello','wpcb_add_to_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('listid','List ID','wpcb_listid_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('apikey','Clé API trello','wpcb_apikey_trello_callback','wpcb_trello','trello_settings_section');
	add_settings_field('token','Token trello','wpcb_token_trello_callback','wpcb_trello','trello_settings_section');
	register_setting('wpcb_trello','wpcb_trello','');
} 
add_action( 'admin_init', 'wpcb_intialize_trello_options' );  



function wpcb_trello_callback() {  
    echo '<p>Réglage des options pour Trello</p>';
	$options = get_option( 'wpcb_trello'); 
	$connectionInfos=checkConnection($options['apikey'],$options['token']);
	$memberInfos=getMembersInfos($options['apikey'],$options['token'],$connectionInfos->idMember);
	$connected=false;
	if ($memberInfos->fullName){
		echo '<p>Connected</p>';
	}
	else {
		echo '<p>Not Connected</p>';
		$nonce_url=urlencode(wp_nonce_url(admin_url( 'plugins.php?page=wpcb&tab=trello&action=updatetoken')));
		echo '<a href="https://trello.com/1/authorize?key='.$options['apikey'].'&return_url='.$nonce_url.'&name= WPCB&expiration=30days&response_type=token&scope=read,write">Connect</a>';
	}
}


function wpcb_add_to_trello_callback($args){  
    $options = get_option( 'wpcb_trello');  
	$html = '<input type="checkbox" id="add_to_trello" name="wpcb_trello[add_to_trello]" value="1" ' . checked(1, $options['add_to_trello'], false) . '/>';  
    $html .= '<label for="add_to_trello"> '  . $args[0] . '</label>';   
    echo $html;
}

function wpcb_listid_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val ="b2c48b296a"; 
    if(isset($options['listid'])){$val = $options['listid'];}
        echo '<input type="text"  size="75"id="listid" name="wpcb_trello[listid]" value="' . $val . '" />';
}

function wpcb_apikey_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val ='g0ffbb747d15113611308102b53601ff-us2'; 
    if(isset($options['apikey'])){$val = $options['apikey'];}
        echo '<input type="text"  size="75"id="apikey" name="wpcb_trello[apikey]" value="' . $val . '" />';
}

function wpcb_token_trello_callback(){  
    $options = get_option( 'wpcb_trello');  
    $val ='g0ffbb747d15113611308102b53601ff-us2'; 
    if(isset($options['token'])){$val = $options['token'];}
        echo '<input type="text"  size="75"id="token" name="wpcb_trello[token]" value="' . $val . '" />';
}

add_action('wpsc_submit_checkout','add_to_trello');
function add_to_trello($a){
	global $wpdb;
	$wpcb_trello = get_option ( 'wpcb_trello' );
	if ($wpcb_trello['add_to_trello']){
	$listid = $wpcb_trello['listid'];
	$apikey=$wpcb_trello['apiKey'];
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


function getMembersInfos($key,$token,$idMember){
	$url='https://trello.com/1/members/'.$idMember.'?fields=fullName&boards=all&board_fields=name';
	$memberInfos=cURL_GET_trello('',$url,$key,$token);
	return $memberInfos;
}

function checkConnection($key,$token){
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