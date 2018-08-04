<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SHttpRequest
 *
 * @author kwlok
 */
class SHttpRequest extends CHttpRequest 
{
    /**
     * @var boolean True to support cross-subdomain authentication
     */
    public $enableCsrfCookieSharing = false;
    /*
     * Route to skip csrf validation
     */
    public $csrfSkippedRoutes = [];
    /**
     * Init
     */
    public function init()
    {
        if ($this->enableCsrfCookieSharing){
            $this->csrfCookie = cookieSharingSettings();
        }
        parent::init();
    }
    
    protected function normalizeRequest()
    {
        //attach event handlers for CSRF in the parent
        parent::normalizeRequest();
            
        //remove the event handler CSRF if this is a route we want skipped
        if($this->enableCsrfValidation)
        {
            //$url=Yii::app()->urlManager->parseUrl($this);
            $url=$this->getRequestUri();
            foreach($this->csrfSkippedRoutes as $route)
            {
                if (strpos($url,$route)!==false){
                    Yii::app()->detachEventHandler('onBeginRequest',array($this,'validateCsrfToken'));
                    Yii::log(__METHOD__.' skip CSRF validation >> '.$url, CLogger::LEVEL_INFO);//cannot use logInfo() will cause error
                    break;
                }
            }
        }
    }
    
    public function validateCsrfToken($event)
    {        
        if (isset($_GET[$this->csrfTokenName])){
            $cookies=$this->getCookies();
            $userToken=$_GET[$this->csrfTokenName];
            if ($cookies->contains($this->csrfTokenName)){
                $cookieToken=$cookies->itemAt($this->csrfTokenName)->value;
                $valid=$cookieToken===$userToken;
            }
            else
                $valid = false;
            if (!$valid)
                throw new CHttpException(400,Sii::t('sii','The CSRF token could not be verified.'));
        }
        else {
            parent::validateCsrfToken($event);
        }
    }
    
    public function getScheme($protocol=null,$suffix=true)
    {
        if (!isset($protocol)){
            if ($this->isSecureConnection)
                $protocol = 'https';
            else
                $protocol = 'http';
        }
        if ($suffix)
            return $protocol.'://';
        else 
            return $protocol;
    }
    
    public function getSecureHostInfo() 
    {
        return Yii::app()->request->getHostInfo('https');
    }    
    /**
     * Retrieves the best guess of the client's actual IP address.
     * Takes into account numerous HTTP proxy headers due to variations
     * in how different ISPs handle IP addresses in headers between hops.
     */
    public function getUserHostIPAddress()
    {
        $ipAddress = null;
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        $ipAddress = $ip;
                    }
                }
            }
        }
        
        return isset($ipAddress)?$ipAddress:request()->getUserHostAddress();
    }
    /**
     * Check if request is coming from mobile device
     * @return int
     */
    public function isMobile() 
    {
        $mobile = false;
        if(preg_match('/(android|mmp|symbian|smartphone|midp|wap|phone|iphone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile = true;
        }
        return $mobile;
    }    
}