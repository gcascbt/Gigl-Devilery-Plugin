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
				
				$params = array(
               'username'        => $username,
               'Password'     => $password,
               'SessionObj'    => "",
               'token' => '0EIX6&bUKpiA$e^uWjFL%5B(3RebDUgcqn*fHaDn',
				);
				
				$response = $this->api_request(
                'login',
                $params
				);
				add_post_meta('43534','post_thee',$response);
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
			//$uri = "https://acedu.revocube.tech/wp-json/wplms/v2/course/1226";
			 $uri = "{$this->request_url}{$endpoint}";
			 //if($method =='post'){
				 $arg = array(
				 	'method' => $method,
				 	'body'        => $args,
	    			'timeout'     => '50',
	    			'redirection' => '5',
	    			'httpversion' => '1.0',
	    			'blocking'    => true,
	    			'headers' => $this->get_headers($token),
	    			'cookies'     => array(),

				 );
				$getApiResponse = wp_remote_request( $uri, $arg );
			//}else{
				//$arg = array(
	    		//	'headers'     => get_headers(),
				// );
				//$getApiResponse = wp_remote_get( $url, $arg );
			//}
			$bodyApiResponse = wp_remote_retrieve_body($getApiResponse);
			return $bodyApiResponse;
{"Code":"200","ShortDescription":"Operation was successful!","Object":{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1laWQiOiJkYzhiOWFlMC1mZWExLTQ4MGYtOGZiYS1hYjg3Y2IwM2IwMTMiLCJ1bmlxdWVfbmFtZSI6IkFDQzAwMTA1MiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vYWNjZXNzY29udHJvbHNlcnZpY2UvMjAxMC8wNy9jbGFpbXMvaWRlbnRpdHlwcm92aWRlciI6IkFTUC5ORVQgSWRlbnRpdHkiLCJBc3BOZXQuSWRlbnRpdHkuU2VjdXJpdHlTdGFtcCI6ImMxODllNWRlLTk4N2UtNDFkYi04MmUyLWJiYzBiYzM5YTVkNyIsInJvbGUiOiJUaGlyZFBhcnR5IiwiQWN0aXZpdHkiOlsiQ3JlYXRlLlRoaXJkUGFydHkiLCJEZWxldGUuVGhpcmRQYXJ0eSIsIlVwZGF0ZS5UaGlyZFBhcnR5IiwiVmlldy5UaGlyZFBhcnR5Il0sIlByaXZpbGVnZSI6IlB1YmxpYzpQdWJsaWMiLCJpc3MiOiJodHRwczovL2FnaWxpdHlzeXN0ZW1hcGlkZXZtLmF6dXJld2Vic2l0ZXMubmV0LyIsImF1ZCI6IjQxNGUxOTI3YTM4ODRmNjhhYmM3OWY3MjgzODM3ZmQxIiwiZXhwIjoxNjQyODAyNDk3LCJuYmYiOjE2NDIzNzA0OTd9.jMzvdPX9A-fOLhYNQksgJaQfQKHdGYAdlUrRJUE-_js","token_type":"bearer","expires_in":431999,"UserId":"dc8b9ae0-fea1-480f-8fba-ab87cb03b013","UserName":"ACC001052","FirstName":"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED","LastName":"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED","Email":"hr@flutterwavego.com","UserChannelType":"Corporate","SystemUserRole":"Third Party Customers","PhoneNumber":"+2348168535534","IsActive":"True","Organization":"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED","Organisation":"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED","UserChannelCode":"ACC001052","PictureUrl":null,"IsMagaya":"False","IsInternational":"False","BVN":null,"Rank":"Basic","ReferralCode":null,"CountryType":null,"IsRequestNewPassword":"False","WalletAddress":null,"PrivateKey":null,"PublicKey":null,"Claim":"Create.ThirdParty,Delete.ThirdParty,Update.ThirdParty,View.ThirdParty,Public:Public","Role":"ThirdParty",".issued":"Sun, 16 Jan 2022 22:01:37 GMT",".expires":"Fri, 21 Jan 2022 22:01:37 GMT"},"magayaErrorMessage":"license_in_use","Cookies":null,"more_reults":0,"Total":0.0,"RefCode":null,"Shipmentcodref":null,"ValidationErrors":{},"VehicleType":null,"ReferrerCode":null,"AverageRatings":0.0,"IsVerified":false,"PartnerType":null,"IsEligible":false,"VehicleDetails":null,"BankName":null,"AccountName":null,"AccountNumber":null}
O:8:"stdClass":20:{s:4:"Code";s:3:"200";s:16:"ShortDescription";s:25:"Operation was successful!";s:6:"Object";O:8:"stdClass":30:{s:12:"access_token";s:773:"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1laWQiOiJkYzhiOWFlMC1mZWExLTQ4MGYtOGZiYS1hYjg3Y2IwM2IwMTMiLCJ1bmlxdWVfbmFtZSI6IkFDQzAwMTA1MiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vYWNjZXNzY29udHJvbHNlcnZpY2UvMjAxMC8wNy9jbGFpbXMvaWRlbnRpdHlwcm92aWRlciI6IkFTUC5ORVQgSWRlbnRpdHkiLCJBc3BOZXQuSWRlbnRpdHkuU2VjdXJpdHlTdGFtcCI6ImMxODllNWRlLTk4N2UtNDFkYi04MmUyLWJiYzBiYzM5YTVkNyIsInJvbGUiOiJUaGlyZFBhcnR5IiwiQWN0aXZpdHkiOlsiQ3JlYXRlLlRoaXJkUGFydHkiLCJEZWxldGUuVGhpcmRQYXJ0eSIsIlVwZGF0ZS5UaGlyZFBhcnR5IiwiVmlldy5UaGlyZFBhcnR5Il0sIlByaXZpbGVnZSI6IlB1YmxpYzpQdWJsaWMiLCJpc3MiOiJodHRwczovL2FnaWxpdHlzeXN0ZW1hcGlkZXZtLmF6dXJld2Vic2l0ZXMubmV0LyIsImF1ZCI6IjQxNGUxOTI3YTM4ODRmNjhhYmM3OWY3MjgzODM3ZmQxIiwiZXhwIjoxNjQyODAyODExLCJuYmYiOjE2NDIzNzA4MTF9.z8Agp_qyCk3rY0zK_BQpc36aKQ83MiuIqLPHbGPHO8U";s:10:"token_type";s:6:"bearer";s:10:"expires_in";i:431999;s:6:"UserId";s:36:"dc8b9ae0-fea1-480f-8fba-ab87cb03b013";s:8:"UserName";s:9:"ACC001052";s:9:"FirstName";s:40:"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED";s:8:"LastName";s:40:"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED";s:5:"Email";s:20:"hr@flutterwavego.com";s:15:"UserChannelType";s:9:"Corporate";s:14:"SystemUserRole";s:21:"Third Party Customers";s:11:"PhoneNumber";s:14:"+2348168535534";s:8:"IsActive";s:4:"True";s:12:"Organization";s:40:"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED";s:12:"Organisation";s:40:"FLUTTERWAVE TECHNOLOGY SOLUTIONS LIMITED";s:15:"UserChannelCode";s:9:"ACC001052";s:10:"PictureUrl";N;s:8:"IsMagaya";s:5:"False";s:15:"IsInternational";s:5:"False";s:3:"BVN";N;s:4:"Rank";s:5:"Basic";s:12:"ReferralCode";N;s:11:"CountryType";N;s:20:"IsRequestNewPassword";s:5:"False";s:13:"WalletAddress";N;s:10:"PrivateKey";N;s:9:"PublicKey";N;s:5:"Claim";s:83:"Create.ThirdParty,Delete.ThirdParty,Update.ThirdParty,View.ThirdParty,Public:Public";s:4:"Role";s:10:"ThirdParty";s:7:".issued";s:29:"Sun, 16 Jan 2022 22:06:51 GMT";s:8:".expires";s:29:"Fri, 21 Jan 2022 22:06:51 GMT";}s:18:"magayaErrorMessage";s:14:"license_in_use";s:7:"Cookies";N;s:11:"more_reults";i:0;s:5:"Total";d:0;s:7:"RefCode";N;s:14:"Shipmentcodref";N;s:16:"ValidationErrors";O:8:"stdClass":0:{}s:11:"VehicleType";N;s:12:"ReferrerCode";N;s:14:"AverageRatings";d:0;s:10:"IsVerified";b:0;s:11:"PartnerType";N;s:10:"IsEligible";b:0;s:14:"VehicleDetails";N;s:8:"BankName";N;s:11:"AccountName";N;s:13:"AccountNumber";N;}
			// $postFields =  json_encode($args);
			// $curl = curl_init();

			// curl_setopt_array($curl, array(
			//   CURLOPT_URL => $uri,
			//   CURLOPT_RETURNTRANSFER => true,
			//   CURLOPT_ENCODING => '',
			//   CURLOPT_MAXREDIRS => 10,
			//   CURLOPT_TIMEOUT => 0,
			//   CURLOPT_FOLLOWLOCATION => true,
			//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			//   CURLOPT_CUSTOMREQUEST => strtoupper($method),
			//   CURLOPT_POSTFIELDS => $postFields,
			//   CURLOPT_HTTPHEADER => $this->get_headers($token),
			// ));
			 
			// $response = curl_exec($curl);
			// $err = curl_error($curl);
			 
			// curl_close($curl);
			 
			// if ($err) {
			//   return "cURL Error #:" . $err;
			// } else {
			//   return json_decode($response);
			// }
		}
		
		/**
			* Generates the headers to pass to API request.
		*/
			public function get_headers($token)
		{
			if(!empty($token)){
				$getHead = array(
	            'Authorization:' => 'Bearer  '.$token,
				);
			}else{
				$getHead = array();
			}

			return $getHead;
			
		}
		// public function get_headers($token)
		// {
		// 	if(!empty($token)){
		// 		$getHead = array(
		// 		'authorization: Bearer  '.$token,
	 //            'content-type: application/json',
		// 		);
		// 	}else{
		// 		$getHead = array(
	 //            'content-type: application/json',
		// 		);
		// 	}

		// 	return $getHead;
			
		// }

	}
