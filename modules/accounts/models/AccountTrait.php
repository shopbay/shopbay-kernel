<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AccountTrait
 * Model owning this trait must have attribute 
 * 'activate_str','email','status',activate_time'
 * 
 * @author kwlok
 */
trait AccountTrait 
{
    public function getIsSystem()
    {
        return $this->id==Account::SYSTEM;
    }

    public function getIsSuperuser()
    {
        return $this->id==Account::SUPERUSER;
    }

    public function getIsAdmin()
    {
        return $this->hasRole(Role::ADMINISTRATOR);
    }

    public function getIsMerchant()
    {
        return $this->hasRole(Role::MERCHANT);
    }
    
    public function passwordChangeRequired()
    {
        return $this->status == Process::ACCOUNT_NEW_PASSWORD;
    }
        
    public function isActive()
    {
        return $this->status == Process::ACTIVE || $this->status == Process::PASSWORD_RESET || Process::ACCOUNT_NEW_PASSWORD;
    }

    public function pendingActivation()
    {
        return in_array($this->status, [Process::SIGNUP,Process::EMAIL_RESET,Process::ACCOUNT_NEW]);
    }
    
    public function pendingSignup()
    {
        return $this->status == Process::PRESIGNUP;
    }
    /**
     * Url to activate account
     * @param $activate_str Activation string
     * @param $network an indicator to which network is used to presignup, and next step to launch presignup process when url is clicked
     * TODO: In fact is it a timestamp that can be used to set expiry date for presignup period
     * @return string url
     */
    public function getActivationUrl($activate_str,$network=null,$route='account/activate')
    {
        if (isset($network))//indicate that is a presignup account via social netowrk sign in
            return url($route.'/presignup?token='.base64_encode($activate_str).'&network='.$network);    
        else
            return url($route.'?token='.base64_encode($activate_str));    
    }
    /**
     * Check if activation period is already expired and not account will be suspended
     * @see self::setActivationToken
     * @return type
     */
    public function getIsActivationExpired()
    {
        $token = explode('.',$this->activate_str);
        if (!isset($token[1]))
            return false;
        
        $today = new DateTime(date('Y-m-d', time()));
        $tokenTime = new DateTime(date('Y-m-d', $token[1]));
        $interval = $tokenTime->diff($today);
        logTrace(__METHOD__. " token created " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days ago"); 
        return $interval->days > Config::getSystemSetting('activation_period');
    }
    
    public function setActivationToken($seed)
    {
        $this->activate_str = md5(mt_rand(10000,99999).$seed).'.'.time();
    }
    /**
     * @throws CDbException
     */
    public function prepareEmailActivation($userid)
    {
        $this->status = Process::EMAIL_RESET;
        $this->activate_str = sha1(mt_rand(10000,99999).time().$this->email);
        $this->activate_time = null;
        $this->update();
        $auth = AuthAssignment::model()->find('userid=\''.$userid.'\' AND itemname=\''.Role::ACTIVATED.'\'');
        if ($auth!=null){
            $auth->delete();
            logInfo(__METHOD__.' Role ACTIVATED removed');
        }
    }    
}
