<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.plans.models.Subscription');
/**
 * Description of ShopSubscriptionTrait
 * 
 * @author kwlok
 */
trait ShopSubscriptionTrait 
{
    private $_s;//online subscriptions
    private $_o;//online subscription
    private $_p;//pending subscriptions
    private $_pd;//pastdue subscriptions
    private $_sp;//suspended subscriptions
    private $_c;//cancelled subscriptions
    /**
     * Latest active or expired subscription
     * @return Subscription 
     */
    public function getCurrentSubscription($shop)
    {
        $subscription = $this->getOnlineSubscription($shop);
        if ($subscription==null && isset($shop))//try if is expired subscription for renewal
            $subscription = $this->getExpiredSubscription($shop);        
        return $subscription;
    }
    /**
     * @return boolean If shop has active, non-expired subscription
     */
    public function hasOnlineSubscription($shop)
    {
        if (!isset($shop))
            return false;
        else
            return $this->getOnlineSubscription($shop)!=null;
    }        
    /**
     * @return Subscription 
     */
    public function getOnlineSubscription($shop)
    {
        if (!isset($this->_o) && isset($shop)){
            if (Shop::model()->mine()->findByPk($shop)==null)
                throwError404(Sii::t('sii','Shop not found'));
            else
                $this->_o = Subscription::model()->mine()->locateShop($shop)->active()->notExpired()->find();
        }
        return $this->_o;
    }
    /**
     * @return array Get all user online subscriptions (including free trials)
     */
    public function getOnlineSubscriptions()
    {
//        Yii::beginProfile(__METHOD__);
        if (!isset($this->_s)){
            $this->_s = Subscription::model()->mine()->active()->notExpired()->findAll();
        }
//        Yii::endProfile(__METHOD__);
        return $this->_s;
    }     
    /**
     * @return boolean If shop has pending subscription
     */
    public function hasPendingSubscription($shop)
    {
        return $this->getPendingSubscription($shop)!=null;
    }        
    /**
     * @return Subscription 
     */
    public function getPendingSubscription($shop)
    {
        if (!isset($this->_p) && isset($shop))
            $this->_p = Subscription::model()->mine()->locateShop($shop)->pending()->find();
        return $this->_p;
    }
    /**
     * @return boolean
     */
    public function hasPastdueSubscription($shop)
    {
        return $this->getPastdueSubscription($shop)!=null;
    }
    /**
     * @return Subscription
     */
    public function getPastdueSubscription($shop)
    {
        if (!isset($this->_pd) && isset($shop))
            $this->_pd = Subscription::model()->mine()->locateShop($shop)->pastdue()->find();
        return $this->_pd;
    }
    /**
     * @return Subscriptions
     */
    public function getPastdueSubscriptions()
    {
        return Subscription::model()->mine()->pastdue()->findAll();
    }
    /**
     * @return boolean
     */
    public function hasSuspendedSubscription($shop)
    {
        return $this->getSuspendedSubscription($shop)!=null;
    }
    /**
     * @return Subscription
     */
    public function getSuspendedSubscription($shop)
    {
        if (!isset($this->_sp) && isset($shop))
            $this->_sp = Subscription::model()->mine()->locateShop($shop)->suspended()->find();
        return $this->_sp;
    }
    /**
     * @return boolean
     */
    public function hasCancelledSubscription($shop)
    {
        return $this->getCancelledSubscription($shop)!=null;
    }
    /**
     * @return Subscription
     */
    public function getCancelledSubscription($shop)
    {
        if (!isset($this->_c) && isset($shop))
            $this->_c = Subscription::model()->mine()->locateShop($shop)->cancelled()->find();
        return $this->_c;
    }    
    /**
     * @return boolean
     */
    public function hasExpiredSubscription($shop)
    {
        if ($this->hasOnlineSubscription($shop))
            return false;
        else
            return $this->getExpiredSubscription($shop)!=null;
    }
    /**
     * Get the latest expired subscription
     * @return Subscription
     */
    public function getExpiredSubscription($shop)
    {
        return Subscription::findLatestExpiredSubscription($shop);
    }
    /**
     * @return boolean
     */
    public function getHasExpiredFreeTrial()
    {
//        Yii::beginProfile(__METHOD__);
        $trial = Subscription::model()->mine()->freeTrial()->find();
//        Yii::endProfile(__METHOD__);
        return $trial!=null ? $trial->hasExpired : false;
    }
    /**
     * @return boolean If any active subscription exists
     */
    public function getHasNonFreeTrialSubscriptions()
    {
        Yii::beginProfile(__METHOD__);
        $found = false;
        foreach ($this->getOnlineSubscriptions() as $sub) {
            if (!$sub->plan->isFreeTrial){
                $found = true;
                break;//any one of them is sufficient
            }
        }
        Yii::endProfile(__METHOD__);
        return $found;
    }        
    /**
     * @return boolean If user has not created any shop before
     */
    public function getHasNoShopBefore()
    {
        return Shop::model()->mine()->count()==0;
    }        
    /**
     * @return boolean
     */
    public function getHasTrialBefore() 
    {
        return Subscription::model()->mine()->freeTrial()->exists();
    }
    /**
     * @return boolean
     */
    public function getHasFreePlanBefore() 
    {
        return Subscription::model()->mine()->freePlan()->exists();
    }
    
}
