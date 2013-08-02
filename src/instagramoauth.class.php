<?php

/**
 * PHP wrapper/library for use with Instagram's REST API
 *
 * - Handles authorization and access token request upon instantiation
 * - Saves initial user data returned by Instagram upon access token request
 * - Provides convenience wrappers for POST, GET, PUT, and DELETE
 * - Provides convenience method for retrieving images
 *
 * Inspired by Twitter OAuth by Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * @author      Bagus Tri K <hello@bagustri.com>
 * @copyright   Bagus Tri K (c) 2013
 * @license     The MIT License (MIT) http://opensource.org/licenses/MIT
 * @version     0.1
 * @see         Instagram clients management page (http://instagram.com/developer/clients/manage/)
 * @see         Instagram API endpoint documentation (http://instagram.com/developer/endpoints/)
 * @since       29/07/2013
 */

/**
 * Class InstagramOAuth
 */
class InstagramOAuth {
	// Authorize URL
	const AUTHORIZEURL = "https://api.instagram.com/oauth/authorize/";

	// Access token URL
	const ACCESSTOKENURL = "https://api.instagram.com/oauth/access_token";

	// APICallURL
	const APIURL = "https://api.instagram.com/v1/";

	// Binary request
	const BINARYREQUEST = "binary";

	// String request
	const STRINGREQUEST = "string";

	// Comments permission
	const PERMISSION_COMMENTS = "comments";

	// Relationships permission
	const PERMISSION_RELATIONSHIPS = "relationships";

	// Like permission
	const PERMISSION_LIKES = "likes";

	// cURL option defaults
	private $curl_opts = array(
		CURLOPT_HEADER          =>  FALSE,
		CURLOPT_RETURNTRANSFER  =>  TRUE,
		CURLOPT_HTTPGET         =>  TRUE,
		CURLOPT_FAILONERROR     =>  TRUE,
		CURLOPT_CONNECTTIMEOUT  =>  30,
		CURLOPT_TIMEOUT         =>  30
	);

	// Application client ID
	private $client_id;

	// Application client secret
	private $client_secret;

	// Redirect URL
	private $redirect_url;

	// Permissions requested
	private $permissions;

	// Authorization code
	private $code;

	// Access token
	private $access_token;

	// HTTP response code
	private $http_code;

	// Error code
	private $error_code;

	// Error message
	private $error_message;

	// User object
	private $user_info;

	// Request response
	private $response;

	// Last request type
	private $last_request = self::STRINGREQUEST;

	// API authorized and ready to use status
	private $ready = FALSE;

	// JSON decode
	var $decodeJSON = TRUE;

	/**
	 * @param string  $client_id      Application client ID
	 * @param string  $client_secret  Application client secret
	 * @param string  $redirect_url   Redirect URL as per application setting
	 * @param mixed   $perms          Additional permissions required
	 */
	function __construct($client_id, $client_secret, $redirect_url, $perms = array()) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_url = $redirect_url;
		$this->permissions = $perms;

