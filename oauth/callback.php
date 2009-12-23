<?php
require('../config.inc.php');
require('../../yosdk/OAuth.php');
$consumer = new OAuthConsumer(YAHOO_OAUTH_APP_KEY, YAHOO_OAUTH_APP_SECRET, YAHOO_OAUTH_APP_ID);//key/secret from Y!

//extract request token from storage
$requestToken = json_decode(file_get_contents('../requestToken.txt'));//error-tip: if token invalid, re-fetch request token

//prep request for access token
$url = 'https://api.login.yahoo.com/oauth/v2/get_token';
$request = OAuthRequest::from_consumer_and_token($consumer, $requestToken, 'POST', $url, array('oauth_verifier'=>$_GET['oauth_verifier']));
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

//extract token params from response
$now = time();
$accessToken = new stdclass();
$accessToken->key = $response["oauth_token"];
$accessToken->secret = $response["oauth_token_secret"];
$accessToken->guid = $response["xoauth_yahoo_guid"];
$accessToken->consumer = $consumer;
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
    $accessToken->handleExpires = $now + $response["oauth_authorization_expires_in"];
}
else {
    $accessToken->handleExpires = -1;
}
//save the token data somewhere persistent
file_put_contents('../accessToken.txt', json_encode($accessToken));
?>

You have now authorized access to your Yahoo! data.  This window will close in 3 seconds.
<script>

// communicate session state to popup monitor
var session = 'true';

window.setTimeout(window.close, 3000);
</script>
