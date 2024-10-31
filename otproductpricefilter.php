<?php
/* 
Plugin Name: Product Price Filter
Plugin URI: https://www.omegatheme.com/
Description: Shows a price filter slider in a widget which lets you narrow down the list of shown products when viewing product categories.
Author: Omegatheme
Version: 1.2.1
Company: XIPAT Flexible Solutions 
Author URI: http://www.omegatheme.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include "otproductpricefilter-widget.php";
add_action( 'init', 'otpf_ScriptInitFrontend' );
function otpf_ScriptInitFrontend() {
    wp_register_style( 'otpf-style-slide', plugins_url('/css/nouislider.min.css', __FILE__) );
    wp_enqueue_style( 'otpf-style-slide' );
    wp_register_style( 'otpf-style-plugin', plugins_url('/css/layout.css', __FILE__) );
    wp_enqueue_style( 'otpf-style-plugin' );
    wp_register_script( 'otpf-script-slide', plugins_url('/js/nouislider.min.js', __FILE__) );
    wp_enqueue_script( 'otpf-script-slide' );
}

class OtProductPriceFilter {
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'otpf_install' ) );

		add_action( 'widgets_init', array( $this, 'otpf_register_widgets' ) );
	}

	function otpf_install() {
		global $wp_version;
		If ( version_compare( $wp_version, "4.0", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 4.0 or higher." );
		}
		/**
		 * Check if WooCommerce is active
		 **/
		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		    deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin required WooCommerce plugin installed and activated. Please <a href='http://www.woothemes.com/woocommerce/' target='_blank'>download and install WooCommerce plugin</a>." );
		}
	}

	function otpf_register_widgets() {
		register_widget( 'otpf_widget' );
	}
}

$otproductpricefilter = new OtProductPriceFilter();

function otpf_e($text, $params=null) {
    if (!is_array($params)) {
        $params = func_get_args();
        $params = array_slice($params, 1);
    }
    return vsprintf(__($text, 'otpf'), $params);
}