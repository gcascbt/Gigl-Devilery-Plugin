<?php
/**
 * @package  GIGLDelivery
 */
namespace IncGiGl\BaseHandlerLocateFiles;

class Deactivate_Shipping_Class_Handler
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
}