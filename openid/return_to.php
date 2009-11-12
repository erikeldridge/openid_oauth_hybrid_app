<?php

/**
 * This script exchanges oauth req token for access token and stores it,
 * and then passes the session identifier back to the init script (/index.php)
 * @copyright (c) 2009, Yahoo! Inc. All rights reserved.
 * @package http://github.com/erikeldridge/openid_oauth_hybrid_sample
 * @license code licensed under the BSD License.  See /license.markdown
 */
 
session_start();

require_once '../config.inc.php';

//ammend include path so we can include files consistently
$includePath = get_include_path().PATH_SEPARATOR
    .OPENID_INCLUDE_PATH.PATH_SEPARATOR
    .OAUTH_INCLUDE_PATH.PATH_SEPARATOR;
set_include_path($includePath);

require_once 'Yahoo.inc';

//safely fetch input
$filters = array(
    'openid.sreg.nickname' => FILTER_SANITIZE_ENCODED,
    'openid.sreg.fullname' => FILTER_SANITIZE_ENCODED,
    'openid.sreg.email' => FILTER_SANITIZE_ENCODED,
    'openid.identity' => FILTER_SANITIZE_ENCODED,
    
    //kludge: actual param is dot-separated, but it's unreadable that way
    'openid_oauth_request_token' => FILTER_SANITIZE_ENCODED
);
$input = filter_var_array($_REQUEST, $filters);

//session identifier
$sessionId = session_id();

// session store interface defined in Yahoo! SDK
$yahooSdkSessionStore = new NativeSessionStore();

// exchange request token for access token
// ref: YahooAuthorization::getAccessTokenProxy
if(isset($input['openid_oauth_request_token'])){
	
	//use oauth consumer to sign request for access token
	$consumer = new OAuthConsumer(YAHOO_OAUTH_APP_KEY, YAHOO_OAUTH_APP_SECRET);
	
	//format request token as expected by oauth lib
	$requestToken = new stdclass();
	$requestToken->key = $input['openid_oauth_request_token'];
	
	//ref: http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html#AuthTokenReq
	$requestToken->secret = '';

    //client defined in Yahoo! SDK
	$client = new OAuthClient($consumer, $requestToken, OAUTH_PARAMS_IN_POST_BODY);
	
	//$YahooConfig["OAUTH_HOSTNAME"] defined in Yahoo! SDK
	$uri = sprintf("https://%s/oauth/v2/get_token", $YahooConfig["OAUTH_HOSTNAME"]);
	
	$response = $client->post($uri);

	parse_str($response["responseBody"], $params);

	$now = time();

	$accessToken = new stdclass();
	
	//note: key is oauth access token.
	//kludge: suspecting php bug - 1st array elem inaccesible by key.
	$accessToken->key = array_shift($params);
	
	$accessToken->secret = $params["oauth_token_secret"];
	$accessToken->guid = $params["xoauth_yahoo_guid"];
	
	//note: consumer is the app key
	$accessToken->consumer = YAHOO_OAUTH_APP_KEY;
	
	$accessToken->sessionHandle = $params["oauth_session_handle"];

	// Check to see if the access token ever expires.
	if(array_key_exists("oauth_expires_in", $params)) {
	    $accessToken->tokenExpires = $now + $params["oauth_expires_in"];
	}
	else {
	    $accessToken->tokenExpires = -1;
	}

	// Check to see if the access session handle ever expires.
	if(array_key_exists("oauth_authorization_expires_in", $params)) {
	    $accessToken->handleExpires = $now +
	            $params["oauth_authorization_expires_in"];
	}
	else {
	    $accessToken->handleExpires = -1;
	}
    
	$yahooSdkSessionStore->storeAccessToken($accessToken);
}

?>

<script>
var sessionId = '<?= $sessionId ?>';
window.setTimeout(window.close, 3000);
</script>