<?php
	
	defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );
	
	class WC_Gigl_Delivery_API
	{
		protected $env;
		
		protected $login_credentials;
		
		protected $request_url;
		
		public function __construct($settings = array())
		{
			$this->env = isset($settings['mode']) ? $settings['mode'] : 'test';
			
			if ($this->env == 'live') {
				$username    = isset($settings['live_username']) ? $settings['live_username'] : '';
				$password = isset($settings['live_password']) ? $settings['live_password'] : '';    
				
				
				$this->request_url = 'https://mobile.gigl-go.com/api/thirdparty/';

				$this->sender_name = isset($settings['sender_name']) ? $settings['sender_name'] : '';
				$this->sender_phone_number = isset($settings['sender_phone_number']) ? $settings['sender_phone_number'] : '';
			} else {
				$username    = isset($settings['test_username']) ? $settings['test_username'] : '';
				$password = isset($settings['test_password']) ? $settings['test_password'] : '';
				
				$this->request_url = 'http://test.giglogisticsse.com/api/thirdparty/';

				$this->sender_name = isset($settings['sender_name']) ? $settings['sender_name'] : '';
				$this->sender_phone_number = isset($settings['sender_phone_number']) ? $settings['sender_phone_number'] : '';
			}
			
			$this->vendor_login($username, $password);
		}
		
		/**
			* Call the Gigl Delivery Login API
			*
			* @param string $username
			* @param string $password
			* @return void
		*/
		public function vendor_login($username, $password)
		{
			$login_credentials = get_transient('login_credentials_from_gigl_deleivery');
			// Transient expired or doesn't exist, fetch the data
			if (empty($login_credentials) || $login_credentials == false) {
				
				$params = [
               'username'        => $username,
               'Password'     => $password,
               'SessionObj'    => ""
				];
				
				$response = $this->api_request(
                'login',
                $params
				);
				
				$login_credentials = $response;
				

				//set transient
				set_transient('login_credentials_from_gigl_deleivery', $login_credentials, (HOUR_IN_SECONDS / 12)); // set transient for 5 mins to 9 mins
				
			}
			
		 	$this->login_credentials = $login_credentials;
		 }
		
		public function get_order_details($waybill)
		{
			$access_token = $this->login_credentials->Object->access_token;
			$params = [];
			
			return $this->api_request('TrackAllShipment/'.$waybill, $params, 'get', $access_token);
		}
		
		public function create_task($params)
		{

			$access_token = $this->login_credentials->Object->access_token;
			$params['UserId'] = $this->login_credentials->Object->UserId;
			$params['CustomerCode'] = $this->login_credentials->Object->UserName; 
         	$params['ReceiverStationId'] = "4";
          	$params['SenderStationId'] = "4";
			
			return $this->api_request('captureshipment', $params, 'post', $access_token);
		}
		public function track_details($waybill)
		{
			$access_token = $this->login_credentials->Object->access_token;
			$params = [];
			
			return $this->api_request('TrackAllShipment/'.$waybill, $params, 'get', $access_token);
		}
		public function calculate_pricing($params)
		{
			$access_token = $this->login_credentials->Object->access_token;
			$params['UserId'] = $this->login_credentials->Object->UserId;
			$params['CustomerCode'] = $this->login_credentials->Object->UserName; 
         	$params['ReceiverStationId'] = "4";
          	$params['SenderStationId'] = "4";
			
			return $this->api_request('price', $params, 'post', $access_token);
		}
		
		public function get_lat_lng($address)
		{
			$access_token = $this->login_credentials->Object->access_token;
			$address = rawurlencode($address);
			$coordinate   = get_transient('gigl_delivery_addr_geocode_' . $address);
			

			if (empty($coordinate)) {
				$params = array('Address' => $address);
				$geocodeResponse = $this->api_request('getaddressdetails', $params,'post',$access_token);
				
			 	$coordinate['Latitude']  = $geocodeResponse->Object->Latitude;
			 	$coordinate['Longitude'] = $geocodeResponse->Object->Longitude;
			 	set_transient('gigl_delivery_addr_geocode_' . $address, $coordinate, DAY_IN_SECONDS * 90);
			}
			
			return $coordinate;
		}
		
		/**
			* Send HTTP Request
			* @param string $endpoint API request path
			* @param array $args API request arguments
			* @param string $method API request method
			* * @param string $token API request token
			* @return JSON decoded transaction object. NULL on API error.
		*/
		public function api_request(
        $endpoint,
        $args = array(),
        $method = 'post', $token = NULL
		) {
			$uri = "{$this->request_url}{$endpoint}";
			$postFields =  json_encode($args);
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $uri,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => strtoupper($method),
			  CURLOPT_POSTFIELDS => $postFields,
			  CURLOPT_HTTPHEADER => $this->get_headers($token),
			));
			 
			$response = curl_exec($curl);
			$err = curl_error($curl);
			 
			curl_close($curl);
			 
			if ($err) {
			  return "cURL Error #:" . $err;
			} else {
			  return json_decode($response);
			}
		}
		
		/**
			* Generates the headers to pass to API request.
		*/
		public function get_headers($token)
		{
			if(!empty($token)){
				$getHead = array(
				'authorization: Bearer  '.$token,
	            'content-type: application/json',
				);
			}else{
				$getHead = array(
	            'content-type: application/json',
				);
			}

			return $getHead;
			
		}

	}
