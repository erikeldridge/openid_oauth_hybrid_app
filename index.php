<!-- 
/**
 * This is the init script.  Load this in a browser and click the log in button.   
 * After rolling through the openid flow, it'll call back to proxy.php
 * to fetch profile data, and then display profile pic and name.
 */ 
 -->

<div style="border-bottom: 1px solid #ddd; padding: 1ex" id="header">
    <div style="display:none" id="auth">
        <a href="openid/?openid_identifier=yahoo.com">
            <img src="http://l.yimg.com/a/i/reg/openid/buttons/2_new.png" id="openid" style="border:none"/>
        </a>
        <p>--- or ---</p>
        <!-- oauthmod -->
        <a href="oauth/" id="oauth">
            auth w/ oauth
        </a>
    </div>
    <div id="profile" style="display:none">
        <span id="name"></span> - <a href="." id="logout">logout</a><br/>
        <img id="pic" src="http://l.yimg.com/a/i/ww/met/anim_loading_sm_082208.gif"/>
    </div>
</div>

<script type="text/javascript" src="http://yui.yahooapis.com/combo?3.0.0b1/build/yui/yui-min.js"></script>
<script>
    
YUI().use('node', 'event', 'io', 'json', 'cookie', function (Y) {
    
    var proxyUri = 'proxy.php',
        handleIoComplete = function (id, o, args) {
            
            //Try/catch described in http://developer.yahoo.com/yui/3/json/#errors
            try {
                var json = Y.JSON.parse(o.responseText);
                Y.log(json);
                    
                //display profile pic & nickname.
                Y.get('#profile').setStyle('display', 'block');
                Y.get('#name').set('innerHTML', json.query.results.profile.nickname);
                Y.get('#pic').set('src', json.query.results.profile.image.imageUrl);
                
                //show log out link
                Y.get('#logout').on("click", function () {
                    Y.Cookie.remove('session');
                });
                
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
                checkPopup = function () {console.log('foo');
                    
                    //If openid is defined, auth is complete, so ...
            		if (popup.session) {
		                
		                //register session state
                        Y.Cookie.set('session', 'true');
                        
            			//... hide openid/oauth ui, ...
            			Y.get('#auth').setStyle('display', 'none');
			            
            			//... fetch profile data, ...
                        Y.on('io:complete', 
                            handleIoComplete, 
                            this                            
                        );
                        Y.io(proxyUri);
                        
                        //... stop monitoring popup.
                	    clearInterval(popupCheckInterval);
                	    
            		//If popup handle ceases to point to anything, ...
            		} else if (Y.Lang.isUndefined(popup)) {
            		
            		    //... stop monitoring popup.
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
        if (e.target.get('id') && ('openid' === e.target.get('id'))) {
            
            //...cancel default action and ...
            e.halt();            
            
            //...init openid authentication in popup using href of a-tag around button.
            launchPopup(e.target.get('parentNode').get('href'));
            
        } else if (e.target.get('id') && ('oauth' === e.target.get('id'))) {

            //...cancel default action and ...
            e.halt();            

            //...init openid authentication in popup using href of a-tag around button.
            launchPopup(e.target.get('href'));
        }
    };
    
    //Init.
    var session = Y.Cookie.get('session');
    
    //If cookie doesn't exist, user's not logged in and/or is not registered, so ...
    if ('true' === session) {
        Y.on('io:complete', 
            handleIoComplete, 
            this                            
        );
        Y.io(proxyUri);
    } else {
    
    	//... reveal log in link and ...
        Y.get('#auth').setStyle('display', 'block').on("click", handleClick);
        
    }	
});

</script>