<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.SWidget');
Yii::import('common.modules.accounts.oauth.OAuth');
Yii::import('common.modules.accounts.oauth.OAuthLogoutAction');
/**
 * OAuthNetworks shows the list with networks that user is connected to, 
 * or connect new network to local account
 */
class OAuthNetworks extends SWidget
{
    public static $scriptFile = 'oauthnetworks.min.js';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.modules.accounts.oauth.widgets.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'zocial';    
    /**
     * The controller action/route to call when oauth network 
     * Default to '/account/authenicate/oauth' and AuthenticateController need to support this action
     * @see OAuthAction
     */
    public static $oauthRoute = '/account/authenticate/oauth';
    /**
     * The controller action/route to call when network link is done
     * Default to '/account/authenicate/oauthLink' and AuthenticateController need to support this action
     * @see OAuthLinkAction
     */
    public static $linkRoute = '/account/authenticate/oauthLink';
    /**
     * The controller action/route to call when unlink network request is submitted
     * Default to '/account/authenicate/oauthUnlink' and AuthenticateController need to support this action
     * @see OAuthUnlinkAction
     */
    public static $unlinkRoute = '/account/authenticate/oauthUnlink';
    /**
     * The controller route to logout oauth network
     * Default to '/account/authenicate/oauthLogout' and AuthenticateController need to support this action
     * @see OAuthLogoutAction
     */
    public static $logoutRoute = '/account/authenticate/oauthLogout';
    /*
     * If to use SGridView widget to render network list; Default to "true"
     */
    public $useGridView = true;
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        parent::init();
        //register addtional required js file
        $this->registerOAuthNetworkScripts();
    }    
    /**
     * Run widget
     * @throws CException
     */
    public function run() 
    {
        $this->render('networks');
    }
    /**
     * Prepare data for widget rendering
     * @return array
     * @throws CException
     */
    public function prepareData()
    {
        $data = array();
        foreach (self::networks() as $network) {

            $oauth = OAuth::model()->findAccount(Yii::app()->user->id,$network);
            $activated=Yii::app()->user->account->isActive();
            $profileUrl = '#';$logoutUrl = '#';$connected = false;$linked=false;$userInfo = '';//default values
            if ($oauth!=null){
                $linked = true;//record found means network linked
                $activated = $oauth->account->isActive();
                if (isset($oauth->profileCache->profileURL))
                    $profileUrl = $oauth->profileCache->profileURL;  
                if ($oauth->profile_cache!=null){
                    $profileCache = unserialize($oauth->profile_cache);
                    if (isset($profileCache['firstName']))
                        $userInfo .= $profileCache['firstName'].' ';
                    if (isset($profileCache['lastName']))
                        $userInfo .= $profileCache['lastName'].' ';
                    if (isset($profileCache['emailVerified']))
                        $userInfo .= '('.$profileCache['emailVerified'].')';
                }
                if ($oauth->isConnected){
                    $connected = true;
                    if (isset(self::$logoutRoute))
                        $logoutUrl = OAuthLogoutAction::formatLogoutUrl(self::$logoutRoute, $oauth);
                    else
                        $logoutUrl = $oauth->logoutUrl;
                }
            }
            
            try {
                array_push($data, array(
                    'provider' => $network, 
                    'connected'=>$connected, 
                    'linked'=>$linked, 
                    'activated' => $activated, 
                    'profileUrl' => $profileUrl, 
                    'logoutUrl' => $logoutUrl,
                    'userInfo' => $userInfo,
                ));

            } catch (Exception $e) {
                logError(__METHOD__, $e->getTraceAsString());
                throw new CException(__CLASS__ . ' error');
            }
        }
        return $data;
    }
    /**
     * @return \CArrayDataProvider
     */
    public function getArrayDataProvider()
    {
        $rawData = [];
        foreach ($this->prepareData() as $network) {
            list($provider, $connected, $linked, $activated, $profileUrl, $logoutUrl, $userInfo) = array_values($network);
            $rawData[] = [
                'connected'=>self::connectedIcon($connected),
                'provider'=>$provider,
                'providerLink'=>CHtml::link($provider, $profileUrl, ['class'=>'zocial '.strtolower($provider),'target' => '_blank']),
                'oauthUrl'=>url(self::$oauthRoute,['provider'=>$provider,'returnUrl'=>urlencode(url(self::$linkRoute,['network'=>$provider,'uid'=>Yii::app()->user->id]))]),
                'linkable'=>!$linked && $activated,
                'unlinkable'=>$linked && $activated,
                'logoutUrl'=>$logoutUrl,
                'logoutable'=>$connected,//may add also only activated user can do this
                'userInfo'=>$userInfo,
            ];
        }
        return new CArrayDataProvider($rawData,array('keyField'=>'provider'));
    }

    protected function getCsrfToken()
    {
        return json_encode(array(request()->csrfTokenName=>request()->getCsrfToken()));
    }
    /**
     * @return array of supported oauth networks
     */
    public static function networks()
    {
        $networks = [];
        $config = OAuth::getConfig();
        foreach ($config['providers'] as $network => $params) {
            if ($params['enabled'])
                $networks[] = $network;
        }
        return $networks;
    }
    
    public static function connectedIcon($connected)
    {
        if ($connected==true)
            return '<i class="fa fa-lock" title="'.Sii::t('sii','Connected to Network').'"></i>';
        else
            return '<i class="fa fa-unlock-alt" title="'.Sii::t('sii','Not Connected to Network').'"></i>';
    }
    
    public static function linkIcon()
    {
        return '<i class="fa fa-link" title="'.Sii::t('sii','Link Network').'"></i>';
    }
    
    public static function linkScript()
    {
        return 'function(){if (!confirm("'.self::getLinkMessage().'")) return false;}';
    }
    
    public static function getLinkMessage()
    {
        return Sii::t('sii', 'Linking this social network to {app} enables you to be able to login {app} using its account.\\n\\nDo you want to link this network?',array('{app}'=>Yii::app()->name));
    }
    
    public static function unlinkIcon()
    {
        return '<i class="fa fa-unlink" title="'.Sii::t('sii','Unlink Network').'"></i>';
    }
    
    public static function unlinkScript()
    {
        return 'function(){if (!confirm("'.self::getUnlinkMessage().'")) return false; else unlinknetwork($(this));}';
    }    
    
    public static function getUnlinkMessage()
    {
        return Sii::t('sii', 'If you unlink this social network from {app}, you will not be able to login {app} using its account.\\n\\nDo you realy want to unlink this network?',array('{app}'=>Yii::app()->name));
    }
    
    public static function signoutIcon()
    {
        return '<i class="fa fa-sign-out" title="'.Sii::t('sii','Sign out Network').'"></i>';
    }

    public static function signoutScript()
    {
        return 'function(){if (!confirm("'.self::getSignoutMessage().'")) return false;}';
    }
    
    public static function getSignoutMessage()
    {
        return Sii::t('sii', 'You will be signing out this social network, but not {app}. You can continue to use {app} until you logout {app}.\\n\\nDo you want to proceed?',array('{app}'=>Yii::app()->name));
    }
    
    public function registerOAuthNetworkScripts()
    {
        Yii::app()->assetManager->minifyJs(Yii::getPathOfAlias($this->pathAlias.'.js'),'oauthnetworks.js');
        $this->registerScriptFile($this->pathAlias.'.js',self::$scriptFile);
    }

    public function getOAuthNetworkScriptsUrl()
    {
        $assetsURL=$this->getOwner()->getAssetsURL($this->pathAlias.'.js');
        return $assetsURL.'/'.self::$scriptFile;
    }
}
