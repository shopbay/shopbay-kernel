<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeBaseGateway');
/**
 * Description of BraintreeCreditCardTokenGateway
 * This is to pay by payment method token (for user already has payment token stored at vault / db)
 * 
 * @author kwlok
 */
class BraintreeCreditCardTokenGateway extends BraintreeBaseGateway 
{
    public function init() 
    {
        parent::init(); 
    }
    /**
     * Get Braintree\Transaction::createSaleTransaction params
     * @param PaymentForm model
     * @return array
     */
    public function getCreateSaleParams($form)
    {
        if (!$form instanceof PaymentForm){
            throw new CException(Sii::t('sii','Invalid payment form'));
        }
        
        return [
            /**
             * A string value representing an existing customer in your Vault that you want to charge.
             */
            'customerId' => $form->payer,
            'amount' => round($form->amount,2),//need to keep to precision 2, else Braintree will reject the transaction
            'paymentMethodToken' => $form->extraPaymentData['paymentToken'],
            /**
             * The merchant account ID used to create a transaction. 
             * Currency is also determined by merchant account ID. 
             * If no merchant account ID is specified, we will use the $merchantAccountId set in BraintreeApi
             * @see BraintreeApi
             */
            'merchantAccountId' => $form->paymentGatewayData['braintree']['merchantAccountId'],
            'orderId' => $form->reference_no,
            'purchaseOrderNumber' => $form->reference_no,
            'options' => [
                'submitForSettlement' => true,
            ]
        ];
    }
   
}
