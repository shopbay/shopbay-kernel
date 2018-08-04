<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeBaseGateway');
/**
 * Description of BraintreePayPalGateway
 *
 * @author kwlok
 */
class BraintreePayPalGateway extends BraintreeBaseGateway 
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
    public function getCreateSaleParams($payment)
    {
        return [
            'amount' => round($payment->amount,2),//need to keep to precision 2, else Braintree will reject the transaction
            'paymentMethodNonce' => $payment->extraPaymentData['nonce'],
            /**
             * The merchant account ID used to create a transaction. 
             * Currency is also determined by merchant account ID. 
             * If no merchant account ID is specified, we will use the $merchantAccountId set in BraintreeApi
             * @see BraintreeApi
             */
            //'merchantAccountId' => '',
            'purchaseOrderNumber' => $payment->reference_no,
            'orderId' => $payment->reference_no,
            'options' => [
                'submitForSettlement' => true,
//                  'paypal' => [
//                  'customField' => 'PayPal custom field',
//                  'description' => 'Description for PayPal email receipt',
//              ],                    
            ]
        ];
    }
}
