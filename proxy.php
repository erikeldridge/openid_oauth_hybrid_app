<?php
/**
 * This is a proxy script for making 3-leg requests using php sdk.  
 * Currently, it is hardcoded to request the profile info only.
 */
 
require_once 'config.inc.php';
require_once '../yosdk/OAuth.php';

$consumer = new OAuthConsumer(YAHOO_OAUTH_APP_KEY, YAHOO_OAUTH_APP_SECRET);

//extract request token from storage
$accessToken = json_decode(file_get_contents('token.txt'));

//prep request for access token
$url = 'http://query.yahooapis.com/v1/yql';
$params = array('q'=>'select * from social.profile where guid=me');
$request = OAuthRequest::from_consumer_and_token($consumer, $accessToken, 'GET', $url, $params);
$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $accessToken);
$headers = array(
	"Accept: application/json"
);

//make request
$ch = curl_init($request->to_url());
$options = array(
	CURLOPT_HTTPHEADER => $headers,
	CURLOPT_RETURNTRANSFER => true
);
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
curl_close($ch);

//error-tip: if request is rejected, refresh access token and re-request

//output
header('Content-type: application/json');
echo $response;
?>