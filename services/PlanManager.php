<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.exceptions.*");
/**
 * Description of PlanManager
 * 
 * @author kwlok
 */
class PlanManager extends ServiceManager 
{
    private $_billingManager;
    /**
     * Create plan model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::PLAN_DRAFT;
        return $this->execute($model, [
            'insert'=>self::EMPTY_PARAMS,
            'insertChilds'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ],$model->getScenario());
    }
    /**
     * Create package model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createPackage($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::PACKAGE_DRAFT;
        return $this->execute($model, [
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ],$model->getScenario());
    }      
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        if (!$model->updatable($user)){
            $model->addError('status', Sii::t('sii','Update not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        $this->validate($user, $model, $checkAccess);
        $result = $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'updateChilds'=>self::EMPTY_PARAMS,
            'updatePermissions'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ],$model->getScenario());
        
        Yii::app()->commonCache->delete(SCache::PUBLISHED_PACKAGES);
        logInfo(__METHOD__.' delete cache '.SCache::PUBLISHED_PACKAGES.' ok');

        return $result;
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updatePackage($user,$model,$checkAccess=true)
    {
        if (!$model->updatable($user)){
            $model->addError('status', Sii::t('sii','Update not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        $this->validate($user, $model, $checkAccess);
        $result = $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ],$model->getScenario());
        
        Yii::app()->commonCache->delete(SCache::PUBLISHED_PACKAGES);
        logInfo(__METHOD__.' delete cache '.SCache::PUBLISHED_PACKAGES.' ok');

        return $result;
    }    
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        if (!$model->deletable($user)){
            $model->addError('status', Sii::t('sii','Delete not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, [
                'recordActivity'=>[
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ],
                'delete'=>self::EMPTY_PARAMS,
            ],'delete');
    } 
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function deletePackage($user,$model,$checkAccess=true)
    {
        if (!$model->deletable($user)){
            $model->addError('status', Sii::t('sii','Delete not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, [
                'recordActivity'=>[
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ],
                'delete'=>self::EMPTY_PARAMS,
            ],'delete');
    }     
    /**
     * Submit Plan 
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function submit($user,$model)
    {
        if (!$model->submitable($user)){
            $model->addError('status', Sii::t('sii','Submission not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        
        return $this->transition($user, $model, 'submit');
    } 
    /**
     * Submit Package 
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function submitPackage($user,$model)
    {
        if (!$model->submitable($user)){
            $model->addError('status', Sii::t('sii','Submission not allowed.'));
            $this->throwValidationErrors($model->errors);
        }        
        return $this->transition($user, $model, 'submit');
    }     
    /**
     * Approve Plan
     * 
     * @param integer $user Session user id to approve the plan
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function approve($user,$model,$transition)
    {
        if (!$model->validate()) {
            logError(__METHOD__.' error',$model->getErrors());
            $this->throwValidationErrors($model->getErrors());
        }
        
        $model->setOfficer($user);
        $model->setAccountOwner('officerAccount');
        //change ownerAttribute to cater for Administrable use
        $this->ownerAttribute = 'id';
        //first run workflow
        $returnModel = $this->runWorkflow(
                        $user,
                        $model, 
                        $transition, 
                        Transition::SCENARIO_C1_D, 
                        Activity::EVENT_APPROVE, 
                        'approvable',
                        false);//non html error output
        
        //need to set here as in "approve" it is called
        //reset back (this is required as in Api mode, serviceManager are static member and shared across all api requrest)
        $this->ownerAttribute = 'account_id';
        return $returnModel;
    }     
    /**
     * Approve Package
     * 
     * @param integer $user Session user id to approve the plan
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function approvePackage($user,$model,$transition)
    {
        if (!$model->validate()) {
            logError(__METHOD__.' error',$model->getErrors());
            $this->throwValidationErrors($model->getErrors());
        }
        
        $model->setOfficer($user);
        $model->setAccountOwner('officerAccount');
        //change ownerAttribute to cater for Administrable use
        $this->ownerAttribute = 'id';
        //first run workflow
        $returnModel = $this->runWorkflow(
                        $user,
                        $model, 
                        $transition, 
                        Transition::SCENARIO_C1_D, 
                        Activity::EVENT_APPROVE, 
                        'approvable',
                        false);//non html error output
        
        //need to set here as in "approve" it is called
        //reset back (this is required as in Api mode, serviceManager are static member and shared across all api requrest)
        $this->ownerAttribute = 'account_id';
        return $returnModel;
    }     
    /**
     * Subscribe Plan 
     * 
     * Note: Rbac assignment is handled via webhook asynchronously
     * @see SubscriptionWorkflow::activateYes()
     * @see {@link SubscriptionWentActiveHandler}
     * 
     * @param integer $user Session user id
     * @param \app\modules\v1\models\Plan $plan CModel model to subscribe
     * @return CModel $model
     * @throws CException
     */
    public function subscribe($user,$plan)
    {
        //If shop is present means changing current plan, null means new subscription
        if (isset($plan->shop)){
            $currentSubscription = Subscription::model()->mine($user)->locateShop($plan->shop)->active()->notExpired()->find();
            if ($currentSubscription==null)//try to check if is to renewal
                $currentSubscription = Subscription::findLatestExpiredSubscription($plan->shop);
            if ($currentSubscription==null)//error when expired subscripton is not even found!
                $plan->addError('status', Sii::t('sii','Shop existing subscription not found.'));
        }
        
        if (!$plan->subscribable())
            $plan->addError('status', Sii::t('sii','Subscription not allowed.'));

        if ($plan->id==Plan::FREE_TRIAL && Subscription::model()->hasTrialBefore($user))
            $plan->addError('status', Sii::t('sii','You have already subscribed to free trial before.'));
        
        if ($plan->hasErrors())
            $this->throwValidationErrors($plan->errors);
                
        if (!$plan->package->subscribable()){
            $plan->package->addError('status', Sii::t('sii','Subscription not allowed.'));
            $this->throwValidationErrors($plan->package->errors);
        }        
                
        //[1]Prepare new subscription form data
        $form = new SubscriptionForm();
        $form->shop_id = $plan->shop;//got value means changing existing plan, null means new subscription
        $form->createPlanData($plan->package,$plan);
        if ($plan->chargeable()){
            $form->setScenario(SubscriptionForm::SCENARIO_PAYMENT);
            $form->setPaymentNonce($plan->paymentNonce);
            $form->payment_token = $plan->paymentToken;//got value means existing customer selects the payment method to pay subscription
        }
        else
            $form->setScenario(SubscriptionForm::SCENARIO_FREE);
                           
        $subscriptionNo = $user.'_'.$plan->id;//initial subscription no, to be updated later when Braintree assign no.
        //[2]Save subscription (as SUBSCRIPTION_PENDING status)
        $subscription = Subscription::create($user, $subscriptionNo, $plan);
        
        //[3]Pay subscription (payment and webhook notification will be handled inside this call)
        $returnSubscriptionData = $this->billingManager->paySubscription($user,$form);
        
        //[4] Update subscriptoin data according to payment gateway data
        $this->execute($subscription, [
            'updateSubscriptionData'=>$returnSubscriptionData,
            'recordActivity'=>[
                'event'=>Activity::EVENT_SUBSCRIBE,
                'description'=>$plan->name,
            ],
        ],$plan->getScenario());
        
        //[5]For FREE plan, have to manual trigger webhook to move from PENDING -> ACTIVE, as no webhook notification available)
        if ($subscription->plan->isFree)     
            $this->subscriptionManager->activate($subscription);
            
        //[6] Changing plan: can existing plan after new subscription is added
        if (isset($currentSubscription)){
            if (!$currentSubscription->hasExpired || $currentSubscription->isActive)//for non expired subscription, cancel it (unsubsribe)
                $this->unsubscribe($user, $currentSubscription, false);//IMPORTANT: cannot delete shop for changing plan
            $shopId = $plan->shop;
        }
        //[7] New plan subscription! Create the shop tied to this subscription
        else {
            $newShop = $this->getShopManager()->create($user);
            $shopId = $newShop->id;
        }
        
        //[8] Binding shop to subscription
        if ($subscription->bindTo($shopId)){
            //[9] Clone subscription plan to shop level 
            $subscription->clonePlan();
            //[10] Everything is ok by far, return subscription object
            return $subscription;
        }
        else {
            return false;
        }
    }     
    /**
     * Unsubscribe Plan 
     * 
     * Note: Rbac revoke is handled via webhook asynchronously
     * @see SubscriptionWorkflow::deactivateYes()
     * @see {@link SubscriptionCanceledHandler}
     * 
     * @param integer $user Session user id
     * @param Subscription $model 
     * @return CModel $model
     * @throws CException
     */
    public function unsubscribe($user,$model, $deleteShop=true)
    {
        if (!$model->cancellable()){
            $model->addError('status', Sii::t('sii','Subscription cancellation not allowed.'));
            $this->throwValidationErrors($model->errors);
        }

        //[1]Move subscription plan to 'PENDING_CANCEL'
        //No activity recording
        //No notfication sending as subscription PENDING_CANCEL is defined by deleted record by "soft delete), findByPk() won't work
        $model = $this->transition($user, $model, 'cancel',null,false,false);
        
        //[2]Cancel subscription payment (payment and webhook notification will be handled inside this call)
        $this->billingManager->cancelSubscription($model);

        //[3]everything is ok by far, return subscription object
        $ops = [
            'recordActivity'=>[
                'event'=>Activity::EVENT_CANCEL,
                'account'=>$user,
                'icon_url'=>$model->getActivityIconUrl(),
                'description'=>$model->getActivityDescription(),
            ],
        ];
        if ($deleteShop){
            $ops['deleteShop'] = self::EMPTY_PARAMS;
            //todo should we also unbind shop? cannot, else subscription history will failed
        }
        
        return $this->execute($model, $ops, $model->getScenario());
    }  
    /**
     * Return billing manager
     * @return BillingManager
     */
    protected function getBillingManager()
    {
        if (!isset($this->_billingManager)){
            $this->_billingManager = Yii::app()->getModule('billings')->serviceManager;
            $this->_billingManager->runMode = 'api';
        }
        return $this->_billingManager;
    }
       
}
