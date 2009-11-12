<!-- 
/**
 * This is the init script.  Load this in a browser and click the log in button.   
 * After rolling through the openid flow, it'll call back to proxy.php
 * to fetch profile data, and then display profile pic and name.
 * @copyright (c) 2009, Yahoo! Inc. All rights reserved.
 * @package http://github.com/erikeldridge/openid_oauth_hybrid_app
 * @license code licensed under the BSD License.  See /license.markdown
 */ 
 -->

<div style="border-bottom: 1px solid #ddd; padding: 1ex">
    <a href="openid/?openid_identifier=yahoo.com">
        <img src="http://l.yimg.com/a/i/reg/openid/buttons/2_new.png" style="border:none;display:none" id="loginButton"/>
    </a>
    <div id="profileDisplay" style="display:none">
    </div>
</div>

<script type="text/javascript" src="http://yui.yahooapis.com/combo?3.0.0b1/build/yui/yui-min.js"></script>
<script>
    
YUI().use('node', 'event', 'io', 'json', 'cookie', function (Y) {
    
    var handleIoComplete = function (id, o, args) {
            
            //Try/catch described in http://developer.yahoo.com/yui/3/json/#errors
            try {
                var json = Y.JSON.parse(o.responseText);
                //console.log(json);
                var name = json.query.results.profile.nickname,
                    src = json.query.results.profile.image.imageUrl;
                    
                //display profile pic & nickname.
                Y.get('#profileDisplay').append('<img src="'+src+'" height="48" width="48"/>'+name);
                Y.get('#profileDisplay').setStyle('display', 'block');
            } catch (e) {
                console.log(e);
            }
        },
        handleClick = function (e) {
            
            //Def. handle of openid auth window.
            var popup = null,
            
                //Def. handle for popup monitoring interval described below.
                popupCheckInterval = null,
    
                //Def. fn to catch openid when auth is complete, or handle failure.
                checkPopup = function () {
                    
                    //If openid is defined, auth is complete, so ...
            		if (popup.sessionId) {
		    
            			//... hide log in button, ...
            			Y.get('#loginButton').setStyle('display', 'none');
			            
			            //todo: show profile loading icon while waiting
			            
            			//... fetch profile data, ...
                        var uri = 'proxy.php?sessionId=' + popup.sessionId;

                        Y.on('io:complete', 
                            handleIoComplete, 
                            this                            
                        );
                        Y.io(uri);
                        
                        //... stop monitoring popup.
            			clearInterval(popupCheckInterval);
			
            		//If popup handle ceases to point to anything, ...
            		} else if (Y.Lang.isUndefined(popup)) {
            		    
            		    //... stop monitoring.
            		    clearInterval(popupCheckInterval);
            		    
            	    }
            	},
            	
            	//Define fn to spawn popup window for openid authentication.
            	launchPopup = function (url) {
                    popup = window.open(
                        url, 

                        //unique id
                        new Date().getTime(),
            
                        'toolbar=0,scrollbars=1,location=1,statusbar=1,menubar=0,'
                        +'resizable=1,width=500,height=500,left = 200,top = 200'
                    );
                    
                    //Monitor popup to determine when auth is complete.
            		popupCheckInterval = window.setInterval(
            		    checkPopup, 
            		    1000
            		);
            	};
        	
        //If login button clicked ...
        if (e.target.get('id') && ('loginButton' === e.target.get('id'))) {
            
            //...cancel default action and ...
            e.halt();            
            
            //...init openid authentication in popup using href of a-tag around button.
            launchPopup(e.target.get('parentNode').get('href'));
        }
    };
    
    //Init.
    var sessionId = Y.Cookie.get('sessionId');
    
    //If cookie doesn't exist, user's not logged in and/or is not registered, so ...
    if (!sessionId) {

    	//... reveal log in link and ...
        Y.get('#loginButton').setStyle('display', 'block');
    }
    
    //... let callback trigger display actions.
    Y.on("click", handleClick);
	
});

</script>