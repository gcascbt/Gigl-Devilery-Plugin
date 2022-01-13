<?php
	
	if (!defined('ABSPATH')) exit; // Exit if accessed directly
	
	/**
		* Main Gigl Delivery Class.
		*
		* @class  WC_Gigl_Delivery
	*/
	class WC_Gigl_Delivery
	{
		/** @var \WC_Gigl_Delivery_API api for this plugin */
		public $api;
		
		/** @var array settings value for this plugin */
		public $settings;
		
		/** @var array order status value for this plugin */
		public $statuses;

		/** @var plugin path identifier */
		public $my_plugin_path;

		/** @var get shipping waybill*/
		public $currentWaybill;
		
		/** @var \WC_Gigl_Delivery single instance of this plugin */
		protected static $instance;
		
		/**
			* Loads functionality/admin classes and add auto schedule order hook.
			*
			* @since 1.0
		*/
		public function __construct()
		{
			//get plugin_path
			$this->my_plugin_path = plugin_dir_path( dirname( __FILE__, 1 ) );
			
			// get settings
			$this->settings = maybe_unserialize(get_option('woocommerce_gigl_delivery_settings'));
			
			$this->statuses = [
            'UPCOMING',
            'STARTED',
            'ENDED',
            'FAILED',
            'ARRIVED',
            '',
            'UNASSIGNED',
            'ACCEPTED',
            'DECLINE',
            'CANCEL',
            'DELETED',
            'MCRT'
			];
			
			$this->init_plugin();
			
			$this->init_hooks();

		}
		
		/**
			* Initializes the plugin.
			*
			* @internal
			*
			* @since 1.0.0
		*/
		public function init_plugin()
		{
			$this->includes();
			
			if (is_admin()) {
				$this->admin_includes();
			}
			
		}
		


		/**
			* Includes all files.
			*
			* @since 1.0.0
		*/
		public function includes()
		{
			$plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
			
			require_once $plugin_path . 'inc/BaseHandlerLocateFiles/WC_Gigl_Delivery_API.php';
			
			require_once $plugin_path . 'inc/BaseHandlerLocateFiles/WC_Gigl_Delivery_Shipping_Method.php';
		}
		
		public function admin_includes()
		{
			$plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
			
			require_once $plugin_path . 'inc/BaseHandlerLocateFiles/WC_Gigl_Delivery_Orders.php';
		}
		
		/**
			* Initialize hooks.
			*
			* @since 1.0.0
		*/
		public function init_hooks()
		{
			/**
				* Actions
			*/
				
			
            // create order when \WC_Order::payment_complete()
			add_action('woocommerce_thankyou', array($this, 'create_order_shipping_task')); 
			
			
			add_action('woocommerce_shipping_init', array($this, 'load_shipping_method'));
			
			// cancel a Gigl delivery task when an order is cancelled in WC
			add_action('woocommerce_order_status_cancelled', array($this, 'cancel_order_shipping_task'));
			
			// adds tracking button(s) to the View Order page
			add_action('woocommerce_order_details_after_order_table', array($this, 'add_view_order_tracking'),10,3);

			/**
				* Filters
			*/
			// Add shipping icon to the shipping label on cart and checkout.
			add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'add_shipping_icon'), PHP_INT_MAX, 2);
			
			add_filter('woocommerce_checkout_fields', array($this, 'remove_address_2_checkout_fields'));
			
			add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_method'));
			
			add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
			
			add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
		}
		
		/**
			* shipping_icon to desplay on cart and checkout.
			*
			* @since   1.0.0
		*/
		function add_shipping_icon($label, $method)
		{
			if ($method->method_id == 'gigl_delivery') {
				$plugin_path = $this->my_plugin_path;
				$logo_title = 'Gigl Delivery';
				$icon_url = plugins_url('assets/logo/gig-logo2.png', $plugin_path);
				$img = '<img class="gigl-delivery-logo"' .
                ' alt="' . $logo_title . '"' .
                ' title="' . $logo_title . '"' .
                ' style="width:25px; height:25px; display:inline;"' .
                ' src="' . $icon_url . '"' .
                '>';
				$label = $img . ' ' . $label;
			}
			
			return $label;
		}

		/**
			* Submit data to Gigl to handle your delivery.
			*
			* @since   1.0.0
		*/
		
		public function create_order_shipping_task($order_id)
		{
			$order = wc_get_order($order_id);
			// $order_status    = $order->get_status();
			$order_items = $order->get_items();
			$shipping_method = @array_shift($order->get_shipping_methods());
			
			if (strpos($shipping_method->get_method_id(), 'gigl_delivery') !== false) {
				
				$receiver_name      = $order->get_shipping_first_name() . " " . $order->get_shipping_last_name();
				$receiver_email     = $order->get_billing_email();
				$receiver_phone     = $order->get_billing_phone();
				$delivery_base_address  = $order->get_shipping_address_1();
				// $delivery_address2  = $order->get_shipping_address_2();
				// $delivery_company   = $order->get_shipping_company();
				$delivery_city      = $order->get_shipping_city();
				$delivery_state_code    = $order->get_shipping_state();
				$delivery_postcode    = $order->get_shipping_postcode();
				
				
				$delivery_country_code  = $order->get_shipping_country();
				$delivery_state = WC()->countries->get_states($delivery_country_code)[$delivery_state_code];
				$delivery_country = WC()->countries->get_countries()[$delivery_country_code];
				$payment_method = $order->get_payment_method();
				
				if($payment_method == 'cod') {
					
					$gigl_payment_method = 1048576; 
					
					}else {
					
					$gigl_payment_method = 524288; 
					
				}
				
				$preShipmentItems = array();
				foreach( $order_items as $item_id => $item ){

					// methods of WC_Order_Item class

					// The element ID can be obtained from an array key or from:
					$item_id = $item->get_id();
					
					// methods of WC_Order_Item_Product class

					$item_name = $item->get_name(); // Name of the product
					$item_type = $item->get_type(); // Type of the order item ("line_item")

					$product_id = $item->get_product_id(); // the Product id
					$wc_product = $item->get_product();    // the WC_Product object

					// order item data as an array
					$item_data = $item->get_data();
					$eachProductItem = array(
											"SpecialPackageId" => "0", 
							                "Quantity" => $item_data['quantity'], 
							                "Weight" => "1", 
							                "ItemType" => "Normal", 
							                "WeightRange" => "0", 
							                "ItemName" => $item_data['name'], 
							                "Value" => $item_data['total'], 
							                "ShipmentType" => "Regular"
							            	);

					$preShipmentItems[] = $eachProductItem;

				}
				
				$sender_name         = $this->settings['sender_name'];
				$sender_phone        = $this->settings['sender_phone_number'];
				$pickup_base_address = $this->settings['pickup_base_address'];
				$pickup_city         = $this->settings['pickup_city'];
				$pickup_state        = $this->settings['pickup_state'];
				$pickup_country      = $this->settings['pickup_country'];
				$pickup_postcode      = $this->settings['pickup_postcode'];
				if (trim($pickup_country) == '') {
					$pickup_country = 'NG';
				}
				
				$todaydate =  date('Y-m-d H:i:s', time());
				$pickup_date = date('Y-m-d H:i:s', strtotime($todaydate . ' +1 day'));
				$delivery_date = date('Y-m-d H:i:s', strtotime($todaydate . ' +2 day'));
				
				$api = $this->get_api();
				
				if($delivery_postcode == '') { 
					
					$delivery_address = trim("$delivery_base_address $delivery_city, $delivery_state, $delivery_country");
					$delivery_coordinate = $api->get_lat_lng($delivery_address);
					
					if (!isset($delivery_coordinate['Latitude']) && !isset($delivery_coordinate['Longitude'])) {
						$delivery_coordinate = $api->get_lat_lng("$delivery_city, $delivery_state, $delivery_country");
					}
					
					$pickup_address = trim("$pickup_base_address $pickup_city, $pickup_state, $pickup_country");
					$pickup_coordinate = $api->get_lat_lng($pickup_address);
					
					if (!isset($pickup_coordinate['Latitude']) && !isset($pickup_coordinate['Longitude'])) {
						$pickup_coordinate = $api->get_lat_lng("$pickup_city, $pickup_state, $pickup_country");
					}
					
				}else {
					
					
					$delivery_address1 = $delivery_postcode . ',' . $delivery_city . ',' . $delivery_state . ',nigeria';
					$delivery_address1 = trim("$delivery_address1");
					
					$delivery_address = trim("$delivery_base_address $delivery_city, $delivery_state, $delivery_country,$delivery_postcode");
					$delivery_coordinate = $api->get_lat_lng($delivery_address1);
					
					if (!isset($delivery_coordinate['Latitude']) && !isset($delivery_coordinate['Longitude'])) {
						$delivery_coordinate = $api->get_lat_lng("$delivery_address1");
					}
					
					$pickup_address1 = $pickup_postcode . ',' . $pickup_city . ',' . $pickup_state . ',nigeria';
					$pickup_address1 = trim("$pickup_address1");
					
					$pickup_address = trim("$pickup_base_address $pickup_city, $pickup_state, $pickup_country, $pickup_postcode");
					$pickup_coordinate = $api->get_lat_lng($pickup_address1);
					
					if (!isset($pickup_coordinate['Latitude']) && !isset($pickup_coordinate['Longitude'])) {
						$pickup_coordinate = $api->get_lat_lng("$pickup_address1");
					}
					
				}
				$receiverLocation = array(
										"Latitude" => $delivery_coordinate['Latitude'],
										"Longitude" => $delivery_coordinate['Longitude']
										);

				$senderLocation = array(
										"Latitude" => $pickup_coordinate['Latitude'],
										"Longitude" => $pickup_coordinate['Longitude']
										);
				if($payment_method == 'cod') {

					$params = array(
								"ReceiverAddress" => $delivery_address,  
								"SenderLocality" => $pickup_city,
								"SenderAddress" => $pickup_address, 
								"ReceiverPhoneNumber" => $receiver_phone, 
								"VehicleType" => "BIKE", 
								"SenderPhoneNumber" => $sender_phone, 
								"SenderName" => $sender_name,
								"ReceiverName" => $receiver_name, 
								"ReceiverLocation" => $receiverLocation,
								"SenderLocation" => $senderLocation,
								"PreShipmentItems" => $preShipmentItems
		    					);
				}else{
					$params = array(
								"ReceiverAddress" => $delivery_address,  
								"SenderLocality" => $pickup_city,
								"SenderAddress" => $pickup_address, 
								"ReceiverPhoneNumber" => $receiver_phone, 
								"VehicleType" => "BIKE", 
								"SenderPhoneNumber" => $sender_phone, 
								"SenderName" => $sender_name,
								"ReceiverName" => $receiver_name, 
								"ReceiverLocation" => $receiverLocation,
								"SenderLocation" => $senderLocation,
								"PreShipmentItems" => $preShipmentItems
		    					);
				}
         
				
				
				// error_log(print_r($params, true));
				$res = $api->create_task($params);
				// error_log(print_r($res, true));
				
				$order->add_order_note("Gigl Delivery: " . $res->Object->message);
				$_SESSION['bogus'] = 'bogus';

				if ($res->Code == 200) {
					
					$_SESSION['reques'] = $res->Object->waybill;
					//$data = $res['data'];
					$this->currentWaybill = $res->Object->waybill;
					 update_post_meta($order_id, 'gigl_delivery_waybill', $res->Object->waybill);
					 update_post_meta($order_id, 'gigl_delivery_check_status_url', 'http://test.giglogisticsse.com/api/thirdparty/TrackAllShipment/'.$res->Object->waybill);
					
					// For Pickup
					update_post_meta($order_id, 'gigl_delivery_pickup_id', $res->Object->waybill);
					//update_post_meta($order_id, 'gigl_delivery_pickup_status', $this->statuses[6]); // UNASSIGNED
					update_post_meta($order_id, 'gigl_delivery_pickup_tracking_url', 'http://test.giglogisticsse.com/api/thirdparty/TrackAllShipment/'.$res->Object->waybill);
					
					// For Delivery
					update_post_meta($order_id, 'gigl_delivery_delivery_id', $res->Object->waybill);
					//update_post_meta($order_id, 'gigl_delivery_delivery_status', $this->statuses[6]); // UNASSIGNED
					update_post_meta($order_id, 'gigl_delivery_delivery_tracking_url', 'http://test.giglogisticsse.com/api/thirdparty/TrackAllShipment/'.$res->Object->waybill);

					update_post_meta($order_id, 'gigl_delivery_status_res', $this->statuses[6]); // UNASSIGNED
					
					update_post_meta($order_id, 'gigl_delivery_order_response', $res);
					
					$note = sprintf(__('Shipment scheduled via Gigl delivery (Order Id: %s)'), $res->Object->waybill);
					$order->add_order_note($note);
				}
			}
		}
		
		/**
			* Cancels an order in Gigl Delivery when it is cancelled in WooCommerce.
			*
			* @since 1.0.0
			*
			* @param int $order_id
		*/
		public function cancel_order_shipping_task($order_id)
		{
			$order = wc_get_order($order_id);
			$gigl_waybill = $order->get_meta('gigl_delivery_waybill');
			$gigl_pickup_id = $order->get_meta('gigl_delivery_pickup_id');
			$gigl_delivery_id = $order->get_meta('gigl_delivery_delivery_id');
			
			if ($gigl_waybill) {
				
				try {
					$params = [
                    'job_id' => $gigl_pickup_id  // check if to cancel pickup task or delivery task
                    //'job_status' => 9 // Gigl delivery job status is 9 for a cancelled task
					];
					$this->get_api()->cancel_task($params);
					
					$order->update_status('cancelled');
					
					$order->add_order_note(__('Order has been cancelled in Gigl Delivery.'));
					} catch (Exception $exception) {
					
					$order->add_order_note(sprintf(
                    /* translators: Placeholder: %s - error message */
                    esc_html__('Unable to cancel order in Gigl Delivery: %s'),
                    $exception->getMessage()
					));
				}
			}
		}
		
		/**
			* Update order status by fetching the order details from Gigl Delivery.
			*
			* @since 1.0.0
			*
			* @param int $order_id
		*/
		public function update_order_shipping_status($order_id)
		{
			$order = wc_get_order($order_id);
			if(!empty($this->currentWaybill)){
				$gigl_waybill = $this->currentWaybill;
			}else{
				$gigl_waybill = $order->get_meta('gigl_delivery_waybill');
			}
			
			
			if ($gigl_waybill) {
				$res = $this->get_api()->get_order_details($gigl_waybill);
				
				if ($res['Code'] == 200) {
					$job_delivery_status = $this->statuses[$res['Object']['MobileShipmentTrackings']['ScanStatus']['Code']];
					$tracking_id = $this->statuses[$res['Object']['MobileShipmentTrackings']['MobileShipmentTrackingId']];
					
					if ($pickup_status == 'ACCEPTED') {
						$order->add_order_note("Gigl Delivery: Agent $pickup_status order");
						} elseif ($pickup_status == 'STARTED') {
						$order->add_order_note("Gigl Delivery: Agent $pickup_status order");
						} elseif($job_delivery_status == 'MCRT'){
						$order->add_order_note("Gigl Delivery: Agent has $pickup_status destination");
						}elseif ($delivery_status == 'ARRIVED') {
						$order->add_order_note("Gigl Delivery: Agent has $pickup_status destination");
						} elseif ($delivery_status == 'ENDED') {
						$order->update_status('completed', 'Gigl Delivery: Order completed successfully');
					}
					update_post_meta($order_id, 'gigl_delivery_tracking_id', $tracking_id);
					update_post_meta($order_id, 'gigl_delivery_status_res', $job_delivery_status);
					update_post_meta($order_id, 'gigl_delivery_order_details_response', $res);
				}
			}
		}
		
		/**
			* Add tracking information to the Order page.
			*
			* @internal
			*
			* @since 1.0.0
			*
			* @param int|\WC_Order $order the order object
		*/
		public function add_view_order_tracking($order)
		{
			
			$order = wc_get_order($order);
			$current_order_id  = $order->get_id();
			if(!empty($this->currentWaybill)){
				$gigl_waybill = $this->currentWaybill;
			}else{
				$gigl_waybill = $order->get_meta('gigl_delivery_waybill');
			}
			$res = $this->get_api()->track_details($gigl_waybill);
			$pickup_tracking_url = $order->get_meta('gigl_delivery_pickup_tracking_url');
			$delivery_tracking_url = $order->get_meta('gigl_delivery_delivery_tracking_url');
			$gigl_state_value = $order->get_meta('gigl_state_value');
			
			// reload the page to fetch the request again.
			if (isset($pickup_tracking_url)&& !empty($delivery_tracking_url)) {
				
			?>
			
			<?php
			}else{
				if(!empty($gigl_state_value)){

				}else{
					add_post_meta($current_order_id,'gigl_state_value','order_id_'.$current_order_id,true);
					
					$loadScript = "<script type='text/javascript'>window.location=document.location.href;</script>";
	    				
	        //Sanitize
	        		echo wp_kses( $loadScript, array( 
	    				'script' => array(
	        			'type' => array()
	    				),
					) );
        		}
			}
			
			if (isset($delivery_tracking_url) && !empty($delivery_tracking_url)) {
				if(empty($gigl_waybill)){ ?>
					<p class="wc-gigl-delivery-track-deliverys"><span style="padding:20px 0px;">Refresh page if track page/button not visible</span>
					 <button onClick="window.location.reload();">Refresh Page</button>
					</p>
					<?php
    			}else{
			?>
			 <p class="wc-gigl-delivery-track-deliverys">
                <a href="#" class="button" id="myBtnTrack" data="<?php echo sanitize_text_field($delivery_tracking_url.'the'.$gigl_waybill); ?>">Track Deliverys</a>
			</p>
		<?php } ?>
			<style>
			/* The Modal (background) */
			.modal {
			  display: none; /* Hidden by default */
			  position: fixed; /* Stay in place */
			  z-index: 1; /* Sit on top */
			  padding-top: 100px; /* Location of the box */
			  left: 0;
			  top: 0;
			  width: 100%; /* Full width */
			  height: 100%; /* Full height */
			  overflow: auto; /* Enable scroll if needed */
			  background-color: rgb(0,0,0); /* Fallback color */
			  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
			}

			/* Modal Content */
			.modal-content {
			  background-color: #fefefe;
			  margin: auto;
			  padding: 20px;
			  border: 1px solid #888;
			  width: 80%;
			}

			/* The Close Button */
			.close {
			  color: #aaaaaa;
			  float: right;
			  font-size: 28px;
			  font-weight: bold;
			}

			.close:hover,
			.close:focus {
			  color: #000;
			  text-decoration: none;
			  cursor: pointer;
			}
			#modelTrac ul {
			  list-style-type: none;
			  width: 100%;
			  display: table;
			  table-layout: fixed;
			}

			#modelTrac li {
			  display: table-cell;
			  width: 50%;
			}
			</style>
			<div id="myModal" class="modal">

  <!-- Modal content -->
			  <div class="modal-content" id="modelTrac">
			    <span class="close">&times;</span>
			    <h4>>>> Tracking</h4>
			    <hr>
			    <ul>
				  <li><strong>Waybill</strong></li>
				  <li><?php echo sanitize_text_field($res->Object->MobileShipmentTrackings[0]->Waybill); ?></li>
				</ul>
				 <hr>
			    <ul>
				  <li><strong>Pickup</strong></li>
				  <li><?php echo  sanitize_text_field($res->Object->Origin); ?></li>
				</ul>
				 <hr>
				<ul>
				  <li><strong>Destination</strong></li>
				  <li><?php echo  sanitize_text_field( $res->Object->Destination); ?></li>
				</ul>
				 <hr>
				<ul>
				  <li><strong>Status</strong></li>
				  <li><?php foreach($res->Object->MobileShipmentTrackings as $MobileShipmentTrackings){ $getStatusVal = $MobileShipmentTrackings->Status ."<br>"; echo wp_kses( $getStatusVal, array( 'br' => array(),) );}  ?>
				  </li>
				</ul>
			  </div>

			</div>

			<script>
			// Get the modal
			var modal = document.getElementById("myModal");

			// Get the button that opens the modal
			var btn = document.getElementById("myBtnTrack");

			// Get the <span> element that closes the modal
			var span = document.getElementsByClassName("close")[0];

			// When the user clicks the button, open the modal 
			btn.onclick = function() {
			  modal.style.display = "block";
			}

			// When the user clicks on <span> (x), close the modal
			span.onclick = function() {
			  modal.style.display = "none";
			}

			// When the user clicks anywhere outside of the modal, close it
			window.onclick = function(event) {
			  if (event.target == modal) {
			    modal.style.display = "none";
			  }
			}
			</script>
			<?php
			}
		}


		/**
			*Remove the shipping and billing address 2 from checkout
			*
			@since 1.0.0
		*/
		
		public function remove_address_2_checkout_fields($fields)
		{
			unset($fields['billing']['billing_address_2']);
			unset($fields['shipping']['shipping_address_2']);
			
			return $fields;
		}
		
		/**
			* Load Shipping method.
			*
			* Load the WooCommerce shipping class.
			*
			* @since 1.0.0
		*/
		public function load_shipping_method()
		{
			$this->shipping_method = new WC_Gigl_Delivery_Shipping_Method;
		}
		
		/**
			* Add shipping method.
			*
			* to the list of available shipping on cart or checkout.
			*
			* @since 1.0.0
		*/
		public function add_shipping_method($methods)
		{
			if (class_exists('WC_Gigl_Delivery_Shipping_Method')) :
            $methods['gigl_delivery'] = 'WC_Gigl_Delivery_Shipping_Method';
			endif;
			
			return $methods;
		}
		
		/**
			* returns the instance of Gigl Delivery API object.
			*
			* @since 1.0
			*
			* @return \WC_Gigl_Delivery_API instance
		*/
		public function get_api()
		{
			// return API object
			if (is_object($this->api)) {
				return $this->api;
			}
			
			$gigl_delivery_settings = $this->settings;
			
			// instantiate API
			return $this->api = new WC_Gigl_Delivery_API($gigl_delivery_settings);
		}
		public function get_apiss()
		{
			
			// return API object
			if (is_object($this->api)) {
				return $this->api;
			}

			$gigl_delivery_settings = $this->settings;
			
			// instantiate API
			return $this->api = new WC_Gigl_Delivery_API($gigl_delivery_settings);
		}
		public function get_plugin_path()
		{
			return plugin_dir_path(__FILE__);
		}
		
		/**
			* Returns Gigl Delivery Instance.
			*
			* Loaded only one instance.
			*
			* @since 1.0.0
			*
			* @return \WC_Gigl_Delivery
		*/
		public static function instance()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
	}
	
	
	/**
		* Returns True Instance of WooCommerce GiglDelivery.
		*
		* @since 1.0.0
		*
		* @return \WC_Gigl_Delivery
	*/
	function wc_gigl_delivery()
	{
		return \WC_Gigl_Delivery::instance();
	}
