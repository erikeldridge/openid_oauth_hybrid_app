# Hybrid Auth (OpenID + OAuth) application

This app demonstrates a site can log in users, and fetch their Yahoo! profile data, with OpenID + OAuth hybrid auth.  User's are also able to simply authenticate using OAuth.  The end result for either action is that the user's name and profile image are displayed.  The app serves as a way to compare the OAuth and Hybrid auth implementations.

# Prerequisites

   * PHP 5.2
   * The OpenID PHP library
   * The OAuth PHP library
   * A Yahoo! OAuth application
   
# Usage

   1. Upload this directory to a publicly accessible server
   1. Edit config.inc.php to use your Yahoo! OAuth app's id, key, secret, and callback URL
   1. Load _index.php_ in a browser
   1. Click the _Sign in through Yahoo!_ button to initiate the OpenID flow  
   - OR -  
   click the _auth w/ oauth_ link to initiate the OAuth flow
   1. Once logged in, click the _logout_link to log out
   
# License

Software License Agreement (BSD License)
Copyright (c) 2009, Yahoo! Inc.
All rights reserved.

Redistribution and use of this software in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of Yahoo! Inc. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission of Yahoo! Inc.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

