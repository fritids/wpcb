<?php
/*
Plugin Name: WP e-Commerce Livraison France
Plugin URI: http://wpcb.fr/plugin-livraison-france
Description: Livraison depuis la France vers la France et l'étranger (Plugin requis : WP e-Commerce)
Version: 1.1.1
Author: 6WWW
Author URI: http://6www.net
*/


class postefrancaise {
	var $internal_name, $name;
	var $services = array();
	var $settings;
	var $base_country;
	var $base_zipcode;
	
	//  Constructor
	function postefrancaise () {
		$this->internal_name = "wpsc_postefrancaise";
		$this->name = 'Poste Française';
		$this->is_external = true;
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->debug = false; // change to true to log (to the PHP error log) the API URLs and responses for each active service
		
		// Initialise the list of available postage services
		$this->services['COLIS'] = __('Colis', 'wpsc');
		$this->services['CHRONOPOST'] = __('Chronopost', 'wpsc');
		$this->services['ENLEVEMENT'] = __('Enlèvement sur place', 'wpsc');
		$this->services['LETTREMAX'] = __('Lettre MAX', 'wpsc');
		
		// Attempt to load the existing settings
		$this->settings = get_option("wpsc_postefrancaise_settings");
		$this->base_country = get_option('base_country');
		$this->base_zipcode = get_option('base_zipcode');
		
		if (!$this->settings) {
			// Initialise the settings with defaults values:
			$this->settings = array();
			$this->settings['services']['COLIS'] = true;
			$this->settings['services']['CHRONOPOST'] = true;
			$this->settings['services']['ENLEVEMENT'] = true;
			$this->settings['services']['LETTREMAX'] = true;
			$this->settings['address'] = 'Centre Ville de Lyon, France';
			$this->settings['days_to_france']['COLIS'] = 4;
			$this->settings['days_to_OM1']['COLIS'] = 8;
			$this->settings['days_to_OM2']['COLIS'] = 12;
			$this->settings['days_to_international_zone_A']['COLIS'] = 15;
			$this->settings['days_to_international_zone_B']['COLIS'] = 15;
			$this->settings['days_to_international_zone_C']['COLIS'] = 15;
			$this->settings['days_to_international_zone_D']['COLIS'] = 15;
			$this->settings['days_to_france']['CHRONOPOST'] = 0.5;
			$this->settings['days_to_zone_1']['CHRONOPOST'] = 3;
			$this->settings['days_to_zone_2']['CHRONOPOST'] = 3;
			$this->settings['days_to_zone_3']['CHRONOPOST'] = 3;
			$this->settings['tarif_lettre']['COLIS'] = false;
			$this->settings['type_tarif_lettre']['COLIS'] = 'prioritaire';
			update_option('wpsc_postefrancaise_settings', $this->settings);
		}
		
		return true;
	} // end constructor
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		$output = '';
		if ($this->base_country != 'FR') {
			return __('Ne fonctionne que pour un commerçant basé en France.', 'wpsc');
		}
		// base_zipcode should be given and equal to 5 (french zipcode)
		if (strlen($this->base_zipcode) != 5) {
			return __('Entrer votre code postal plus haut sur cette page.', 'wpsc');
		}
		// Load the values :
		// Create the admin form
		$output.='<tr><td>';
		$output.='</td></tr>';
		$output .= "<tr><td><p>Adresse physique :</p></td></tr><tr><td><input type='text' name='wpsc_postefrancaise_settings[address]' value='".$this->settings['address']."'></td></tr>";
		$output .= "<tr><td>" . __('Choisissez les services que vous voulez proposer:', 'wpsc') . "</td></tr>";
		// Colis :
		$output .= "<tr><td><label><input type='checkbox' ";
		if ($this->settings['services']['COLIS']=='on'){
			$output.=" checked='checked' ";
		}
		$output.=" name='wpsc_postefrancaise_settings[services][COLIS]'/>".$this->services['COLIS']."</label></td></tr>";
		// CHRONOPOST
		$output .= "<tr><td><label><input type='checkbox' ";
		if ($this->settings['services']['CHRONOPOST']=='on'){
			$output.=" checked='checked' ";
		}
		$output.=" name='wpsc_postefrancaise_settings[services][CHRONOPOST]'/>".$this->services['CHRONOPOST']."</label></td></tr>";
		// Enlà¨vement sur place :
		$output .= "<tr><td><label><input type='checkbox' ";
		if ($this->settings['services']['ENLEVEMENT']=='on'){
			$output.=" checked='checked' ";
		}
		$output.=" name='wpsc_postefrancaise_settings[services][ENLEVEMENT]'/>".$this->services['ENLEVEMENT']."</label></td></tr>";	
		
