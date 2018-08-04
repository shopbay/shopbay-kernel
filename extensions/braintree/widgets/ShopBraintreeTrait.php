<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShopBraintreeTrait
 *
 * @author kwlok
 */
trait ShopBraintreeTrait
{
    public    $formActionUrl;//the form url to be replaced
    public    $shopId;//the shop that uses Braintree as payment gateway
    public    $braintreeMethod;//the braintree method used; E.g. credit card, Paypal etc
    
    public function loadBraintreeParams()
    {
        if (!isset($this->shopId))
            throw new CException(Sii::t('sii','Shop must be specified.'));
        if (!isset($this->formActionUrl))
            throw new CException(Sii::t('sii','Form Action Url must be specified.'));
        
        $model = PaymentMethod::model()->shopAndMethod($this->shopId,$this->braintreeMethod)->find();
        if ($model===null)
            throw new CException(Sii::t('sii','Payment Method model not found.'));
        
        $this->apiMode = $model->getParamsAttributeAsString('apiMode');
        $this->merchantId = $model->getParamsAttributeAsString('merchantId');
        $this->publicKey = $model->getParamsAttributeAsString('publicKey');
        $this->privateKey = $model->getParamsAttributeAsString('privateKey');
        $this->merchantAccountId = $model->getParamsAttributeAsString('merchantAccountId');
    }
}
