<?php 
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl\PagesHandlerLocateFiles;

use \IncGiGl\BaseHandlerLocateFiles\Base_Controller_Handler_Class;
use \IncGiGl\ApiHandlerLocateFiles\Settings_Api_Handler_Class;

/**
* 
*/
class Admin_GIGL_Handler_Class extends Base_Controller_Handler_Class
{
	public $settings;

	public $pages = array();

	public $subpages = array();

	public function __construct()
	{
		$this->settings = new Settings_Api_Handler_Class();

		$this->pages = array(
			array(
				'page_title' => 'GIGL Plugin', 
				'menu_title' => 'GIGL Logistics', 
				'capability' => 'manage_options', 
				'menu_slug' => 'gigl_plugin', 
				'callback' => function() { return require_once( plugin_dir_path( dirname( __FILE__, 2 ) ) . "/templates/admin.php" ); }, 
				'icon_url' => 'dashicons-airplane', 
				'position' => 110
			)
		);

		$this->subpages = array(
			array(
				'parent_slug' => 'gigl_plugin', 
				'page_title' => 'GIG Settings', 
				'menu_title' => 'GIG Settings', 
				'capability' => 'manage_options', 
				'menu_slug' => 'wc-settings&tab=shipping&section=gigl_delivery', 
				'callback' => function() { echo sanitize_text_field('<h1>CPT Manager</h1>'); }
			),
			// array(
			// 	'parent_slug' => 'gigl_plugin', 
			// 	'page_title' => 'Custom Taxonomies', 
			// 	'menu_title' => 'Taxonomies', 
			// 	'capability' => 'manage_options', 
			// 	'menu_slug' => 'gigl_taxonomies', 
			// 	'callback' => function() { echo sanitize_text_field('<h1>Taxonomies Manager</h1>'); }
			// ),
			// array(
			// 	'parent_slug' => 'gigl_plugin', 
			// 	'page_title' => 'Custom Widgets', 
			// 	'menu_title' => 'Widgets', 
			// 	'capability' => 'manage_options', 
			// 	'menu_slug' => 'gigl_widgets', 
			// 	'callback' => function() { echo sanitize_text_field('<h1>Widgets Manager</h1>'; }
			// )
		);
	}

	public function register() 
	{
		$this->settings->addPages( $this->pages )->withSubPage( 'Dashboard' )->addSubPages( $this->subpages )->register();
	}
}