<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once("includes/phpqrcode/qrlib.php");


/*
Plugin Name: SEPA Girocode
Plugin URI:  http://www.halli-online.de/sepa-girocode/
Description: Create EPC-Codes (in Germany known as Girocode) for money transfer | Girocode-Barcode für SEPA-Überweisungen erstellen
Version:     0.5.1
Author:      Michael Hallmann
Author URI:  http://www.halli-online.de/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SEPA Girocode is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
SEPA Girocode is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with SEPA Girocode. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


class sepa_girocode_class {
  
private $data = array();

function __construct() {
	$this->data['iban'] = "";
	$this->data['bic'] = "";
	$this->data['beneficiary'] = "";
	$this->data['purpose'] = "";
	$this->data['amount'] = 0;
	$this->data['isclickable'] = 1;
	$this->data['purposecode'] = '';
	$this->data['reference'] = "";
	$this->data['dimension'] = 150;
	$this->data['frame'] = 1;
	$this->data['divclass'] = "";  // deprecated
	$this->data['style'] = '';
	$this->data['class'] = '';
	$this->data['alt'] = '';
	$this->data['title'] = '';

}

public function getValidAtts() {
	return $this->data;
}

public function __get( $property) {
	if (array_key_exists($property, $this->data)) 
		return $this->data["$property"];
	else
		throw new Exception( 'Can\'t get property ' . $property);
}


public function __set( $property, $value ) {
	if (! array_key_exists($property, $this->data)) {
		throw new Exception( 'Can\'t set property ' . $property);
	}

	
	$value = trim($value);

	switch( $property) {
		case "bic":
			$this->data["$property"] = strtoupper( substr( $value, 0, 11 ) );
			break;
		case "iban":
			$this->data["$property"] = strtoupper( $value );
			break;
		case "amount":
			$this->data["$property"] = floatval($value);
			break;
		case "purpose":
			$this->data["$property"] = substr($value, 0, 140);
			break;
		case "beneficiary":
			$this->data["$property"] = substr( $value, 0, 70 );
			break;
		case "reference":
			$this->data["$property"] = substr( $value, 0, 35 );
			break;
		case "purposecode":
			$this->data["$property"] = strtoupper(substr( $value, 0, 4 ));
			break;
		case "isclickable":
			$this->data["$property"] = floatval($value);
			break;
		case "frame":
			$this->data["$property"] = floatval($value);
			break;
		default:
			$this->data["$property"] = $value;
    }
	
	return true;
}

public function getGoogleUrl() {
	// https://developers.google.com/chart/infographics/docs/qr_codes
	// this function is currently not in use
	$nl = chr(13) . chr(10);

	$url  = "https://chart.googleapis.com/chart?";
	$url .= "cht=qr&";
	$url .= "chs=150x150&";
	$url .= "chld=M|4&";
	$url .= "chl=";

	return $url . urlencode( $this->getQrPayload() );
}


public function getQrPayload() {
	$nl = chr(13) . chr(10);
	
	$qrdata  = "BCD" . $nl;  // Service Tag
	$qrdata .= "001" . $nl; // Version
	$qrdata .= "1" . $nl;  // Char Set UTF-8
	$qrdata .= "SCT" . $nl;  // Id-Code
	$qrdata .= $this->bic . $nl;  // BIC, blank if not used
	$qrdata .= $this->beneficiary . $nl; // name of beneficiary
	$qrdata .= $this->iban . $nl;  // IBAN of beneficiary
	$qrdata .= "EUR" . $this->amount . $nl; // amount in EUR
	$qrdata .= $this->purposecode . $nl;  // purpose code
	$qrdata .= $this->reference . $nl;  // remittance (reference), beginning with RF
	// one can use either purpose or reference, reference overrules purpose
	if ($this->reference == "") {
		$qrdata .= $this->purpose . $nl;  // remittance (text), unstructured
	} else  {
		$qrdata .= "" . $nl;  
	}

	$qrdata .= "nvc:trash@halli-online.de";  // information (beneficiary to originator information)

	return $qrdata;
}

public function renderQrCode() {

	$payload = $this->getQrPayload();
	$outerFrame = 1; 
	$pixelPerPoint = 8; 
	
	// render Image without Girocode frame
	if ($this->frame == 0) {
		ob_start ( );
		QRcode::png ($payload, false, QR_ECLEVEL_M, $pixelPerPoint, $outerFrame, false);
		$tmp = ob_get_contents ( );
		ob_end_clean ( );
		return $tmp;
		die();
	}

	
	
	// generating frame 
	
	$frame = QRcode::text($payload, false, QR_ECLEVEL_M); 
	 
	// rendering frame with GD2 (that should be function by real impl.!!!) 
	$h = count($frame); 
	$w = strlen($frame[0]); 
	$imgW = $w + 2*$outerFrame; 
	$imgH = $h + 2*$outerFrame; 
	 
	$base_image = imagecreate($imgW, $imgH); 
	 
	$col[0] = imagecolorallocate($base_image,255,255,255); // BG, white  
	$col[1] = imagecolorallocate($base_image,0,0,0);     // FG, black
	
	imagefill($base_image, 0, 0, $col[0]); 

	for($y=0; $y<$h; $y++) { 
		for($x=0; $x<$w; $x++) { 
			if ($frame[$y][$x] == '1') { 
				imagesetpixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]);  
			} 
		} 
	} 

	$target_image = imagecreatefrompng(plugin_dir_path( __FILE__ ) . "images/girocode.png");

	imagecopyresized( 
		$target_image,  
		$base_image,  
		25, 25, 0, 0,
		350, 350, $imgW, $imgH 
	); 
	
	imagedestroy($base_image); 
	
	ob_start ( );
	imagepng ( $target_image );
	$tmp = ob_get_contents ( );
	ob_end_clean ( );
	
	imagedestroy($target_image);
	
	return $tmp;
}


public function QrPngWithHeader() {
		
			
		$image = imagecreatefromstring($this->renderQrCode());
		if (! $image)  die("Fehler in getQrPng");
		header('Content-Type: image/png');
		imagepng($image);
		imagedestroy($image);  
		return true;
		exit;
}

public function getTransientKey() { 
	return "sgc_" . md5($this->getQrPayload());
}

public function getQrImgTag() {
	$key = $this->getTransientKey();
	$img_src = "index.php?sepa-girocode=show-code&key=" . $key;
	$click_url = "index.php?sepa-girocode=get-codefile&key=" . $key;
	$width_height = $this->dimension;
	
	
	if ($this->isclickable == 1) {
		$a = "<a href=\"$click_url\" target=\"_blank\">";
		$ae = "</a>"; 
	} else {
		$a = "";
		$ae = "";
	}
	
	$output = "<img src=\"$img_src\" width=\"$width_height\" height=\"$width_height\"";
	
	if (strlen($this->class) > 0) {
		$output .= " class=\"" . $this->class . "\"";
	}
	
	if (strlen($this->title) > 0) {
		$output .= " title=\"" . $this->title . "\"";
	}
	
	if (strlen($this->alt) > 0) {
		$output .= " alt=\"" . $this->alt . "\"";
	}
	
	if (strlen($this->style) > 0) {
		$output .= " style=\"" . $this->style . "\"";
	}

	$output = $a . $output . "/>" . $ae;

	return $output;
}

}  // end class


function sepa_girocode_activate() {
	// nothing to do
}

function sepa_girocode_deactivate() {
	global $wpdb;
	// delete all transients
	$result = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_sgc_%'");
	foreach ($result as $transient) {
		delete_transient(trim(substr($transient->option_name, 11, 999)));
	}
}

function sepa_girocode_update() {
	global $wpdb;
	// delete all transients
	$result = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_sgc_%'");
	foreach ($result as $transient) {
		delete_transient(trim(substr($transient->option_name, 11, 999)));
	}
}
	
	
function sepa_girocode_shortcode( $atts ) {
	
	$sgc = new sepa_girocode_class();
		
	foreach ($atts as $k => $v) {
		if (array_key_exists($k, $sgc->getValidAtts())) {
			$sgc->$k = $v;
		} else {
			throw new Exception( 'Can\'t set parameter ' . $k);
		}
	}

	$key =  $sgc->getTransientKey();

	if ( false === ( $value = get_transient($key) ) ) {
		$tmp = set_transient($key, 
					  $sgc,
					  YEAR_IN_SECONDS);
		if (!$tmp) die ("Konnte Transient nicht setzen! KEY = " . $key);
	}

	return $sgc->getQrImgTag();
}	



function sepa_girocode_parse_request($wp) {
	// only process requests with "halli-girocode=show-code"
	

	if (array_key_exists('sepa-girocode', $wp->query_vars) && $wp->query_vars['sepa-girocode'] == 'show-code') {
		
		$key = $wp->query_vars['key'];
		if ( false === ( $sgc = get_transient($key) ) ) {
			die("Kein Transient gefunden!");
		}
		
		$sgc->QrPngWithHeader();
		exit;

	}
	

	
	if (array_key_exists('sepa-girocode', $wp->query_vars) && $wp->query_vars['sepa-girocode'] == 'get-codefile') {
		$key = $wp->query_vars['key'];
		$sgc = get_transient($key);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$key.girocode\"");
		echo $sgc->getQrPayload();
		
		exit;
	}
	
}




function sepa_girocode_query_vars($vars) {
	$vars[] = 'sepa-girocode';
	$vars[] = 'key';
	return $vars;
}


// Generator
function sepa_girocode_shortcode_generator( $atts ) {
	
	$output = "";
		
	// Demo-Modus?
	$demo = 0;
	$std_beneficiary = "";
	$std_iban = "";
	$std_bic = "";
	$std_amount = "";
	$std_purpose = "";
	
	if (is_array($atts) && array_key_exists("demo", $atts) && $atts["demo"] == 1) {
		$std_beneficiary = "Deutsches Rotes Kreuz";
		$std_iban = "DE63370205000005023307";
		$std_bic = "BFSWDE33XXX";
		$std_amount = "20";
		$std_purpose = "Spende";
	}
	
	
	
	
	if ( isset( $_POST['gcf-submitted'] ) ) {
		
		$sgc = new sepa_girocode_class();
		$sgc->beneficiary = sanitize_text_field( $_POST["gcf-beneficiary"] );
		$sgc->iban = sanitize_text_field( $_POST["gcf-iban"] );
		$sgc->bic = sanitize_text_field( $_POST["gcf-bic"] );
		$sgc->amount = sanitize_text_field( $_POST["gcf-amount"] );
		$sgc->purpose = sanitize_text_field( $_POST["gcf-purpose"] );
		$sgc->purposecode = sanitize_text_field( $_POST["gcf-purposecode"] );
		$sgc->isclickable = 0;
		$sgc->dimension = 300;

        // $to = get_option( 'admin_email' );

		$key =  $sgc->getTransientKey();

		if ( false === ( $value = get_transient($key) ) ) {
			$tmp = set_transient($key, 
						  $sgc,
						  HOUR_IN_SECONDS);
			if (!$tmp) die ("Konnte Transient nicht setzen! KEY = " . $key);
		}

		$output .= '<div style="float:right;width=300px;"><p>' . $sgc->getQrImgTag() . '</p></div>';
        
		
    }
	
	
	
	//$sgc = new sepa_girocode_class();
		
	$output .=  '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	
    $output .=  '<p>';
    $output .=  'Zahlungsempfänger*<br />';
    $output .=  '<input type="text" name="gcf-beneficiary" title="nur Buchstaben, Zahlen, Leerzeichen und die Zeichen :?,-(+.)/ sind erlaubt, max. 70 Zeichen" minlength="2" maxlenth="70" pattern="[a-zA-Z0-9 :?,-(+.)/]+" value="' . ( isset( $_POST["gcf-beneficiary"] ) ? esc_attr( $_POST["gcf-beneficiary"] ) : $std_beneficiary ) . '" size="40" required />';
    $output .=  '</p>';
	
    $output .=  '<p>';
    $output .=  'IBAN* <br />';
    $output .=  '<input type="text" name="gcf-iban" minlength="15" maxlength="31" title="nur Buchstaben und Zahlen erlaubt" pattern="[a-zA-Z0-9]+" value="' . ( isset( $_POST["gcf-iban"] ) ? esc_attr( $_POST["gcf-iban"] ) : $std_iban ) . '" size="40" required/>';
    $output .=  '</p>';
	
    $output .=  '<p>';
    $output .=  'BIC* <br />';
    $output .=  '<input type="text" name="gcf-bic" minlength="8" maxlength="11" title="nur Buchstaben und Zahlen erlaubt, min. 8, max. 11 Stellen" pattern="[a-zA-Z0-9]+" value="' . ( isset( $_POST["gcf-bic"] ) ? esc_attr( $_POST["gcf-bic"] ) : $std_bic ) . '" size="40" required />';
    $output .=  '</p>';

	$output .=  '<p>';
    $output .=  'Betrag in EUR* <br />';
    $output .=  '<input type="number" name="gcf-amount" min="0" max="999999999" step="1" value="' . ( isset( $_POST["gcf-amount"] ) ? esc_attr( $_POST["gcf-amount"] ) : $std_amount ) . '" size="40" />';
    $output .=  '</p>';
	
	$output .=  '<p>';
    $output .=  'Verwendungszweck*<br />';
    $output .=  '<input type="text" name="gcf-purpose" title="nur Buchstaben, Zahlen, Leerzeichen und die Zeichen :?,-(+.)/ sind erlaubt, max. 140 Zeichen" maxlength="140" pattern="[a-zA-Z0-9 :?,-(+.)/]+" value="' . ( isset( $_POST["gcf-purpose"] ) ? esc_attr( $_POST["gcf-purpose"] ) : $std_purpose ) . '" size="40" required />';
    $output .=  '</p>';
	
	$output .=  '<p>';
    $output .=  'Purpose-Code*<br />';
	$output .=  '<fieldset>';
    $output .=  '<input type="radio" name="gcf-purposecode" id = "standard" value = "" ' . ( ! isset($_POST["gcf-purposecode"]) || $_POST["gcf-purposecode"]  == ""  ?  'checked' : '' ) . ' size="40" />';
    $output .=  '<label for="standard">Standard</label>';
	$output .=  '</br>';
	$output .=  '<input type="radio" name="gcf-purposecode" id = "char" value = "CHAR" ' . ( isset($_POST["gcf-purposecode"]) && $_POST["gcf-purposecode"]  == "CHAR"  ?  'checked' : '' ) . ' size="40" />';
	$output .=  '<label for="char">Spende (CHAR)</label>';
	$output .=  '</fieldset>';
	$output .=  '</p>';
	
	
	
	
    $output .=  '<p><input type="submit" name="gcf-submitted" value="Girocode generieren"/></p>';
    $output .=  '</form>';

	return $output;
}	





register_activation_hook( __FILE__, 'sepa_girocode_activate' );
register_deactivation_hook( __FILE__, 'sepa_girocode_deactivate' );


add_shortcode( 'girocode', 'sepa_girocode_shortcode' );
add_shortcode( 'girocode-generator', 'sepa_girocode_shortcode_generator' );


add_action('parse_request', 'sepa_girocode_parse_request');
add_filter('query_vars', 'sepa_girocode_query_vars');

add_action( 'upgrader_process_complete', 'sepa_girocode_upgrade');


?>