		// Authorization already granted or access token already obtained
		if (!empty($_GET["code"]) || !empty($_GET["accesstoken"])) {
			// Authorized but no access token
			if (empty($_GET["accesstoken"])) {
				$this->getAccessToken($_GET["code"]);
			} else {
				$this->ready = TRUE;
			}
		} else {
			$this->getAuthorizationCode();
		}
	}

	/**
	 * Get authorization code
	 */
	private function getAuthorizationCode() {
		$params = array(
			"client_id"		=>	$this->client_id,
			"redirect_uri"	=>	$this->redirect_url,
			"response_type"	=>	"code"
		);

		if (is_array($this->permissions) && count($this->permissions) > 0) {
			$params["scope"] = implode(" ", $this->permissions);
		}

		$params_string = http_build_query($params);

		header("Location: " . sprintf("%s?%s", self::AUTHORIZEURL, $params_string));
	}

	/**
	 * Get access token, must have valid authorization code
	 *
	 * @param string      $code   Authorization code
	 * @throws Exception
	 */
	private function getAccessToken($code) {
		$this->code = $code;

		$params = array(
			"client_id"		=>	$this->client_id,
			"client_secret"	=>	$this->client_secret,
			"grant_type"	=>	"authorization_code",
			"redirect_uri"	=>	$this->redirect_url,
			"code"			=>	$this->code
		);

		$params_string = http_build_query($params);

		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_opts);
		curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded"));
    curl_setopt($ch, CURLOPT_URL, self::ACCESSTOKENURL);

		$this->response = curl_exec($ch);
		if ($this->response === FALSE) {
			$this->error_code = curl_errno($ch);
			$this->error_message = curl_error($ch);
		}

		$result = json_decode($this->response, TRUE);

		$this->http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    $this->access_token = $result["access_token"];
    $this->user_info = $result["user"];

    curl_close($ch);

		if ($this->http_code !== 200) {
			throw new Exception("Error. Unable to obtain access token. (HTTP: " . $this->http_code . ")");
		}

		$this->ready = TRUE;
	}

	/**
	 * GET request wrapper
	 *
	 * @param   string  $endpoint   API endpoint URL
	 * @param   array   $params     Optional parameters to include with request
	 */
	function get($endpoint, $params = array()) {
		$this->_makeRequest("GET", $endpoint, $params);
	}

	/**
	 * GET binary request wrapper
	 *
	 * @param   string  $url    Image URL
	 */
	function getImage($url) {
		$this->_makeBinaryRequest($url);
	}

	/**
	 * POST request wrapper
	 *
	 * @param   string      $endpoint   API endpoint URL
	 * @param   array       $params     Optional parameters to include with request
	 */
	function post($endpoint, $params = array()) {
		$this->_makeRequest("POST", $endpoint, $params);
	}

	/**
	 * DELETE request wrapper
	 *
	 * @param   string  $endpoint   API endpoint URL
	 * @param   array   $params     Optional parameters to include with request
	 */
	function delete($endpoint, $params = array()) {
		$this->_makeRequest("DELETE", $endpoint, $params);
	}

	/**
	 * PUT request wrapper
	 *
	 * @param   string  $endpoint   API endpoint URL
	 * @param   array   $params     Optional parameters to include with request
	 */
	function put($endpoint, $params = array()) {
		$this->_makeRequest("PUT", $endpoint, $params);
	}

	/**
	 * Make binary HTTP request
	 *
	 * @param   string      $url    URL of file to fetch
	 * @throws  Exception
	 */
	private function _makeBinaryRequest($url) {
		$this->last_request = self::BINARYREQUEST;

		$ch = curl_init($url);
		curl_setopt_array($ch, $this->curl_opts);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);

		$this->response = curl_exec($ch);
		$this->http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

		if ($this->response === FALSE) {
			$this->error_code = curl_errno($ch);
			$this->error_message = curl_error($ch);
		}

		curl_close($ch);

		if ($this->http_code !== 200) {
			throw new Exception("Error. Binary request failed. (HTTP: " . $this->http_code . ")");
		}
	}

	/**
	 * Make HTTP request
	 *
	 * @param   string      $verb       HTTP method to use
	 * @param   string      $endpoint   API endpoint URL
	 * @param   array       $params     Optional parameters to include with request
	 * @throws  Exception
	 */
	private function _makeRequest($verb, $endpoint, $params = array()) {
		$this->last_request = self::STRINGREQUEST;

		// PUT or DELETE
		// Use Instagram's provided convenience parameter for better compatibility
		if (stripos($verb, "PUT") !== FALSE ||
				stripos($verb, "DELETE") !== FALSE) {
			$params["_method"] = $verb;
		}

		$params["access_token"] = $this->access_token;

		$params_string = http_build_query($params);

		$endpoint = trim($endpoint, "/");

		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_opts);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded"));

		// GET request
		if (stripos($verb, "GET") !== FALSE) {
			curl_setopt($ch, CURLOPT_URL, sprintf("%s%s?%s", self::APIURL, $endpoint, $params_string));
		} else {
			curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
			curl_setopt($ch, CURLOPT_URL, $endpoint);
		}

		$this->response = curl_exec($ch);
		$this->http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

		if ($this->response === FALSE) {
			$this->error_code = curl_errno($ch);
			$this->error_message = curl_error($ch);
		}

		curl_close($ch);

		if ($this->http_code !== 200) {
			throw new Exception("Error. Request failed. (HTTP: " . $this->http_code . ")");
		}
	}

	/**
	 * API response getter
	 *
	 * @param   bool  $decode
	 *
	 * @return  mixed
	 */
	function response($decode = TRUE) {
		return ($this->decodeJSON || $decode) && $this->last_request !== self::BINARYREQUEST ?
				json_decode($this->response, TRUE) :
				$this->response;
	}

	/**
	 * Last HTTP code getter
	 *
	 * @return mixed
	 */
	function httpCode() {
		return $this->http_code;
	}

	/**
	 * User info getter
	 *
	 * @param   string $key
	 *
	 * @return  mixed
	 */
	function getUserInfo($key = "") {
		return empty($key) ?
				$this->user_info :
				$this->user_info[$key];
	}

	/**
	 * API ready state getter
	 *
	 * @return bool
	 */
	function isReady() {
		return $this->ready;
	}

	/**
	 * Override cURL options
	 *
	 * @param string    $option
	 * @param mixed     $value
	 */
	function setCurlOption($option, $value) {
		$this->curl_opts[$option] = $value;
	}
}