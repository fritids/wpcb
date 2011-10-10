<?php
/*
Plugin Name:WP e-Commerce Atos SIPS
Plugin URI: http://wpcb.fr
Description: Plugin de paiement par CB ATOS SIPS (Mercanet,...) (Plugin requis : WP e-Commerce)
Version: 1.0.4
Author: 6WWW
Author URI: http://6www.net
*/

define('__WPRoot__',dirname(dirname(dirname(dirname(__FILE__)))));
define('__ServerRoot__',dirname(dirname(dirname(dirname(dirname(__FILE__))))));

if (!class_exists('atosLoader')) {
	class atosLoader {
		function load() {
			register_activation_hook( __file__, array(&$this, 'activate' ));
			register_deactivation_hook( __file__, array(&$this, 'deactivate' ));
			if(get_option('atos_msg')) {
				add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>'.get_option('atos_msg').'</strong></p></div>\';') );
				delete_option('atos_msg');
			}
		}
		// activate the plugin
		function activate() {
			$wpecommercePluginDir = dirname(dirname(__file__)).'/wp-e-commerce';
			
			$wpcb_Dir = dirname(__file__);
			if(get_option('wpsc_version')){
				if(floatval(get_option('wpsc_version'))>3.7){
					$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/wpsc-merchants';
				} else {
					$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/merchants';
				}
			} else {
				$pluginDir = dirname(dirname(__file__)) . '/wp-e-commerce/merchants';
			}
			$sourceFile = $wpcb_Dir . '/atos.merchant.php';
			$destinationFile = $pluginDir . '/atos.merchant.php';
			
			// Copy the file to the WP e-Commerce merchants folder
			if(file_exists($pluginDir))
			{
				@copy($sourceFile, $destinationFile);
					if(!file_exists($destinationFile))
					{
						if(get_option('atos_msg'))
						{
							update_option('atos_msg', '<strong>WP e-Commerce WPCB :</strong> Please copy atos.merchant.php manually to wp-e-commerce/merchants.');
						} else {
							add_option('atos_msg', '<strong>WP e-Commerce WPCB :</strong> Please copy atos.merchant.php manually to wp-e-commerce/merchants.');
						}
					}
					else {
						// Copy the pointer outside the folder :
						$sourceFile = $wpcb_Dir . '/Pointeur_automatic_response.php';
						$destinationFile = __WPRoot__.'/Pointeur_automatic_response.php';
						
						@copy($sourceFile, $destinationFile);
						if(!file_exists($destinationFile)) {
							if(get_option('atos_msg')) {
								update_option('atos_msg', '<strong>WP e-Commerce WPCB :</strong> Please copy Pointeur_automatic_response.php manually to '.$destinationFile.' .');
								} 
								else {
									add_option('atos_msg', '<strong>WP e-Commerce WPCB :</strong> Please copy simple-paypal.merchant.php manually to '.$destinationFile.' .');							
								}
						}
						else {
							// Set default values for options :
						update_option('atos_merchantid','005009461440411'); 
						update_option('atos_normal_return_url',site_url());
						update_option('atos_cancel_return_url',site_url());
						update_option('atos_gateway_image',plugins_url('wpcb/logo/LogoMercanetBnpParibas.gif'));
						update_option('atos_pathfile',__ServerRoot__.'/cgi-bin/pathfile');
						update_option('atos_path_bin',__ServerRoot__.'/cgi-bin/request');
						update_option('atos_path_bin_response',__ServerRoot__.'/cgi-bin/response');
						update_option('atos_logfile',__ServerRoot__.'/cgi-bin/logfile.txt');
						update_option('atos_test','off');
						update_option('atos_advert','advert.jpg');
						update_option('atos_logo_id2','logo_id2.jpg');
						update_option('atos_payment_means','CB,2,VISA,2,MASTERCARD,2');
						update_option('atos_debug','on');
						}
					}
					
			} else {
				if(get_option('atos_msg'))
				{
					update_option('atos_msg', "WP e-Commerce wasn't found, please install it first.");
				} else {
					add_option('atos_msg', "WP e-Commerce wasn't found, please install it first.");
				}
			}
			
		} // end of function activate
		/**
		* deactivate the plugin
		*/
		function deactivate() {
			// Supprimer le pointeur de la racine de Wordpress
			unlink( __WPRoot__.'/PointeurPointeur_automatic_response.php');
			// Supprimer les options enregistrées par le plugin
			delete_option('atos_merchantid');
			delete_option('atos_normal_return_url');
			delete_option('atos_cancel_return_url');
			delete_option('atos_gateway_image');
			delete_option('atos_pathfile');
			delete_option('atos_path_bin');
			delete_option('atos_path_bin_response');
			delete_option('atos_logfile');
			delete_option('atos_test');
			delete_option('atos_msg');
			delete_option('atos_advert');
			delete_option('atos_logo_id2');
			delete_option('atos_payment_means');
			delete_option('atos_debug');
		}
	}
	$atosLoad = new atosLoader();
	$atosLoad->load();
}
