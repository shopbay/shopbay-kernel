<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SUrlManager
 *
 * @author kwlok
 */
class SUrlManager extends CUrlManager
{
    /**
     * @property boolean Indicate if all url will be using SSL.
     */
    public $forceSecure = false;
    /**
     * @property string the host domain of web app. e.g. www.shopbay.org
     */
    public $hostDomain;
    /**
     * @property string the cdn domain. e.g. cdn.shopbay.org
     */
    public $cdnDomain;
    /**
     * @property string the merchant portal domain. e.g. merchant.shopbay.org
     */
    public $merchantDomain;
    /**
     * @property string the shop storefront domain. e.g. www.shopbay.org
     */
    public $shopDomain;
    /**
     * @property string the bot app domain. e.g. bot.shopbay.org
     */
    public $botDomain;
    /**
     * @property string the domain name
     */
    public $domain;
    /**
     * @property string the home url
     */
    public $homeUrl;
    /**
     * @property array the default rules to be loaded
     */
    public $defaultRules = [
        'account'=>'accounts/management/index',
        'account/<controller>/<action:\w+>'=>'accounts/<controller>/<action>',
        'account/<controller>/<action:\w+>/*'=>'accounts/<controller>/<action>/*',
        'account/<controller>/*'=>'accounts/<controller>',
    ];
    /**
     * @var array list of routes that should work only in SSL mode.
     * Each array element can be either a URL route (e.g. 'site/create') 
     * or a controller ID (e.g. 'settings'). The latter means all actions
     * of that controller should be secured.
     */
    public $secureRoutes = [];
    private $_secureMap;
    /**
     * @var array list of routes that should be exluded to work from SSL mode.
     */
    public $excludeSecureRoutes = [];
    private $_excludeSecureMap;
    /**
     * Initializes the application component.
     */
    public function init()
    {
        if (!isset($this->hostDomain))
            throw new CException(Sii::t('sii','Host domain is not defined.'));
        /**
         * If cdn domain is not set, follow host domain to make app self-provisioning its assets.
         * @todo If were to support external cdn, might have to implement assets publishing to external cdn network (websites)
         */
        if (!isset($this->cdnDomain))
            $this->cdnDomain = $this->hostDomain;
        
        $this->domain = resolveDomain($this->hostDomain);
        
        //load common rules
        $this->rules = array_merge($this->defaultRules,$this->rules);
        //logTrace(__METHOD__.' rules',$this->rules);
        parent::init();
    }    
    /**
     * This method create url based on host domain
     * @param type $route
     * @param boolean $forceSecure if use secure connection
     * @return type
     */
    public function createHostUrl($route=null,$forceSecure=false)
    {
        return $this->createDomainUrl($this->hostDomain, $route, $forceSecure);
    }    
    /**
     * This method create url based on cdn domain
     * @param string $route
     * @param string $scheme
     * @return type
     */
    public function createCdnUrl($route=null,$scheme=null)
    {
        return request()->getScheme($scheme).$this->cdnDomain.$route;
    }
    /**
     * This method create url based on merchant domain
     * @param type $route
     * @param boolean $forceSecure if use secure connection
     * @return type
     */
    public function createMerchantUrl($route=null,$forceSecure=false)
    {
        return $this->createDomainUrl($this->merchantDomain, $route, $forceSecure);
    }  
    /**
     * This method create url based on shop domain
     * @param type $route
     * @param boolean $forceSecure if use secure connection
     * @return type
     */
    public function createShopUrl($route=null,$forceSecure=false)
    {
        return $this->createDomainUrl($this->shopDomain, $route, $forceSecure);
    }    
    /**
     * This method create the community url based on domain
     * @param type $route
     * @param boolean $forceSecure if use secure connection
     * @return type
     */
    public function createCommunityUrl($route=null,$forceSecure=false)
    {
        return $this->createHostUrl('/community/'.$route,$forceSecure);
    }  
    /**
     * This method create domain url based on domain and route 
     * @param type $domain
     * @param type $route
     * @param boolean $forceSecure if use secure connection
     * @return type
     */
    public function createDomainUrl($domain,$route=null,$forceSecure=false)
    {
        // Check if the route is supposed to use secure or non-secure connection
        if ($this->isSecureRoute($route) || $this->forceSecure || $forceSecure || request()->isSecureConnection)
            $scheme = request()->getScheme('https');
        else
            $scheme = request()->getScheme('http');
        
        if (isset($route[0]) && $route[0]!='/')
            $route = '/'.$route;//add / if not start with "/"
        return $scheme.$domain.$route;
    }  
    /**
     * OVERRIDE (create default url)
     * @param type $route
     * @param type $params
     * @param type $ampersand
     * @return type
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        $url = parent::createUrl($route, $params, $ampersand);
 
        if (strpos($url, 'http') === 0) {
            logTrace(__METHOD__.' already an absolute URL, return it directly');
            //follow current scheme
            return request()->isSecureConnection ? str_replace('http://', 'https://', $url) : $url;
        }
 
        // Check if the route is supposed to use non-SSL url
        if ($this->isSecureRoute($route) || $this->forceSecure || request()->isSecureConnection){
            //logTrace(__METHOD__." use ssl for route $route");
            return request()->getSecureHostInfo().$url;
        }
        else {
            return request()->getHostInfo('http').$url;
        }
    }
    public function parseUrl($request)
    {
        $route = parent::parseUrl($request);
 
        //force Redirect 301 if not in https for either forceSecure or is secure route
        if ($this->forceSecure && !$request->isSecureConnection ||
            $this->isSecureRoute($request->url) && !$request->isSecureConnection){
            $request->redirect(request()->getSecureHostInfo().$request->url, true, 301);
        }
        
        return $route;
    }    
    
    public function getHomeUrl() 
    {
        if (!isset($this->homeUrl)){
            if ($this->forceSecure || request()->isSecureConnection)
                $this->homeUrl = request()->getSecureHostInfo().'/';
            else
                $this->homeUrl = $this->createHostUrl();
        }
        return $this->homeUrl;
    }
    /**
     * @param string the URL route to be checked
     * @return boolean if the give route should be serviced in SSL mode
     */
    protected function isSecureRoute($route)
    {
        if ($this->_secureMap === null) {
            foreach ($this->secureRoutes as $r) {
                $this->_secureMap[strtolower($r)] = true;
            }
        }
        if ($this->_excludeSecureMap === null) {
            foreach ($this->excludeSecureRoutes as $r) {
                $this->_excludeSecureMap[strtolower($r)] = true;
            }
        }
        $route = strtolower($route);
        //[1]check exlude route first
        if (isset($this->_excludeSecureMap[$route]) || $this->_hasControllerInMap($route,'_excludeSecureMap')) {
            return false;
        } 
        //[2]next check secure route
        if (isset($this->_secureMap[$route])) {
            return true;
        } else {
            return $this->_hasControllerInMap($route,'_secureMap');
        }
    }    
    /**
     * Check if controller id is secure (from either secure or exlcude map
     * @param type $route
     * @param type $map
     * @return type
     */
    private function _hasControllerInMap($route,$map)
    {
        return ($pos = strpos($route, '/')) !== false 
                && isset($this->{$map}[substr($route, 0, $pos)]);
    }
    
}
