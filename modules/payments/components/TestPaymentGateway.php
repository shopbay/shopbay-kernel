<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentGateway');
/**
 * Description of TestPaymentGateway
 * Used in codeception test (@see shopbay-api/config/yii1engine.php)
 * 
 * Return dummy test data
 *
 * @author kwlok
 */
class TestPaymentGateway extends PaymentGateway 
{
    public function init() 
    {
        parent::init(); 
    }
    /**
     * Process payment
     * @param PaymentForm model
     * @param $returnTraceAsArray default to True
     */
    public function process($payment,$returnTraceAsArray=true)
    {
        $this->validate($payment);
        if ($returnTraceAsArray){//return test transaction data
            $paymentToken = isset($payment->extraPaymentData['paymentToken']) ? $payment->extraPaymentData['paymentToken'] : $payment->extraPaymentData['nonce']; 
            return [
                'subscription_no'=>'sub_'.time(),
                'payment_token'=>$paymentToken,
                'start_date'=>Helper::getMySqlDateFormat(time()),
                'end_date'=>'9999-12-31',
//                'cardType'=>'Dummy Card',
//                'last4'=>'XXXX',
//                'id'=>time(),
            ];
        }
        else
            return self::PAID;
    }      
    /**
     * Cancel recurring billing
     * @param string $subscriptionNo
     */
    public function cancel($subscriptionNo)
    {
        logInfo(__METHOD__.' subscription cancelled',$subscriptionNo);
        return $subscriptionNo;
    }  
    
}