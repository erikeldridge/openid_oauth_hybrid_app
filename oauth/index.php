<?php
require('../config.inc.php');
require('../../yosdk/OAuth.php');
//BEGIN: get req token
//prep request
$consumer = new OAuthConsumer(YAHOO_OAUTH_APP_KEY, YAHOO_OAUTH_APP_SECRET, YAHOO_OAUTH_APP_ID);//key/secret from Y!
$url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
$request = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, array('oauth_callback' => YAHOO_OAUTH_APP_CALLBACK_URI));
$request->sign_request(new OAuthSignatureMethod_PLAINTEXT(), $consumer, NULL);
//make request
$ch = curl_init($url);
$options = array(
	CURLOPT_POSTFIELDS => $request->to_postdata(),
	CURLOPT_RETURNTRANSFER => true
);
curl_setopt_array($ch, $options);
parse_str(curl_exec($ch), $resp);
curl_close($ch);
//extract token from response
$requestToken = new stdclass();
$requestToken->key = $resp["oauth_token"];
$requestToken->secret = $resp["oauth_token_secret"];

//save the token data somewhere persistent
file_put_contents('../requestToken.txt', json_encode($requestToken));

//BEGIN: direct user to Y! for auth
$url = sprintf("https://%s/oauth/v2/request_auth?oauth_token=%s", 
	'api.login.yahoo.com', 
	urlencode($requestToken->key)
);
header('location: '.$url);
?>