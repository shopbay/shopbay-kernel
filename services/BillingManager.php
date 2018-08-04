<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.exceptions.*");
Yii::import('common.modules.payments.models.PaymentForm');
Yii::import('common.modules.plans.models.SubscriptionRetryChargeForm');
/**
 * Description of BillingManager
 *
 * @author kwlok
 */
class BillingManager extends ServiceManager 
{
    public $paymentGateway;
    /**
     * Create billing record
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to create
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ),$model->getScenario());
    }   
    /**
     * Update billing record
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ),$model->getScenario());
    }     
    /**
     * Make one time payment (using BraintreeCreditCardTokenGateway)
     * @param type $user
     * @param PaymentForm $form
     * @return ShopTheme 
     * @throws CException
     */
    public function pay($user, PaymentForm $form)
    {
        if (!$form->validate())
            $this->throwValidationErrors($form->getErrors());
        
        if ($form->amount > 0 ){//only process amount bigger than zero 
            Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeCreditCardTokenGateway');
            $gateway = new BraintreeCreditCardTokenGateway();
            return $gateway->process($form,true);//return back trace no, any exception will be thrown here        
        }
        else {
            return null;
        }        
    }     
    /**
     * Pay a subscription
     * @param type $user
     * @param type $form
     * @return string subscription id
     * @throws CException
     */
    public function paySubscription($user,$form)
    {
        if (!$form instanceof SubscriptionForm)
            throw new CException(Sii::t('sii','Invalid subscription form'));

        if (!$form->validate())
            $this->throwValidationErrors($form->getErrors());
        
        if ($form->getScenario()==SubscriptionForm::SCENARIO_PAYMENT){
            //[1] Validate subscription type
            if ($form->planData['type']!=Plan::RECURRING)
                throw new CException(Sii::t('sii','Subscription type must be recurring'));
            //[2] Prepare PaymentForm
            $paymentForm = $this->_preparePaymentForm($user, $form, $form->paymentNonce, $form->braintreeData);
            //[3] Make payment 
            return $this->_getPaymentGateway()->process($paymentForm);//any exception will be thrown here        
        }
        else {
            //random assign 6 chars subscription id for FREE / TRIAL plan
            //why 6? for consistency to be inline with subscription id returned by payment gateway
            return [
                'subscription_no'=>substr(uniqid(),0,6),
            ];
        }        
    }
    /**
     * Subscription retry charge
     * @param type $form
     */
    public function repaySubscription($form)
    {
        if (!$form instanceof SubscriptionRetryChargeForm)
            throw new ServiceValidationException(Sii::t('sii','Invalid input form'));

        if (!$form->validate())
            $this->throwValidationErrors($form->errors,true);
            
        $response = $this->_getPaymentGateway()->getBraintreeApi()->retryCharge($form->subscription_no, $form->amount);
        if ($response['success']){
            logInfo(__METHOD__.' ok for amount '.$form->amount, $form->subscription_no);
            //Move subscription to PENDING prepared for re-activation (to be handled via webhook "went_active")          
            //$this->subscriptionManager->reactivate($subscription,WorkflowManager::DECISION_HOLD);
            return true;
        }
        else 
            throw new ServiceValidationException(Sii::t('sii','Subscription recharging fails'));
    }
    /**
     * Cancel a subscription (for payment)
     * @param Subscription $model
     * @throws CException
     */
    public function cancelSubscription($model)
    {
        //Cancel subscription payment for non-free subscription
        if (!$model->plan->isFree){
            //[1]Let PaymentGateway trigger webhook notification to move from PENDING_CANCEL -> CANCELED
            return $this->_getPaymentGateway()->cancel($model->subscription_no);//any exception will be thrown here        
        }
        else {
            //[2]Have to manual trigger webhook to move from PENDING_CANCEL -> CANCELED
            //for free plan - as no webhook notification available)
            $this->subscriptionManager->deactivate($model);
        }        
    }      
    /**
     * Update Subscription - mainly to change payment method
     * @param Subscription $subscription
     * @param string $paymentMethodToken
     */
    public function updateSubscription($subscription,$paymentMethodToken)
    {
        if (!$subscription instanceof Subscription)
            throw new ServiceValidationException(Sii::t('sii','Invalid subscription object'));

        $response = $this->_getPaymentGateway()->getBraintreeApi()->updateSubscription($subscription->subscription_no,$paymentMethodToken);
        if ($response['success']){
            $subscription->payment_token = $paymentMethodToken;
            if ($subscription->save()){
                logInfo(__METHOD__." subscription $subscription->subscription_no payment method updated ok",$paymentMethodToken);
                return true;
            }
        }
        
        logError(__METHOD__." subscription $subscription->subscription_no payment method error",$response);
        return $response['response']->message;//return error message
    }
    /**
     * This payment form is used to pay subscription by payment method 
     */
    private function _preparePaymentForm($user,$subscriptionForm,$nonce,$paymentGatewayData)
    {
        if (!$subscriptionForm instanceof SubscriptionForm)
            throw new CException(Sii::t('sii','Invalid subscription form'));
        
        $form = new PaymentForm();
        $form->payer = $user;
        $form->type = Payment::SUBSCRIPTION;
        $form->method = PaymentMethod::BRAINTREE_CREDITCARD;//credit card payment method
        $form->amount = $subscriptionForm->planData['price'];
        $form->currency = $subscriptionForm->planData['currency'];
        $form->extraPaymentData['planId'] = (int)$subscriptionForm->planData['id'];
        $form->extraPaymentData['nonce'] = $nonce;
        $form->extraPaymentData['paymentToken'] = $subscriptionForm->payment_token;
        $form->paymentGatewayData['braintree'] = $paymentGatewayData;
        $form->status = Process::UNPAID;
        return $form;
    }
    
    private function _getPaymentGateway()
    {
        if (!isset($this->paymentGateway))
            throw new CException(Sii::t('sii','Payment Gateway not defined'));
        
        Yii::import($this->paymentGateway);//value in path alias
        $paymentGatewayClass = end(explode('.', $this->paymentGateway));
        logTrace(__METHOD__.' loading class '.$paymentGatewayClass,$this->paymentGateway);
        return new $paymentGatewayClass();
    }     

}
