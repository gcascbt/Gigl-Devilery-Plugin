<?php
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl\BaseHandlerLocateFiles;

use \IncGiGl\BaseHandlerLocateFiles\Base_Controller_Handler_Class;
class Settings_Links_Handler_Class extends Base_Controller_Handler_Class
{	
	
	
	public function register() 
	{
		
		//print_r($this->wc_active_check());
		add_filter( "plugin_action_links_$this->plugin", array( $this, 'settings_link' ) );
	}

	public function settings_link( $links ) 
	{
		$settings_link = '<a href="admin.php?page=wc-settings&tab=shipping&section=gigl_delivery">Settings</a>';
		array_push( $links, $settings_link );
		return $links;
	}
	
}