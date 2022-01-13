<?php
	
	defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );
	
	/**
		* Gigl Delivery Orders Class
		*
		* Adds order admin page customizations
		*
		* @since 1.0
	*/
	class WC_Gigl_Delivery_Orders
	{
		/** @var \WC_Gigl_Delivery_Orders single instance of this class */
		private static $instance;
		
		/**
			* Add various admin hooks/filters
		*/
		public function __construct()
		{
			
			
			// update order status
			add_action('admin_footer-edit.php', array($this, 'add_order_bulk_actions'));
			add_action('load-edit.php', array($this, 'process_order_bulk_actions'));
			
			// add 'Gigl Delivery Information' order meta box
			add_action('add_meta_boxes', array($this, 'add_order_meta_box'));
			
			
			// process order meta box order actions
			add_action('woocommerce_order_action_wc_gigl_delivery_update_status', array($this, 'process_order_meta_box_actions'));
			
			// add 'Update Gigl Delivery Status' order meta box order actions
			add_filter('woocommerce_order_actions', array($this, 'add_order_meta_box_actions'));
			
		}
		
		
		/**
			* Add "Update Gigl Order Status"
			*
			* @since 1.0
		*/
		public function add_order_bulk_actions()
		{
			global $post_type, $post_status;
			
			if ($post_type == 'shop_order' && $post_status != 'trash') {
			?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('select[name^=action]').append(
					$('<option>').val('update_order_status').text('<?php _e('Update Order Status (via gigl delivery)'); ?>')
                    );
				});
			</script>
			<?php
			}
		}
		
		/**
			* Processes the "Export"
			*
			* @since  1.0
		*/
		public function process_order_bulk_actions()
		{
			global $typenow;
			
			if ('shop_order' == $typenow) {
				// get the action
				$wp_list_table = _get_list_table('WP_Posts_List_Table');
				$action        = $wp_list_table->current_action();
				
				// return if not processing our actions
				if (!in_array($action, array('update_order_status'))) {
					return;
				}
				
				// security check
				check_admin_referer('bulk-posts');
				
				// make sure order IDs are submitted
				if (isset($_REQUEST['post'])) {
					$order_ids = array_map('absint', $_REQUEST['post']);
				}
				
				// return if there are no orders to export
				if (empty($order_ids)) {
					return;
				}
				
				// give ourselves an unlimited timeout if possible
				@set_time_limit(0);
				
				foreach ($order_ids as $order_id) {
					try {
						wc_gigl_delivery()->update_order_shipping_status($order_id);
						} catch (\Exception $e) {
					}
				}
			}
		}
		
		/**
			* Add 'Update Shipping Status' order actions to the 'Edit Order' page
			*
			* @since 1.0
			* @param array $actions
			* @return array
		*/
		public function add_order_meta_box_actions($actions)
		{
			// add update shipping status action
			$actions['wc_gigl_delivery_update_status'] = __('Update Order Status (via gigl delivery)');
			
			return $actions;
		}
		
		
		/**
			* Handle actions from the 'Edit Order' order action select box
			*
			* @since 1.0
			* @param \WC_Order $order object
		*/
		public function process_order_meta_box_actions($order)
		{
			wc_gigl_delivery()->update_order_shipping_status($order);
		}
		
		
		/**
			* Add 'Gigl Delivery Information' meta-box to 'Edit Order' page
			*
			* @since 1.0
		*/
		public function add_order_meta_box()
		{
			add_meta_box(
            'wc_gigl_delivery_order_meta_box',
            __('Gigl Delivery'),
            array($this, 'render_order_meta_box'),
            'shop_order',
            'side'
			);
		}
		
		
		/**
			* Display the 'Gigl Delivery Information' meta-box on the 'Edit Order' page
			*
			* @since 1.0
		*/
		public function render_order_meta_box()
		{
			global $post;
			
			$order = wc_get_order($post);
			
			$gigl_order_id = $order->get_meta('gigl_delivery_waybill');
			
			if ($gigl_order_id && $gigl_order_id > 0) {
				$this->show_gigl_delivery_shipment_status($order);
				} else {
				$this->shipment_order_send_form($order);
			}
		}
		
		public function show_gigl_delivery_shipment_status($order)
		{
			$gigl_order_id = $order->get_meta('gigl_delivery_waybill');
		?>
		
        <table id="wc_gigl_delivery_order_meta_box">
            <tr>
                <th><strong><?php esc_html_e('Way-bill') ?> : </strong></th>
                <td><?php echo esc_html((empty($gigl_order_id)) ? __('N/A') : $gigl_order_id); ?></td>
			</tr>
			
            <tr>
                <th><strong><?php esc_html_e('Shipping Status') ?> : </strong></th>
                <td>
                    <?php echo sanitize_text_field($order->get_meta('gigl_delivery_status_res')); ?>
				</td>
			</tr>
			
            <tr>
                <th><strong><?php esc_html_e('Tracking ID') ?> : </strong></th>
                <td>
                    <?php echo sanitize_text_field($order->get_meta('gigl_delivery_tracking_id')); ?>
				</td>
			</tr>
		</table>
		<?php
		}
		
		public function shipment_order_send_form($order)
		{
		?> 
        <p> No scheduled task for this order</p>
		<?php
		}
		
		/**
			* Gets the main loader instance.
			*
			* Ensures only instance can be loaded.
			*
			*
			* @return \WC_Gigl_Delivery_Loader
		*/
		public static function instance()
		{
			if (null === self::$instance) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
	}
	
	// fire it up!
	return WC_Gigl_Delivery_Orders::instance();
