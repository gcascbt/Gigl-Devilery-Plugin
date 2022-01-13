<?php
/**
 * @package  GIGLDelivery
 */
/*
Plugin Name: GIGL Delivery
Plugin URI: https://giglogistics.com
Description: A delivery platform.
Version: 1.0.0
Author: Wahab "GIGL" Olaonipekun
Author URI: https://github.com/gcascbt
License: GPLv2 or later
Text Domain: gigl-delivery
Copyright: © 2022 GIG Logistics
*/


// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

// Require once the Composer Autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_gigl_plugin() {
	IncGiGl\BaseHandlerLocateFiles\Activate_Shipping_Class_Handler::activate();
}
register_activation_hook( __FILE__, 'activate_gigl_plugin' );

/**
 * The code that runs during plugin deactivation
 */
function deactivate_gigl_plugin() {
	IncGiGl\BaseHandlerLocateFiles\Deactivate_Shipping_Class_Handler::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_gigl_plugin' );

/**
 * Initialize all the core classes of the plugin
 */
if ( class_exists( 'IncGiGl\\Init_GIGL' ) ) {
	IncGiGl\Init_GIGL::register_services();
}
