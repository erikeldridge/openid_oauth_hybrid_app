<?php

/**
 * This script generates the openid auth url and redirects to it.
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

//include openid files
require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/SReg.php";
require_once "Auth/OpenID/PAPE.php";

$openidFileStore = new Auth_OpenID_FileStore('/tmp/');
$openidConsumer =& new Auth_OpenID_Consumer($openidFileStore);

//this could just as easily be set dynamically
$openidIdentifier = 'yahoo.com';
$openidAuthRequest = $openidConsumer->begin($openidIdentifier);

//Add simple reg support.
//Note: domains implementing Yahoo! hybrid auth must be whitelisted
$openidSimpleRegRequest = Auth_OpenID_SRegRequest::build(
    array('nickname'), // req'd
    array('fullname', 'email') // optional
);
$openidAuthRequest->addExtension($openidSimpleRegRequest);

//url for openid provider log in page
$openidLoginRedirectUrl = $openidAuthRequest->redirectURL(
    OPENID_REALM_URI,
    OPENID_RETURN_TO_URI
);

//add hybrid auth fields
$additionalFields = array(
    'openid.ns.oauth' => 'http://specs.openid.net/extensions/oauth/1.0',
    'openid.oauth.consumer' => YAHOO_OAUTH_APP_KEY
);

$openidLoginRedirectUrl .= '&'.http_build_query($additionalFields);

header('Location: '.$openidLoginRedirectUrl);
?>