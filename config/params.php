<?php 
return [
    /**
     * configuration for general information
     */
    'ORG_NAME' => readConfig('app','organization'),
    'APP_VERSION' => readConfig('system','versionLabel'),
    /**
     * configuration for backend integration
     */
    'OAUTH_CLIENT_PORTAL' => 'ShopbayOauthApp',
    /**
     * configuration for CSRF token name
     * @todo Not supported for configuration. This token name is hardcoded at some othe code files, including javascript files. 
     */
    'CSRF_TOKEN_NAME' => 'APP_CSRF_TOKEN',
    /**
     * configuration for default locale
     */
    'LOCALE_DEFAULT' => 'en_sg',
    'DATETIME_FORMAT' => 'd/M/yy h:mm a',
    'DATE_FORMAT' => 'd/M/yy',  
    /**
     * configuration for vendor stuff
     */
    'BOWER_ASSET_DIR' => 'bower-asset', //starting Yii 2.0.13, it becomes 'bower-asset'. Before that is 'bower'
    
];

