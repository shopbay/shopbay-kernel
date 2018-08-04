<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.widgets.HostedFieldsForm');
/**
 * Description of PayPalForm
 *
 * @author kwlok
 */
class PayPalForm extends HostedFieldsForm
{
    protected $braintreeCustomJs = 'braintree-paypal.js';
    public $containerId = 'paypal_container';       
    public $shopName;       
    public $currency;       
    public $shippingAddress;//override shipping address  
    /**
     * Init widget
     */
    public function init() 
    {
        if (!isset($this->shopName))
            throw new CException(Sii::t('sii','Shop name must be specified.'));
        if (!isset($this->currency))
            throw new CException(Sii::t('sii','Shop currency must be specified.'));
        
        parent::init();
    }    
    /**
     * @return config in HTML tag
     */
    public function getConfigTag() 
    {
        $html = CHtml::openTag('div',array('style'=>'display:none;'));
        $html .= CHtml::tag('span',array(
            'id'=>'braintree_paypal_config',
            'data-type'=>$this->type,
            'data-client-token'=>$this->clientToken,
            'data-shop-name'=>$this->shopName,
            'data-currency'=>$this->currency,
            'data-shipping-address'=>$this->shippingAddress,
            'data-container'=>$this->containerId));
        $html .= CHtml::closeTag('div');
        return $html;
    }
    /**
     * @return Html form
     */
    public function renderForm() 
    {
        //form header
        echo CHtml::openTag('div',array('class'=>'braintree-paypal-header'));
        echo CHtml::tag('span',array(),Sii::t('sii','Click the button to sign into your PayPal account and pay securely this order.'));
        echo CHtml::closeTag('div');
        echo CHtml::tag('div',array('id'=>$this->containerId),'');
    }    
    /**
     * @return PayPal configurations 
     */
    public function getOptions($excludeId=false)
    {
        return null;//not required; braintree setup is handled at braintree-custom.js
    }

}
