<?php

/**
 * This script generates the openid auth url w/ ax request and redirects to it.
 */
 
//suppress warnings caused by php openid lib as we need to redirect
error_reporting(E_ERROR | E_PARSE);

//php openid lib requires session
session_start();

//define oauth & openid params
require_once '../config.inc.php';

//ammend include path so we can include files consistently
$includePath = get_include_path().PATH_SEPARATOR
    .OPENID_INCLUDE_PATH.PATH_SEPARATOR
    .OAUTH_INCLUDE_PATH.PATH_SEPARATOR;
set_include_path($includePath);

//include openid files
require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/AX.php";

//init basic openid auth url generation
$openidFileStore = new Auth_OpenID_FileStore('/tmp/');
$openidConsumer =& new Auth_OpenID_Consumer($openidFileStore);

//this could just as easily be set dynamically
$openidIdentifier = 'yahoo.com';
$openidAuthRequest = $openidConsumer->begin($openidIdentifier);

//add openid ax req params for all the fields mentioned in the blog post below
//ref: http://developer.yahoo.net/blog/archives/2009/12/yahoo_openid_now_with_attribute_exchange.html
//ref: http://stackoverflow.com/questions/1183788/example-usage-of-ax-in-php-openid
$openid_ax_attributes = array(
    Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',1,1, 'email'),
    Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson',1,1, 'fullname'),
    Auth_OpenID_AX_AttrInfo::make('http://axschema.org/media/image/default',1,1, 'profile_pic'),
    Auth_OpenID_AX_AttrInfo::make('http://axschema.org/person/gender',1,1, 'gender'),
);

$openid_ax_request = new Auth_OpenID_AX_FetchRequest;

foreach($openid_ax_attributes as $attribute){
        $openid_ax_request->add($attribute);
}

$openidAuthRequest->addExtension($openid_ax_request);

//this is the url for openid provider log in page
$openidLoginRedirectUrl = $openidAuthRequest->redirectURL(
    OPENID_REALM_URI,
    OPENID_RETURN_TO_URI
);

//append hybrid auth fields
$additionalFields = array(
    'openid.ns.oauth' => 'http://specs.openid.net/extensions/oauth/1.0',
    'openid.oauth.consumer' => YAHOO_OAUTH_APP_KEY
);

$openidLoginRedirectUrl .= '&'.http_build_query($additionalFields);

//redirect for auth
header('Location: '.$openidLoginRedirectUrl);

?>