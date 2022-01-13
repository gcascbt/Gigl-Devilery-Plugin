<?php
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl;

final class Init_GIGL
{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function get_services() 
	{
		return [
			BaseHandlerLocateFiles\Settings_Links_Handler_Class::class,
			PagesHandlerLocateFiles\Admin_GIGL_Handler_Class::class,
			BaseHandlerLocateFiles\Enqueue_Class_Files_Handler::class,
			PagesHandlerLocateFiles\Deliver_Loader_Handler_Class::class
		];
	}

	/**
	 * Loop through the classes, initialize them, 
	 * and call the register() method if it exists
	 * @return
	 */
	public static function register_services() 
	{
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Initialize the class
	 * @param  class $class    class from the services array
	 * @return class instance  new instance of the class
	 */
	private static function instantiate( $class )
	{
		$service = new $class();

		return $service;
	}
}