		$output.="<tr><td><label>Délais de livraison vers la France pour les colis standards (colissimo) :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_france][COLIS]' value='".$this->settings[days_to_france][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'outre zone OM1 pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_OM1][COLIS]' value='".$this->settings[days_to_OM1][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'outre zone OM2 pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_OM2][COLIS]' value='".$this->settings[days_to_OM2][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone A pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_international_zone_A][COLIS]' value='".$this->settings[days_to_international_zone_A][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone B pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_international_zone_B][COLIS]' value='".$this->settings[days_to_international_zone_B][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone C pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_international_zone_C][COLIS]' value='".$this->settings[days_to_international_zone_C][COLIS]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone D pour les Colis :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_international_zone_D][COLIS]' value='".$this->settings[days_to_international_zone_D][COLIS]."'/></td></tr>";
		// Option tarif lettre :
		$output .= "<tr><td><label><input type='checkbox' ";
		if ($this->settings['tarif_lettre']['COLIS']){
			$output.=" checked='checked' ";
		}
		$output.=" name='wpsc_postefrancaise_settings[tarif_lettre][COLIS]'/>Utiliser le tarif <strong>lettre</strong> pour les colis < 3k g</label></td></tr>";
		// 2 choix : lettre verte ou lettre prioritaire :
		$output.='<tr><td><INPUT type="radio" name="wpsc_postefrancaise_settings[type_tarif_lettre][COLIS]" value="prioritaire"';
		if ($this->settings['type_tarif_lettre']['COLIS']=='prioritaire'){
			$output.=' checked="checked" ';
		}
		$output.="> Lettre Prioritaire</input> ";
		$output.='<INPUT type="radio" name="wpsc_postefrancaise_settings[type_tarif_lettre][COLIS]" value="verte"';
		if ($this->settings['type_tarif_lettre']['COLIS']=='verte'){
			$output.=' checked="checked" ';
		}
		$output.="> Lettre Verte</input></td></tr>";
		// CHRONOPOST
		
		$output.="<tr><td><label>Délais de livraison vers la France métropolitaine et monaco pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_france][CHRONOPOST]' value='".$this->settings[days_to_france][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 1 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_1][CHRONOPOST]' value='".$this->settings[days_to_zone_1][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 2 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_2][CHRONOPOST]' value='".$this->settings[days_to_zone_2][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 3 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_3][CHRONOPOST]' value='".$this->settings[days_to_zone_3][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 4 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_4][CHRONOPOST]' value='".$this->settings[days_to_zone_4][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 5 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_5][CHRONOPOST]' value='".$this->settings[days_to_zone_5][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 6 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_6][CHRONOPOST]' value='".$this->settings[days_to_zone_6][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 7 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_7][CHRONOPOST]' value='".$this->settings[days_to_zone_7][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 8 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_8][CHRONOPOST]' value='".$this->settings[days_to_zone_8][CHRONOPOST]."'/></td></tr>";
		$output.="<tr><td><label>Délais de livraison vers l'international zone 9 pour les colis chronopost :</label></td></tr>";
		$output.="<tr><td><input type='text' name='wpsc_postefrancaise_settings[days_to_zone_9][CHRONOPOST]' value='".$this->settings[days_to_zone_9][CHRONOPOST]."'/></td></tr>";
		
		$output .= "<tr><td><label><input type='checkbox' ";
		if ($this->settings['tarif_pretaexpedier']['CHRONOPOST']){
			$output.=" checked='checked' ";
		}
		$output.=" name='wpsc_postefrancaise_settings[tarif_pretaexpedier][CHRONOPOST]'/>Utiliser les produits <strong>prêt-à-expédier</strong> pour les colis chronopost <6kg -> </label></td></tr>";
		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][enveloppe_document][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['enveloppe_document']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Enveloppe 500g et 1kg (documents uniquement)</input> ";
		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][pochette_gonflable][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['pochette_gonflable']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Pochette gonflable 1kg et 2kg</input> ";
		$output.="> Enveloppe 500g et 1kg (documents uniquement)</input> ";
		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][boite][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['boite']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Boîte 3kg et 6kg</input> ";
		// UE : 
		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][enveloppe_document_ue][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['enveloppe_document_ue']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Enveloppe 500g Union Européenne (documents uniquement)</input> ";
		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][pochette_gonflable_ue][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['pochette_gonflable_ue']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Pochette gonflable 2kg Union Européenne</input> ";

		$output.='<tr><td><INPUT type="checkbox" name="wpsc_postefrancaise_settings[tarif_pretaexpedier][boite_ue][CHRONOPOST]" ';
		if ($this->settings['tarif_pretaexpedier']['boite_ue']['CHRONOPOST']){
			$output.=' checked="checked" ';
		}
		$output.="> Boîte 6kg Union Européene</input> ";



		// Autres options :
		$output.= "<input type='hidden' name='wpsc_postefrancaise_settings_updateoptions' value='true'>";
		$output.= "</td></tr>";
		$output.= "<tr><td><h4>" . __('Notes:', 'wpsc') . "</h4>";
		$output.= '1. Lacheteur aura le choix de la méthode de livraison au moment de régler son panier.<br />';
		$output.= '2. Le poids de chaque produit doit être renseigné.<br />';
		$output.= '3. Les dimensions de chaque produit doivent àªtre renseignées (optionel)<br />';
		$output.= "</tr></td>";
		$output.= "<tr><td><h4>" . __('Tarifs:', 'wpsc') . "</h4>";
		$output.= '<a href="http://www.laposte.fr/content/download/12782/102894/file/fm-resume-tarifs-particuliers-2011d_10_01.pdf?espace=particulier" target="_blank">Lettre</a> | <a href="http://www.laposte.fr/content/download/9317/67273/file/Métropole%20BP.pdf?espace=particulier" target="_blank">Colis</a> | <a href="http://www.chronopost.fr/transport-express/webdav/site/chronov4/users/chronopost/public/pdf/Tarifs_et_VPC/Tarifs_guichet.pdf" target="_blank">Chronopost</a>';
		$output.= "</tr></td>";
		
		return $output;
	} // End of getForm function
	
	function submit_form() {
		$this->settings['services'] = array();

		// Only continue if this module's options were updated
		if ( !isset($_POST["wpsc_postefrancaise_settings_updateoptions"]) || !$_POST["wpsc_postefrancaise_settings_updateoptions"] ) return;
		
		// Save the settings :
		$this->settings['services']['COLIS'] = $_POST['wpsc_postefrancaise_settings']['services']['COLIS'];
		$this->settings['services']['CHRONOPOST'] = $_POST['wpsc_postefrancaise_settings']['services']['CHRONOPOST'];					
		$this->settings['services']['ENLEVEMENT'] = $_POST['wpsc_postefrancaise_settings']['services']['ENLEVEMENT'];
		$this->settings['address']=$_POST['wpsc_postefrancaise_settings']['address'];
		$this->settings['tarif_lettre']['COLIS']=$_POST['wpsc_postefrancaise_settings']['tarif_lettre']['COLIS'];
		$this->settings['type_tarif_lettre']['COLIS']=$_POST['wpsc_postefrancaise_settings']['type_tarif_lettre']['COLIS'];
		$this->settings['days_to_france']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_france']['COLIS'];
		$this->settings['days_to_OM1']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_OM1']['COLIS'];
		$this->settings['days_to_OM2']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_OM2']['COLIS'];
		$this->settings['days_to_international_zone_A']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_international_zone_A']['COLIS'];
		$this->settings['days_to_international_zone_B']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_international_zone_B']['COLIS'];
		$this->settings['days_to_international_zone_C']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_international_zone_C']['COLIS'];
		$this->settings['days_to_international_zone_D']['COLIS']=$_POST['wpsc_postefrancaise_settings']['days_to_international_zone_D']['COLIS'];
		$this->settings['days_to_france']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_france']['CHRONOPOST'];
		$this->settings['days_to_zone_1']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_1']['CHRONOPOST'];
		$this->settings['days_to_zone_2']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_2']['CHRONOPOST'];
		$this->settings['days_to_zone_3']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_3']['CHRONOPOST'];
		$this->settings['days_to_zone_4']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_4']['CHRONOPOST'];
		$this->settings['days_to_zone_5']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_5']['CHRONOPOST'];
		$this->settings['days_to_zone_6']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_6']['CHRONOPOST'];
		$this->settings['days_to_zone_7']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_7']['CHRONOPOST'];
		$this->settings['days_to_zone_8']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_8']['CHRONOPOST'];
		$this->settings['days_to_zone_9']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['days_to_zone_9']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['enveloppe_document']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['enveloppe_document']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['pochette_gonflable']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['pochette_gonflable']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['boite']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['boite']['CHRONOPOST'];
		// Union européenne prêt-a-expédier :
		$this->settings['tarif_pretaexpedier']['enveloppe_document_ue']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['enveloppe_document_ue']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['pochette_gonflable_ue']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['pochette_gonflable_ue']['CHRONOPOST'];
		$this->settings['tarif_pretaexpedier']['boite_ue']['CHRONOPOST']=$_POST['wpsc_postefrancaise_settings']['tarif_pretaexpedier']['boite_ue']['CHRONOPOST'];
		
		// Save to db :
		update_option('wpsc_postefrancaise_settings',$this->settings);
		
			
		return true;
	} // End of submit_form function
	
	function getQuote() {
		global $wpdb, $wpsc_cart;
		$wpsc_postefrancaise_settings= get_option("wpsc_postefrancaise_settings");

		if ($this->base_country != 'FR' || strlen($this->base_zipcode) != 5 || !count($wpsc_cart->cart_items)) return;
		$dest = $_SESSION['wpsc_delivery_country'];
		$destzipcode = '';
		if(isset($_POST['zipcode'])) {
			$destzipcode = $_POST['zipcode'];      
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		} 
		else if(isset($_SESSION['wpsc_zipcode'])) {
			$destzipcode = $_SESSION['wpsc_zipcode'];
		}
		if ($dest == 'FR' && strlen($destzipcode) != 5) {return array();}

		/*
		3 possible scenarios:
		1. Cart consists of only item(s) that have "disregard shipping" ticked.
		In this case, WPEC doesn't mention shipping at all during checkout, and this shipping module probably won't be executed at all.
		Just in case it does get queried, we should still override the quoted price(s) to $0.00 so the customer is able to get free shipping.
		2. Cart consists of only item(s) where "disregard shipping" isn't ticked (ie. all item(s) attract shipping charges).
		In this case, we should query the quote as per normal.
		3. Cart consists of one or more "disregard shipping" product(s), and one or more other products that attract shipping charges.
		In this case, we should query the quote, only taking into account the product(s) that attract shipping charges.
		Products with "disregard shipping" ticked shouldn't have their weight or dimensions included in the quote.
		*/
		

		// Weight is in grams
		$weight = wpsc_convert_weight($wpsc_cart->calculate_total_weight(true), 'pound', 'gram');
		// Calculate the total cart dimensions by adding the volume of each product then calculating the cubed root
		$volume = 0;
		// Total number of item(s) in the cart
		$numItems = count($wpsc_cart->cart_items);

		if ($numItems == 0) {
		    // The customer's cart is empty. This probably shouldn't occur, but just in case!
		    return array();
		}

		// Total number of item(s) that don't attract shipping charges.
		$numItemsWithDisregardShippingTicked = 0;

		foreach($wpsc_cart->cart_items as $cart_item) {
			if ( !$cart_item->uses_shipping ) {
			    // The "Disregard Shipping for this product" option is ticked for this item.
			    // Don't include it in the shipping quote.
			    $numItemsWithDisregardShippingTicked++;
			    continue;
			}

			// If we are here then this item attracts shipping charges.
			$meta = get_product_meta($cart_item->product_id,'product_metadata',true);
			$meta = $meta['dimensions'];

			if ($meta && is_array($meta)) {
				$productVolume = 1;
				foreach (array('width','height','length') as $dimension) {
					// Cubi square of the dimension to get the volume of the box it will be squared later
					switch ($meta["{$dimension}_unit"]) {
						// we need the units in mm
						case 'cm':
							// convert from cm to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 10);
							break;
						case 'meter':
							// convert from m to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 1000);
							break;
						case 'in':
							// convert from in to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 25.4);
							break;
					}
				}
				$volume += floatval($productVolume);
			}
		}
		// Calculate the cubic root of the total volume, rounding up
		$cuberoot = ceil(pow($volume, 1 / 3));
		
		// Use default dimensions of 100mm if the volume is zero
		$height=100; // Mettre dans les options, todo
		$width=100;
		$length=100;
		
		if ($cuberoot > 0) {
		    $height = $width = $length = $cuberoot;
		}

		if ($length < 100) $length = 100;
		if ($width < 100) $width = 100;

		$shippingPriceNeedsToBeZero = false;
		
		if ($numItemsWithDisregardShippingTicked == $numItems) {
		    // The cart consists of entirely "disregard shipping" products, so the shipping quote(s) should be $0.00
		    // Set the weight to 1 gram so that we can obtain valid Australia Post quotes (which we will then ignore the quoted price of)
		    $weight = 1;
		    $shippingPriceNeedsToBeZero = true;
		}
		
		$params = array(
		    'Pickup_Postcode' => $this->base_zipcode
		    , 'Destination_Postcode' => $destzipcode
		    , 'Quantity' => 1
		    , 'Weight' => $weight
		    , 'Height' => $height
		    , 'Width' => $width
		    , 'Length' => $length
		    , 'Country' => $dest
		);

		// Tableaux des destinations COLIS :
		$dest_colis_fr=array('FR');
		$dest_colis_outre_mer_zone_1=array('GP','MQ','GY','RE','YT','PM');
		$dest_colis_outre_mer_zone_2=array('NC','PF','WF','TF');
		$dest_colis_international_zone_A=array('AD','AL','AM' ,'AT' ,'AX' ,'AZ' ,'BA' ,'BE' ,'BG' ,'BY' ,'CH' ,'CY' ,'CZ' ,'DE' ,'DK','EE','ES','FI','FO','FR','GB','GE','GG','GI','GR','HR','HU','IE' ,'IM','IS','IT','JE' ,'KZ' ,'LI' ,'LT' ,'LU' ,'LV','MC','MD','ME','MK','MT','NL','NO','PL','PT','RO','RS','RU','SE','SI','SJ','SK','SM','TR' ,'UA' ,'VA') ;
		$dest_colis_international_zone_B=array('DZ','MA','LY','TN','MR'); // +Europe de l'est to do
		$dest_colis_international_zone_C=array('CA','US'); // + Proche et moyen Orient + Afrique Hors Magreb todo
		$dest_colis_international_zone_D=array(); // + reste du monde todo
		
		// colis :
		$service_postefrancaise='COLIS';
		// Utilisation de l'api "home-made" (uniquement a destination de france métropolitaine)
		if (in_array($params['Country'],$dest_colis_fr)){
			// Tarifs à  destination de la france :
			$methods[$service_postefrancaise]['name']='Colis France métropolitaine et Monaco';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_france'][$service_postefrancaise];
			//Table tarifaire colis France Métropolitaine et Monaco
			if($params['Weight']>=0 && $params['Weight']<=20){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=0.60; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=0.57; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=5.60;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>20 && $params['Weight']<=50){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=1.00; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=0.95; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=5.60;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>50 && $params['Weight']<=100){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=1.45; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=1.40; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=5.60;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>100 && $params['Weight']<=250){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=2.40; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=2.30; 
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=5.60;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>250 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=3.25;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=3.10;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=5.60;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=4.20;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=4.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=6.95;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=5.50;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=5.25;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=7.95;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_lettre'][$service_postefrancaise]){
					// Tarif different si tarif_lettre coché :
					// 2 types de tarif lettre : tarif prioritaire et tarif verte :
					if ($wpsc_postefrancaise_settings['type_tarif_lettre'][$service_postefrancaise]=='prioritaire'){
						$methods[$service_postefrancaise]['charge']=6.40;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else { //verte
						$methods[$service_postefrancaise]['charge']=6.10;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else { // Pas de tarif lettre :
						$methods[$service_postefrancaise]['charge']=8.95;
						$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=10.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=12.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=15.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=18.20;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=30000){
				$methods[$service_postefrancaise]['charge']=24.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids non trouvé';
			}
		} // Fin des tarifs France Monaco
		elseif (in_array($params['Country'],$dest_colis_outre_mer_zone_1)){
			// Tarifs à  destination de la zone outre mer 1
			$methods[$service_postefrancaise]['name']='Colis Outre-Mer Zone 1';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_OM1'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				$methods[$service_postefrancaise]['charge']=8.45;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>500 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=12.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=17.35;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=22.00;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=26.65;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=31.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=35.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=40.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=45.25;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=49.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=54.55;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=77.75;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=100.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>20000 && $params['Weight']<=25000){
				$methods[$service_postefrancaise]['charge']=124.15;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>25000 && $params['Weight']<=30000){
				$methods[$service_postefrancaise]['charge']=147.35;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids non trouvé';
				}
		} // Fin de zone Colis outre mer zone 1
		// Tarifs à  destination de la zone outre mer 2
		elseif (in_array($params['Country'],$dest_colis_outre_mer_zone_2)){
			$methods[$service_postefrancaise]['name']='Colis Outre-Mer Zone 2';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_OM2'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				$methods[$service_postefrancaise]['charge']=10.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>500 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=15.20;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=26.80;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=38.40;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=50.00;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=61.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=73.20;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=84.80;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=96.40;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=108.00;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=119.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=177.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=235.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>20000 && $params['Weight']<=25000){
				$methods[$service_postefrancaise]['charge']=293.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>25000 && $params['Weight']<=30000){
				$methods[$service_postefrancaise]['charge']=351.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids > 30 kg impossible';
				}
		} // Fin de zone Colis outre mer zone 2
		elseif (in_array($params['Country'],$dest_colis_international_zone_A) ){
			$methods[$service_postefrancaise]['name']='Colis International Zone A';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_international_zone_A'][$service_postefrancaise];
		// Zone Internationale A
			if($params['Weight']>=0 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=16.15;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=17.85;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=21.55;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=25.25;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=28.95;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=32.65;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=36.35;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=40.05;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=43.75;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=47.45;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=54.65;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=61.85;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>20000 && $params['Weight']<=25000){
				$methods[$service_postefrancaise]['charge']=69.05;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>25000 && $params['Weight']<=30000){
				$methods[$service_postefrancaise]['charge']=76.25;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {$methods[$service_postefrancaise]['err_msg']='Poids > 30 kg impossible';}
		} // Fin zone A
		elseif (in_array($params['Country'],$dest_colis_international_zone_B)) { // + Europe de l'est todo.
			$methods[$service_postefrancaise]['name']='Colis International Zone B';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_international_zone_B'][$service_postefrancaise];
		// Zone Internationale B
			if($params['Weight']>=0 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=19.80;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=21.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=26.25;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=30.80;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=35.35;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=39.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=44.45;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=49.00;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=53.55;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=58.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=68.50;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=78.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids > 20 kg impossible';
			}
		} // Fin international zone B
		// Zone Internationale C
		elseif (in_array($params['Country'],$dest_colis_international_zone_C)){
			$methods[$service_postefrancaise]['name']='Colis International Zone C';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_international_zone_C'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=23.20;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=31.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=40.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=50.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=60.50;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=70.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=80.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=89.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=99.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=109.50;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=133.60;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=157.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids > 20 kg impossible';
			}
		}// Fin international zone C
		else { // Tarif internationaux colis Zone D
			$methods[$service_postefrancaise]['name']='Colis International Zone D';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_international_zone_D'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=1000){
				$methods[$service_postefrancaise]['charge']=26.40;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				$methods[$service_postefrancaise]['charge']=39.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				$methods[$service_postefrancaise]['charge']=52.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=4000){
				$methods[$service_postefrancaise]['charge']=66.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>4000 && $params['Weight']<=5000){
				$methods[$service_postefrancaise]['charge']=79.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				$methods[$service_postefrancaise]['charge']=92.50;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=7000){
				$methods[$service_postefrancaise]['charge']=105.70;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>7000 && $params['Weight']<=8000){
				$methods[$service_postefrancaise]['charge']=118.90;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>8000 && $params['Weight']<=9000){
				$methods[$service_postefrancaise]['charge']=132.10;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>9000 && $params['Weight']<=10000){
				$methods[$service_postefrancaise]['charge']=145.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
				$methods[$service_postefrancaise]['charge']=171.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
				$methods[$service_postefrancaise]['charge']=197.30;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids > 20 kg impossible';
			}
		} // Fin de international zone D
		// **********************************************************************************
		// CHRONOPOST (jusqu'à  zone 3 pour le moment)
		$dest_chronopost_fr=array('FR');
		$dest_chronopost_zone_1=array('DE','BE','LU','NL');
		$dest_chronopost_zone_2=array('AT','DE','ES','FI','GB','GR','IE','IT','PT','SE');
		$dest_chronopost_zone_3=array('BU','CY','EE','HU','LV','LT','MT','PL','CZ','RO','SK','SI');
		$dest_chronopost_zone_4=array('AL','AD','AM','BY','BA','IC','HR','FO','GE','GI','GS','IS','JE','LI','MK','MD','ME','NO','RU','SM','RS','CH','TR','UA'); // Europe hors UE
		$dest_chronopost_zone_5=array('AI','AG','AR','AW','BS','BB','BZ','BM','BO','BR','CA','KY','CL','CO','CR','CU','AN','DO','DM','SV','EC','US','GD','GL','GT','GY','HT','HN','JM','MX','MS','NI','PA','PY','PE','PR','AN','KN','VC','VI','LC','SR','TT','TC','UY','VE','VI','VG'); // Amerique
		$dest_chronopost_zone_6=array('ZA','DZ','AO','SA','AZ','BH','BI','BW','BF','CM','CV','CF','KM','CG','CD','CI','DJ','EG','AE','ER','ET','GA','GM','GH','GN','GW','GQ','IQ','IR','IL','JO','KE','KW','LS','LB','LR','LY','MG','MW','ML','MA','MU','MR','MZ','NA','NE','NG','OM','UG','PS','QA','RW','ST','SN','SC','SL','SO','SD','SZ','SY','TZ','TD','TG','TN','YE','ZM','ZW'); // Afrique moyen orient
		$dest_chronopost_zone_7=array('AF','AU','BD','BT','BN','KH','CN','CX','CC','CK','KR','FJ','GU','HK','IN','ID','JP','KZ','KG','KI','LA','MO','MY','MV','MH','FM','MN','MM','NR','NP','NF','NZ','UZ','PK','PW','PG','PH','MP','SB','WS','AS','SG','LK','TK','TW','TH','TL','TO','TM','TV','VU','VN',); // Asie Océanie
		$dest_chronopost_zone_8=array('GP','MQ','RE'); 
		$dest_chronopost_zone_9=array('GY','YT','NC','PF','PM','WF'); // 
		
		
		$service_postefrancaise='CHRONOPOST';
		if (in_array($params['Country'],$dest_chronopost_fr)){
			$methods[$service_postefrancaise]['name']='Chrono 13 France métropolitaine et Monaco';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_france'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=22.50;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_document'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=23.50;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						$methods[$service_postefrancaise]['err_msg']='Méthode non connue';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=33.52;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=23.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=23.50;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						$methods[$service_postefrancaise]['err_msg']='Méthode non connue';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=33.52;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>1000 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=25.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=33.52;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=33.52;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>2000 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=27.01;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=38.31;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=38.31;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>3000 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=30.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=38.31;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=38.31;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>5000 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=30.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=46.99;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=46.99;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif ($params['Weight']>6000 && $params['Weight']<=10000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=46.99;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>10000 && $params['Weight']<=15000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=55.69;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>15000 && $params['Weight']<=20000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=64.36;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>20000 && $params['Weight']<=25000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=73.04;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif ($params['Weight']>25000 && $params['Weight']<=30000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=81.73;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			else {
				$methods[$service_postefrancaise]['err_msg']='Poids trop élevé pour chronopost';
			}
		} // Fin de chronopost France
		elseif (in_array($params['Country'],$dest_chronopost_zone_1)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 1';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_1'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=46.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=47.07;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=47.07;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=53.25;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=53.25;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=59.42;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=59.42;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=65.59;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=65.59;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=71.76;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=71.76;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=77.93;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=77.93;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=84.10;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=84.10;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=90.27;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=90.27;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=96.45;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=96.45;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=102.62;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=102.62;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=108.79;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=108.79;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=114.96;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=114.96;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=121.13;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=127.30;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=133.47;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=139.64;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=145.82;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=151.99;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=158.16;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=164.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*3.59;//todo : round sup
				$methods[$service_postefrancaise]['charge']=164.33+$TarifSup;
				$methods[$service_postefrancaise]['err_msg']='OK';

			}
		} // Fin de chronopost zone 1
		elseif (in_array($params['Country'],$dest_chronopost_zone_2)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 2';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_2'][$service_postefrancaise];
if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=46.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=51.81;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=51.81;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=58.56;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=58.56;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=65.30;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=65.30;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=72.05;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=72.05;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=78.79;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=78.79;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=85.54;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=85.54;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=92.28;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=92.28;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=99.03;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=99.03;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=105.77;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=105.77;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=112.52;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=112.52;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=119.27;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=119.27;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=126.01;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=126.01;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=132.76;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=139.50;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=146.25;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=152.99;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=159.74;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=166.48;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=173.23;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=179.97;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*4.88;//todo : round sup
				$methods[$service_postefrancaise]['charge']=179.97+$TarifSup;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
		} // Fin de chronopost zone 2
		elseif (in_array($params['Country'],$dest_chronopost_zone_3)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 3';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_3'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=46.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=96.77;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=96.77;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=114.23;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=114.23;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=131.69;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=131.69;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=57.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=149.15;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=149.15;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=166.61;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=166.61;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=184.08;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=184.08;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=196.87;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=196.87;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=209.67;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=209.67;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=222.47;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=222.47;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=235.27;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=235.27;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=248.06;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=248.06;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_ue'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=88.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=260.86;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=260.86;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=273.66;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=286.45;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=299.25;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=312.05;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=324.85;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=337.64;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=350.44;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=363.24;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*10.52;//todo : round sup
				$methods[$service_postefrancaise]['charge']=363.24+$TarifSup;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
		} // Fin de chronopost zone 3
		// OUTRE MER ZONE 8
		elseif (in_array($params['Country'],$dest_chronopost_zone_8)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 8 (Outre-mer)';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_8'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=78.01;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=78.01;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=88.27;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=88.27;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=98.54;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=98.54;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=108.80;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=108.80;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=119.06;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=119.06;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=129.33;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=129.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=140.33;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=140.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=144.83;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=144.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=149.33;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=149.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=153.83;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=153.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=158.33;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=158.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
					else {
						//Tarif chronopost emballé par vos soins
						$methods[$service_postefrancaise]['charge']=162.83;
						$methods[$service_postefrancaise]['err_msg']='OK';
					}
				}
				else{
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=162.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=167.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=171.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=176.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=180.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=185.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=189.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=194.33;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					//Tarif chronopost emballé par vos soins
					$methods[$service_postefrancaise]['charge']=198.83;
					$methods[$service_postefrancaise]['err_msg']='OK';
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*6.60;//todo : round sup
				$methods[$service_postefrancaise]['charge']=198.83+$TarifSup;
				$methods[$service_postefrancaise]['err_msg']='OK';
			}
		} // Fin de chronopost zone 8
		// OUTRE MER ZONE 9
		elseif (in_array($params['Country'],$dest_chronopost_zone_9)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 9 (Outre-mer)';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_9'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=92.38;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=92.38;
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=114.60;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=114.60;
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=136.81;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=136.81;
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=159.03;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=159.03;
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=181.25;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=181.25;
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=203.47;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=203.47;
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=227.38;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=227.38;
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=242.79;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=242.79;
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=258.20;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=258.20;
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=273.61;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=273.61;
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=287.87;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=287.87;
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=302.13;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=302.13;
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					$methods[$service_postefrancaise]['charge']=316.39;
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					$methods[$service_postefrancaise]['charge']=330.65;
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					$methods[$service_postefrancaise]['charge']=344.91;
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					$methods[$service_postefrancaise]['charge']=359.17;
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					$methods[$service_postefrancaise]['charge']=373.43;
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					$methods[$service_postefrancaise]['charge']=387.69;
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					$methods[$service_postefrancaise]['charge']=401.95;
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					$methods[$service_postefrancaise]['charge']=416.21;
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*16.68;//todo : round sup
				$methods[$service_postefrancaise]['charge']=416.21+$TarifSup;
			}
			$methods[$service_postefrancaise]['err_msg']='OK';
		} // Fin de chronopost zone 9
		// RESTE DU MONDE Zone 4
		elseif (in_array($params['Country'],$dest_chronopost_zone_4)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 4';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_4'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=68.71;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=68.71;
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=77.22;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=77.22;
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=85.73;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=85.73;
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=94.24;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=94.24;
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=102.75;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=102.75;
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=111.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=111.26;
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=125.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=125.26;
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=132.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=132.26;
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=139.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=139.26;
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=146.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=146.26;
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=152.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=152.26;
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=158.26;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=158.26;
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					$methods[$service_postefrancaise]['charge']=164.26;
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					$methods[$service_postefrancaise]['charge']=168.26;
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					$methods[$service_postefrancaise]['charge']=172.26;
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					$methods[$service_postefrancaise]['charge']=176.26;
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					$methods[$service_postefrancaise]['charge']=180.26;
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					$methods[$service_postefrancaise]['charge']=184.26;
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					$methods[$service_postefrancaise]['charge']=188.26;
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					$methods[$service_postefrancaise]['charge']=192.26;
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*4.32;//todo : round sup
				$methods[$service_postefrancaise]['charge']=192.26+$TarifSup;
			}
			$methods[$service_postefrancaise]['err_msg']='OK';
		} // Fin de chronopost zone 4
		// RESTE DU MONDE Zone 5
		elseif (in_array($params['Country'],$dest_chronopost_zone_5)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 5';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_5'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=78.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=78.01;
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=88.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=88.01;
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=98.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=98.01;
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=108.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=108.01;
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=118.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=118.01;
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=128.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=128.01;
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=141.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=141.01;
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=145.51;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=145.51;
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=150.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=150.01;
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=154.51;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=154.51;
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=159.01;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=159.01;
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=163.51;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=163.51;
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					$methods[$service_postefrancaise]['charge']=168.01;
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					$methods[$service_postefrancaise]['charge']=172.51;
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					$methods[$service_postefrancaise]['charge']=177.01;
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					$methods[$service_postefrancaise]['charge']=181.51;
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					$methods[$service_postefrancaise]['charge']=186.01;
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					$methods[$service_postefrancaise]['charge']=190.51;
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					$methods[$service_postefrancaise]['charge']=195.01;
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					$methods[$service_postefrancaise]['charge']=199.51;
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*7.80;//todo : round sup
				$methods[$service_postefrancaise]['charge']=199.51+$TarifSup;
			}
			$methods[$service_postefrancaise]['err_msg']='OK';
		} // Fin de chronopost zone 5
		// RESTE DU MONDE Zone 6
		elseif (in_array($params['Country'],$dest_chronopost_zone_6)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 6';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_6'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=87.43;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=87.43;
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=104.82;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=104.82;
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=122.20;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=122.20;
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=139.59;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=139.59;
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=156.98;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=156.98;
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=174.37;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=174.37;
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=195.34;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=195.34;
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=205.81;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=205.81;
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=216.28;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=216.28;
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=226.75;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=226.75;
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=237.45;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=237.45;
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=248.15;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=248.15;
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					$methods[$service_postefrancaise]['charge']=258.85;
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					$methods[$service_postefrancaise]['charge']=269.55;
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					$methods[$service_postefrancaise]['charge']=280.25;
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					$methods[$service_postefrancaise]['charge']=290.95;
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					$methods[$service_postefrancaise]['charge']=301.65;
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					$methods[$service_postefrancaise]['charge']=312.35;
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					$methods[$service_postefrancaise]['charge']=323.05;
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					$methods[$service_postefrancaise]['charge']=333.75;
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*12.72;//todo : round sup
				$methods[$service_postefrancaise]['charge']=333.75+$TarifSup;
			}
			$methods[$service_postefrancaise]['err_msg']='OK';
		} // Fin de chronopost zone 6
				// RESTE DU MONDE Zone 7
		elseif (in_array($params['Country'],$dest_chronopost_zone_7)){
			$methods[$service_postefrancaise]['name']='Chrono Express International Zone 7';
			$methods[$service_postefrancaise]['days']=$this->settings['days_to_zone_7'][$service_postefrancaise];
			if($params['Weight']>=0 && $params['Weight']<=500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['enveloppe_document_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=58.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=92.02;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=92.02;
				}
			}	
			elseif($params['Weight']>500 && $params['Weight']<=1000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=110.37;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=110.37;
				}
			}
			elseif($params['Weight']>1000 && $params['Weight']<=1500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=128.72;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=128.72;
				}
			}
			elseif($params['Weight']>1500 && $params['Weight']<=2000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['pochette_gonflable_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=77.00;
					}
					elseif ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=147.08;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=147.08;
				}
			}
			elseif($params['Weight']>2000 && $params['Weight']<=2500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=165.43;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=165.43;
				}
			}
			elseif($params['Weight']>2500 && $params['Weight']<=3000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=183.79;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=183.79;
				}
			}
			elseif($params['Weight']>3000 && $params['Weight']<=3500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=205.83;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=205.83;
				}
			}
			elseif($params['Weight']>3500 && $params['Weight']<=4000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=216.87;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=216.87;
				}
			}
			elseif($params['Weight']>4000 && $params['Weight']<=4500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=227.91;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=227.91;
				}
			}
			elseif($params['Weight']>4500 && $params['Weight']<=5000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=238.95;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=238.95;
				}
			}
			elseif($params['Weight']>5000 && $params['Weight']<=5500){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=250.22;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=250.22;
				}
			}
			elseif($params['Weight']>5500 && $params['Weight']<=6000){
				if ($wpsc_postefrancaise_settings['tarif_pretaexpedier'][$service_postefrancaise]){
					if ($wpsc_postefrancaise_settings['tarif_pretaexpedier']['boite_om_rdm'][$service_postefrancaise]){	
						$methods[$service_postefrancaise]['charge']=150.00;
					}
					else {
						$methods[$service_postefrancaise]['charge']=261.49;
					}
				}
				else{
					$methods[$service_postefrancaise]['charge']=261.49;
				}
			}
			elseif($params['Weight']>6000 && $params['Weight']<=6500){
					$methods[$service_postefrancaise]['charge']=272.76;
			}
			elseif($params['Weight']>6500 && $params['Weight']<=7000){
					$methods[$service_postefrancaise]['charge']=284.03;
			}
			elseif($params['Weight']>7000 && $params['Weight']<=7500){
					$methods[$service_postefrancaise]['charge']=295.30;
			}
			elseif($params['Weight']>7500 && $params['Weight']<=8000){
					$methods[$service_postefrancaise]['charge']=306.57;
			}
			elseif($params['Weight']>8000 && $params['Weight']<=8500){
					$methods[$service_postefrancaise]['charge']=317.84;
			}
			elseif($params['Weight']>8500 && $params['Weight']<=9000){
					$methods[$service_postefrancaise]['charge']=329.11;
			}
			elseif($params['Weight']>9000 && $params['Weight']<=9500){
					$methods[$service_postefrancaise]['charge']=340.38;
			}
			elseif($params['Weight']>9500 && $params['Weight']<=10000){
					$methods[$service_postefrancaise]['charge']=351.65;
			}						
			else {
				$WeightSup=$params['Weight']-10000;
				$TarifSup=$WeightSup/0.5*13.32;//todo : round sup
				$methods[$service_postefrancaise]['charge']=351.65+$TarifSup;
			}
			$methods[$service_postefrancaise]['err_msg']='OK';
		} // Fin de chronopost zone 7
		else {
			// Reste du monde chronopost pas encore développé
			$methods[$service_postefrancaise]['err_msg']='Chronopost indisponible pour les zones 4 5 6 et 7 pour le moment';
		}
		// ENLEVEMENT
		$service_postefrancaise='ENLEVEMENT';
		$methods[$service_postefrancaise]['name']='Enlèvement sur place : '.$this->settings['address'];
		$methods[$service_postefrancaise]['charge']=0; // Ne coute rien
		$methods[$service_postefrancaise]['err_msg']='OK';
		
		
		//Essai API :
		$post_data['apiKey']=$wpcb_general['apiKey'];
		$post_data['emailapiKey']=$wpcb_general['emailapiKey'];
		$response=wp_remote_post('http://wpcb.fr/api/wpcb/valid.php',array('body' =>$post_data));
		$valid=unserialize($response['body']);
		if ($valid[0]){
			//Cle API valid, on peut aller chercher des tarifs speciaux // Pas necessaire car déjà vérifier après... à réfléchir
			$services=array('LETTREMAX');
			foreach ($services as $service){
			$post_data['service']=$service;
			$post_data['weight']='50';
			$response=wp_remote_post('http://wpcb.fr/api/wpcb/livraison/getQuote.php',array('body' =>$post_data)); // Validation de la clé a l'intérieur
			$methodsWPCB=unserialize($response['body']);
			//Add to the list :
			$methods[$service]='Lettre MAX'; // A faire passer en réglage
			$methods[$service]['charge']=$methodsWPCB['charge']; // Ne coute rien
			$methods[$service]['err_msg']='OK';
			}
		}
		else {
				//
		}
		
		// Allow another WordPress plugin to override the quoted method(s)/amount(s)
		$methods = apply_filters('wpsc_postefrancaise_methods', $methods, $this->base_zipcode, $destzipcode, $dest, $weight);
		
		$quotedMethods = array();
		
		// Debug :
		//$text = sprintf('Poids : %1$d grammes',$params['Weight']);
		//$quotedMethods[$text] = 1;
		
		
		foreach ($methods as $code => $data) {
			// Only include methods with an OK response
			if ($data['err_msg'] != 'OK') continue;
			// Only include methods that are checked in the admin :
			if (!$this->settings['services'][$code]) continue;
			if ($data['days']) {
				// If the estimated number of days is specified, so include it in the quote
				$text = sprintf('%1$s (temps de livraison estimé : %2$d jours ouvrables)', $data['name'], $data['days']);
			}
			else {
				// No time estimate
				$text = $data['name'];
			}
			$quotedMethods[$text] = $data['charge'];
		}
		return $quotedMethods;
	} // End of getQuote function
	
	function get_item_shipping() {} // don't delete
}

function postefrancaise_setup() {
	global $wpsc_shipping_modules;
	$postefrancaise = new postefrancaise();
	$wpsc_shipping_modules[$postefrancaise->getInternalName()] = $postefrancaise;
}

add_action('plugins_loaded', 'postefrancaise_setup');
?>