<?php
// ----------------------------------------------------------------------------------------
// A template HybridAuth config file shared by webapp shopbay-shop and shopbay-merchant
// @see HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
//http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
// (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
// ----------------------------------------------------------------------------------------
return array(
    "base_url" => "https://{webapp_domain}/account/authenticate/oauth", 

    "providers" => array ( 
        // openid providers
        "OpenID" => array (
            "enabled" => false
        ),

        "AOL"  => array ( 
            "enabled" => false 
        ),

        "Yahoo" => array ( 
            "enabled" => false,
            "keys"    => array ( "id" => "", "secret" => "" )
        ),

        "Google" => array ( 
            "enabled" => false,
            "keys"    => array ( "id" => "", "secret" => "" )
        ),

        "Facebook" => array ( 
            "enabled" => true,
            "keys"    => array ( "id" => readConfig('facebook','appId'), "secret" => readConfig('facebook','appSecret') ),
            "scope"   => "email, public_profile, user_friends", // you can change the data, that will be asked from user
            "trustForwarded" => true,
            "allowSignedRequest"=>false,
            "display" => "popup" ,
        ),

        "Twitter" => array ( 
            "enabled" => false,
            "keys"    => array ( "key" => "", "secret" => "" ) 
        ),

        // windows live
        "Live" => array ( 
            "enabled" => false,
            "keys"    => array ( "id" => "", "secret" => "" ) 
        ),

        "LinkedIn" => array ( 
            "enabled" => false,
            "keys"    => array ( "key" => "", "secret" => "" ) 
        ),

        "Foursquare" => array (
            "enabled" => false,
            "keys"    => array ( "id" => "", "secret" => "" ) 
        ),
    ),

    // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
    "debug_mode" => YII_DEBUG,

    "debug_file" => '',
);