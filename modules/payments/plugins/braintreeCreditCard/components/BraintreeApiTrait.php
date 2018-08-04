<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.BraintreeApi');
/**
 * Description of BraintreeApiTrait
 *
 * @author kwlok
 */
trait BraintreeApiTrait 
{
    protected $apiMode;
    protected $merchantId;
    protected $publicKey;
    protected $privateKey;
    protected $merchantAccountId;
    protected $merchantCurrency;//If not set, will use the first merchant account id found in shopbay.json
    /**
     * Return the main braintree parameters used in API sending
     * @return array
     */
    public function getBraintreeParams()
    {
        return [
            'apiMode',
            'merchantId',
            'publicKey',
            'privateKey',
            'merchantAccountId',
        ];
    }
    /**
     * Set which currency to use for payment
     * @param type $currency
     */
    public function setBraintreeCurrency($currency)
    {
        $this->merchantCurrency = $currency;
    }
    /**
     * Get BraintreeApi object
     * @param type $form
     * @param type $currency To determine which merchant account id to be used
     * @return \BraintreeApi
     * @throws CException
     */
    public function getBraintreeApi($form=null)
    {
        $config = $this->getBraintreeConfig($form);
        return $this->constructBraintreeApi($config);
    }
    /**
     * Another helper method to construct BraintreeApi object with configuration data
     * @param array $config
     * @return \BraintreeApi
     * @throws CException
     */
    public function constructBraintreeApi($config=[])
    {
        return new BraintreeApi($config['apiMode'], $config['merchantId'], $config['publicKey'], $config['privateKey'], $config['merchantAccountId']);
    }
    /**
     * Get Braintree configuration data
     * Note: Currency needs to be set before calling this method
     * @see self::setBraintreeCurrency()
     * 
     * @param type $form
     * @param type $currency
     * @return array
     * @throws CException
     */
    public function getBraintreeConfig($form=null)
    {
        if (!isset($form)){
            $this->apiMode = readConfig('braintree', 'apiMode');
            $this->merchantId = readConfig('braintree', 'merchantId');
            $this->publicKey = readConfig('braintree', 'publicKey');
            $this->privateKey = readConfig('braintree', 'privateKey');
            $this->merchantAccountId = $this->parseMerchantAccount();
        }
        else {
            if (!$form instanceof PaymentForm && !isset($form->extraPaymentData['nonce'])){
                throw new CException(Sii::t('sii','Payment nonce not found'));
            }

            if (isset($form->paymentGatewayData['braintree'])){
                foreach ($this->braintreeParams as $param) {
                    $this->$param = $form->paymentGatewayData['braintree'][$param];
                }
            }
            elseif ($form->hasShop){
                $model = $form->getPaymentMethod($form->method);
                foreach ($this->braintreeParams as $param) {
                    $this->$param = $model->getParamsAttributeAsString($param);
                }
            }
        }
        
        $result = [];
        foreach ($this->braintreeParams as $param) {
            $result[$param] = $this->$param;
        }
        return $result;
    }
    /**
     * Create Trace No for internal reference (stores partial transaction data
     * @param object $transaction The transaction object return by Braintree
     * @return array
     */
    public function createTraceNo($transaction)
    {
        return [
            'id'=>$transaction->id,
            'planId' => $transaction->planId,
            'subscriptionId' => $transaction->subscriptionId,
            'createdAt' => $transaction->createdAt->format('Y-m-d H:i:s'),
            'currency'=>$transaction->currencyIsoCode,
            'amount'=>$transaction->amount,
            'orderId'=>$transaction->orderId,
            'processorAuthorizationCode' => $transaction->processorAuthorizationCode,
            'processorResponseCode' => $transaction->processorResponseCode,
            'processorResponseText' => $transaction->processorResponseText,
            'purchaseOrderNumber'=>$transaction->purchaseOrderNumber,
            'last4' => $transaction->creditCard['last4'],
            'cardType' => $transaction->creditCard['cardType'],
         ];
    } 
    /**
     * Select which merchant account to use based on currency
     * @param string $currency 
     */
    protected function parseMerchantAccount()
    {
        $merchantAccounts = readConfig('braintree', 'merchantAccounts');
        if (isset($this->merchantCurrency) && isset($merchantAccounts[$this->merchantCurrency]))
            return $merchantAccounts[$this->merchantCurrency];
        else {
            foreach ($merchantAccounts as $currency => $merchantAccountId) {
                return $merchantAccountId;//return the first found
            }
            //Error when hit here! in the event $merchantAccounts is empty (happen only when shopbay.json config is not correctly configured
            return null;
        }
    }
}
