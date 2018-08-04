<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentBaseGateway');
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeApiTrait');
/**
 * Description of BraintreeRecurringBillingGateway
 *
 * @author kwlok
 */
class BraintreeRecurringBillingGateway extends PaymentBaseGateway 
{
    use BraintreeApiTrait;
    /**
     * Process recurring billing
     * @param PaymentForm model
     * @return array Subscription data 
     */
    public function process($form)
    {
        logTrace(__METHOD__.' Receiving data',$form->attributes);
        
        $braintree = $this->getBraintreeApi($form);
        //[1] If payment token exists, also implies customer already exists
        if (isset($form->extraPaymentData['paymentToken']) && $form->extraPaymentData['paymentToken']!=null){
            //@see BillingManager for how paymentToken is derived.
            $paymentMethodToken = $form->extraPaymentData['paymentToken'];
            logTrace(__METHOD__.' Use existing payment token',$form->extraPaymentData);
        }
        //[2] If payment nonce exists 
        elseif (isset($form->extraPaymentData['nonce']) && $form->extraPaymentData['nonce']!=null){
            //[2.1] New Customer! Braintree does not yet have the record
            if (($customer = $braintree->findCustomer($form->payer)) == false){
                logInfo(__METHOD__.' Customer not found, create new customer ...');
                $customer = $braintree->createCustomer([
                    'id' => $form->payer,
                    'email' => user()->getEmail(),
                    'paymentMethodNonce'=>$form->extraPaymentData['nonce'],
                    'creditCard' => [
                        'options' => [
                            'verifyCard' => true,
                            'makeDefault' => true,
                        ]
                    ],    
                ]);   
                if ($customer['success']){
                    $paymentMethodToken = $this->parsePaymentMethodToken($customer['response']->customer);
                    //[2.1.1] Create billing record if not exists
                    if (!Billing::model()->exists('account_id='.$form->payer))
                        $this->createBillingRecord($form->payer, $customer['response']->customer->id, user()->getEmail(), $paymentMethodToken,$customer['response']->customer->createdAt->format('j'));
                }
                else {
                    logError(__METHOD__.' Create customer error',$customer);
                    throw new CException(Sii::t('sii','Could not create customer').': '.$customer['response']->message.' ['.$customer['response']->code.']');
                }
            }
            //[2.2] Customer found in Braintree! 
            else {
                // customer "somehow" eixsts in Braintree db - this normally should not happen unless got data corruption
                $paymentMethodToken = $this->parsePaymentMethodToken($customer);
            }
        }
        else {
            logError(__METHOD__.' Payment token not found',$form->extraPaymentData);
            throw new CException(Sii::t('sii','Payment token not found'));
        } 
        
        //[3]Create subscription after customer is created 
        $subscription = $braintree->createSubscription($paymentMethodToken,$form->extraPaymentData['planId']);
        if ($subscription['success']){
            $form->reference_no = $subscription['response']->subscription->id;
            $form->trace_no = json_encode($this->createTraceNo($subscription['response']->subscription->transactions[0]));
            Yii::app()->serviceManager->getPaymentManager()->pay($form);
            logInfo(__METHOD__.' subscription created',$subscription);//all subscription data are available inside
            return [
                'subscription_no'=>$subscription['response']->subscription->id,
                'payment_token'=>$subscription['response']->subscription->paymentMethodToken,
                'start_date'=>$subscription['response']->subscription->billingPeriodStartDate->format('Y-m-d'),
                'end_date'=>$subscription['response']->subscription->billingPeriodEndDate->format('Y-m-d'),
            ];
        }
        else {
            logError(__METHOD__.' subscription error',$subscription);
            throw new CException(Sii::t('sii','Could not create subscription').': '.$subscription['response']->message);
        }            
    }     
    /**
     * Cancel recurring billing
     * @param string $subscriptionNo
     */
    public function cancel($subscriptionNo)
    {
        $subscription = $this->getBraintreeApi()->cancelSubscription($subscriptionNo);
        logInfo(__METHOD__.' braintree cancel api invoked',$subscription);
        if ($subscription['success']){
            logInfo(__METHOD__.' subscription cancelled',$subscription);
            return $subscription['response']->subscription->id;
        }
        else {
            logError(__METHOD__.' subscription error',$subscription);
            throw new CException(Sii::t('sii','Could not cancel subscription').': '.$subscription['response']->message);
        }            
    }  
    
    protected function parsePaymentMethodToken($customer)
    {
        return $customer->paymentMethods[0]->token;
    }
    
    protected function createBillingRecord($user,$customerId,$email,$paymentMethodToken,$billingDayOfMonth)
    {
        $billing = new Billing();
        $billing->customer_id = $customerId;
        $billing->email = $email;
        $billing->payment_method_token = $paymentMethodToken;
        $billing->billing_day_of_month = $billingDayOfMonth;//get today's day
        $billing = Yii::app()->getModule('billings')->serviceManager->create($user,$billing);
        logTrace(__METHOD__.' ok',$billing->attributes);
    }
}
