<?php
/**
 * This is a proxy script for making 3-leg requests using php sdk.  
 * Currently, it is hardcoded to request the profile info only.
 * @copyright (c) 2009, Yahoo! Inc. All rights reserved.
 * @package http://github.com/erikeldridge/openid_oauth_hybrid_sample
 * @license code licensed under the BSD License.  See /license.markdown
 */
 
//fetch session init'd by /openid/return_to.php script.
//note: we use server-side instead of cookie storage cause /index.php never refreshes.
session_id($_GET['sessionId']);
session_start();

require_once 'config.inc.php';

//ammend include path so we can include files consistently
$includePath = get_include_path().PATH_SEPARATOR
    .OPENID_INCLUDE_PATH.PATH_SEPARATOR
    .OAUTH_INCLUDE_PATH.PATH_SEPARATOR;
set_include_path($includePath);

require_once 'Yahoo.inc';

//safely fetch input
$filters = array(
    'openidHash' => FILTER_SANITIZE_ENCODED
);
$input = filter_var_array($_REQUEST, $filters);

$store = new NativeSessionStore();

//init Y! session for easy data fetch & oauth token mgmt
$yahooSession = YahooSession::requireSession(
    YAHOO_OAUTH_APP_KEY, 
    YAHOO_OAUTH_APP_SECRET, 
    YAHOO_OAUTH_APP_ID,
    YAHOO_OAUTH_APP_CALLBACK_URI, 
    $store
);

header('Content-type: application/json');

//we can get name from sreg, but we need the pic too, so fetch everything
echo json_encode($yahooSession->query('select * from social.profile where guid=me'));
?>