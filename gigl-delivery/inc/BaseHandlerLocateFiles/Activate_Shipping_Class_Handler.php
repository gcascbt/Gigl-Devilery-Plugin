<?php
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl\BaseHandlerLocateFiles;

class Activate_Shipping_Class_Handler
{
	public static function activate() {
		flush_rewrite_rules();
	}
}