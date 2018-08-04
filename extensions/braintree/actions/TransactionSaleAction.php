<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.BraintreeApi');
/**
 * A simple Braintree sale transaction creation 
 *
 * @author kwlok
 */
class TransactionSaleAction extends CAction
{
    public $environment = 'sandbox'; //'sandbox' or 'production'
    public $merchantId;
    public $publicKey;
    public $privateKey;
    /**
     * Run create sale transaction (used for DropIn)
     */
    public function run() 
    {
        $braintree = new BraintreeApi($this->environment,$this->merchantId,$this->publicKey,$this->privateKey);
        if (!user()->isGuest && isset($_POST["payment_method_nonce"]) && isset($_POST["order_amount"])){
            $nonce = $_POST["payment_method_nonce"];
            $amount = $_POST["order_amount"];
            $result = $braintree->createSaleTransaction([
                'amount' => $amount,
                'paymentMethodNonce' => $nonce,
            ]);
            echo dump($result); 
            Yii::app()->end();
        }
        throwError403(Sii::t('sii','Unauthorized Access'));
    } 
}
