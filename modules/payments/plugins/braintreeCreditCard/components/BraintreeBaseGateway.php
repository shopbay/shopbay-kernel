<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentBaseGateway');
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeApiTrait');
/**
 * Description of BraintreeBaseGateway
 *
 * @author kwlok
 */
abstract class BraintreeBaseGateway extends PaymentBaseGateway 
{
    use BraintreeApiTrait;
    
    public function init() 
    {
        parent::init(); 
    }
    /**
     * Get Braintree\Transaction::createSaleTransaction params
     * @param PaymentForm model
     * @return array
     */
    abstract public function getCreateSaleParams($payment);
    /**
     * Process payment
     * @param PaymentForm model
     */
    public function process($form,$returnTraceAsArray=false)
    {
        $braintree = $this->getBraintreeApi($form);
        $result = $braintree->createSaleTransaction($this->getCreateSaleParams($form));
        if ($result['success']){
            $form->trace_no = json_encode($this->createTraceNo($result['response']->transaction));
            Yii::app()->serviceManager->getPaymentManager()->pay($form);
            logInfo(__METHOD__.' braintree ok',$result);
            if ($returnTraceAsArray)
                return json_decode($form->trace_no,true);
            else
                return self::PAID;
        }
        else {
            logError(__METHOD__.' braintree error',$result);
            throw new CException(Sii::t('sii','Could not proceed payment').': '.$result['response']->message);
        }
    }     
    
}
