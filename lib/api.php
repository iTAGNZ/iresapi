<?php
/*
  ------------------------------------------------
  ------ Demonstration code for iRES API v1 ------
  ------------------------------------------------
*/

class iRESAPI {

	// define API URL (format: [server]/api/[version])
	public $url = 'https://ires.co.nz/api/v1';
	
	// define default search options
	public $limit = null;
	public $order = null;
	
	// define default format for returned data
	public $format = 'JSON';
	
	private $api_url = '';
	private $action = '';
	private $api_token = '';
	private $private_key = '';
	private $data_to_send = '';
	
	public $response_headers = null;
	public $response_code = null;
	public $die_on_error = false;
	
	// instantiate required variables
	public function __construct($api_token = null, $private_key = null) {
		if(!$api_token)
			die('You must provide an API token!');

		if(!$private_key)
			die('You must provide a private key!');
			
		// Set the required API token. Your credentials for iRES resource access will be
		// automatically calculated from this API token.
		$this->api_token = $api_token;
		$this->private_key = $private_key;
	}
	
	// --- Get operators: CURL option
	// http://php.net/curl
	public function get_operators_curl() {
		$this->set_api_url('operators');
		$curl = curl_init($this->api_url);
		if(!$curl)
			throw new Exception('Cannot open CURL.');
		
		// set options for CURL
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->get_data());
		curl_setopt($curl, CURLOPT_HEADER, 1); // return response header code
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); // disable the "100 Continue" header
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($curl);
		if(!$response)
			throw new Exception('Could not get response from CURL.');
		
		list($headers, $body) = explode("\r\n\r\n", $response, 2); // split headers and body
		$this->response_headers = explode("\n", $headers); // split headers into array by new lines
		// throw exception when header is incorrect
		$this->check_response_headers();

		curl_close($curl);
		return $body;
	}
	
	// --- Get operators: Stream option
	// http://php.net/stream_context_create
	public function get_operators_stream($method = 'POST') {
		$this->set_api_url('operators');
		
		// params for stream
		$params = http_build_query($this->get_data());
		
		// initiate stream
		$send_params = array(
			'http' => array(
				'method' => $method,
				'ignore_errors' => true
			)
		);
		
		// set variables appropriately
		if($method == 'POST')
			$send_params['http']['content'] = $params;
		else
			$this->api_url .= '?' . $params;
		
		// get and save headers
		stream_context_set_default($send_params);
		$headers = @get_headers($this->api_url, 1);
		$this->response_headers = $headers;
		
		// throw exception when header is incorrect
		$this->check_response_headers();
		
		// create request
		$request = stream_context_create($send_params);
		
		// make request to API
		$fp = @fopen($this->api_url, 'rb', false, $request);
		if(!$fp)
			throw new Exception('Stream: Error opening URL: ' . $this->api_url);
			
		// get results
		$response = @stream_get_contents($fp);
		if($response === false)
			throw new Exception('Stream: Error getting response.');
			
		return $response;
	}
	
	// --- Get operators: PECL_HTTP option (procedural, requires PECL_HTTP enabled)
	// http://www.php.net/manual/en/function.http-post-data.php
	public function get_operators_pecl() {
		$this->action = 'operators';
		
		// check that extension is enabled
		if(!extension_loaded('pecl_http'))
			throw new Exception('PECL_HTTP extension is not loaded on this server.');
		
		// everything is performed with one line
		$response = http_post_data($this->api_url, $this->get_data());
		if(!$response)
			throw new Exception('PECL: Error processing http_post_data request.');
			
		return $response;
	}
	
	// --- Get operators: PECL_HTTP option (OO, requires PECL_HTTP enabled)
	// http://www.php.net/manual/en/class.httprequest.php
	public function get_operators_pecl_oo() {
		$this->action = 'operators';
		
		// check that extension is enabled
		if(!extension_loaded('pecl_http'))
			throw new Exception('PECL_HTTP extension is not loaded on this server.');
		
		$request = new HTTPRequst($this->api_url, HTTP_METH_POST);
		if(!$request)
			throw new Exception("Error instantiating HTTPRequest class to $this->api_url");
			
		$request->setRawPostData($this->get_data());
		$request->send();
		
		$response = $request->getResponseBody();
		if(!$response)
			throw new Exception("Error getting a response from HTTPRequest: $this->api_url");
		return $response;
	}
	
	private function get_data() {
		// Define data to send via appropriate HTTP method
		$data = array('token' => $this->api_token);
		
		// these are optional parameters, refer to the docs
		if($this->order)
			$data['order'] = $this->order;
		if($this->limit)
			$data['limit'] = $this->limit;
		
		// generate HMAC checksum of data
		$hmac = hash_hmac('sha256', implode($data), $this->private_key);
		$data['checksum'] = $hmac;

		return $data;
	}
	
	private function set_api_url($action) {
		$this->action = $action;
		$this->api_url = $this->url  . '/' . $action . '.' . strtolower($this->format);
	}

	// Check response headers for 200, throw exception when otherwise
	private function check_response_headers() {
		$code = $this->get_http_response_code($this->response_headers);
		$this->response_code = (int)$code;
		if($this->response_code != 200 && $this->die_on_error)
			throw new Exception('Error: response header is: ' . $this->response_headers[0]);
	}
	
	// Return response code from headers, e.g. 405 from HTTP/1.0 405 Method Not Allowed
	public function get_http_response_code($response_headers) {
		return substr($response_headers[0], 9, 3);
	}
}

?>