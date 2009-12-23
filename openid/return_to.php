<?php

/**
 * This script exchanges oauth req token for access token and stores it,
 * and then passes the session identifier back to the init script (/index.php)
 */
 
require_once '../config.inc.php';
require('../../yosdk/OAuth.php');

//safely fetch input
$filters = array(
    'openid_sreg_nickname' => FILTER_SANITIZE_ENCODED,
    'openid_sreg_fullname' => FILTER_SANITIZE_ENCODED,
    'openid_sreg_email' => FILTER_SANITIZE_ENCODED,
    'openid_identity' => FILTER_SANITIZE_ENCODED,
    'openid_oauth_request_token' => FILTER_SANITIZE_ENCODED
);
$input = filter_var_array($_REQUEST, $filters);

// exchange request token for access token
// ref: YahooAuthorization::getAccessTokenProxy
if(isset($input['openid_oauth_request_token'])){
	
	//use oauth consumer to sign request for access token
    $consumer = new OAuthConsumer(YAHOO_OAUTH_APP_KEY, YAHOO_OAUTH_APP_SECRET, YAHOO_OAUTH_APP_ID);
	
	//format request token as expected by oauth lib
	$requestToken = new stdclass();
	$requestToken->key = $input['openid_oauth_request_token'];
	
	//ref: http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html#AuthTokenReq
	$requestToken->secret = '';
	
	$url = 'https://api.login.yahoo.com/oauth/v2/get_token';
    $request = OAuthRequest::from_consumer_and_token($consumer, $requestToken, 'POST', $url, array());
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $requestToken);
    $headers = array(
    	"Accept: application/json"
    );

    //make request
    $ch = curl_init($url);
    $options = array(
        CURLOPT_POST=> true,
    	CURLOPT_POSTFIELDS => $request->to_postdata(),
		CURLOPT_HTTPHEADER => $headers,
    	CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $options);
    parse_str(curl_exec($ch), $response);
    curl_close($ch);

	$now = time();
	$accessToken = new stdclass();
	
	//load token w/ openid params
	$accessToken->openid = $input;

    //set statndard oauth params
	$accessToken->key = $response['oauth_token'];
	$accessToken->secret = $response["oauth_token_secret"];
	$accessToken->guid = $response["xoauth_yahoo_guid"];
	
	//note: consumer is the app key not consumer obj
	$accessToken->consumer = YAHOO_OAUTH_APP_KEY;
	
	$accessToken->sessionHandle = $response["oauth_session_handle"];

	// Check to see if the access token ever expires.
	if(array_key_exists("oauth_expires_in", $response)) {
	    $accessToken->tokenExpires = $now + $response["oauth_expires_in"];
	}
	else {
	    $accessToken->tokenExpires = -1;
	}

	// Check to see if the access session handle ever expires.
	if(array_key_exists("oauth_authorization_expires_in", $response)) {
	    $accessToken->handleExpires = $now +
	            $response["oauth_authorization_expires_in"];
	}
	else {
	    $accessToken->handleExpires = -1;
	}
    
	file_put_contents('../token.txt', json_encode($accessToken));
}

?>

You are now logged in.  This window will close in 3 seconds.
<script>

// communicate session state to popup monitor in index.php
var session = 'true';

window.setTimeout(window.close, 3000);
</script>