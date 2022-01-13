<?php 
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl\BaseHandlerLocateFiles;

use \IncGiGl\BaseHandlerLocateFiles\Base_Controller_Handler_Class;

/**
* 
*/
class Enqueue_Class_Files_Handler extends Base_Controller_Handler_Class
{
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue() {
		// enqueue all our scripts
		wp_enqueue_style( 'mypluginstyle', $this->plugin_url . 'assets/mystyle.css' );
		wp_enqueue_script( 'mypluginscript', $this->plugin_url . 'assets/myscript.js' );
	}
}