<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.widgets.BraintreeBaseForm');
Yii::import('common.extensions.braintree.widgets.ShopBraintreeTrait');
Yii::import('common.extensions.braintree.widgets.HostedFieldsTrait');
/**
 * Description of HostedFieldsForm
 *
 * @author kwlok
 */
class HostedFieldsForm extends BraintreeBaseForm
{
    use HostedFieldsTrait {
        init as traitInit;
    }    
    use ShopBraintreeTrait;
    
    public    $attachSubmitButton = true;
    /**
     * Init widget
     */
    public function init() 
    {
        $this->loadBraintreeParams();
        $this->traitInit();
    }   
    /**
     * @return config in HTML tag
     */
    public function getConfigTag() 
    {
        $html = CHtml::openTag('div',array('style'=>'display:none;'));
        $html .= CHtml::tag('span',array(
            'id'=>'braintree_config',
            'data-type'=>$this->type,
            'data-client-token'=>$this->clientToken,
            'data-form-url'=>$this->formActionUrl,
            'data-container'=>$this->containerId,
            'data-hosted-fields'=>$this->getOptions(true)));
        $html .= CHtml::closeTag('div');
        return $html;
    }  
    /**
     * @return Html form
     */
    public function renderForm() 
    {
        echo CHtml::form($this->formActionUrl, 'post', array('id'=>$this->containerId));
        //form header
        echo CHtml::openTag('div',array('class'=>'braintree-hosted-fields-header'));
        echo CHtml::tag('span',array(),Sii::t('sii','You have chosen credit card to pay this order.'));
        $icons = '';
        foreach ($this->getCreditCardIcons() as $icon) {
            $icons .= CHtml::image($icon['imageUrl'],$icon['alt'],array('style'=>'width:40px;padding-right:5px;'));
        }
        echo CHtml::tag('span',array('class'=>'credit-card-icons','data-base-url'=>CHtml::encode($this->iconBaseUrl)),$icons);
        echo CHtml::closeTag('div');
        //begin form body wrapper
        echo CHtml::openTag('div',array('id'=>'card_wrapper'));
        //  card number
        echo CHtml::label(Sii::t('sii','Card Number'),'card_number',array('required'=>true));
        echo CHtml::tag('div',array('id'=>'card_number'),'');
        //  expiration date wrapper
        echo CHtml::openTag('div',array('id'=>'expiration_date_wrapper'));
        echo CHtml::label(Sii::t('sii','Expiration Date'),'expiration_date',array('required'=>true));
        echo CHtml::tag('div',array('id'=>'expiration_date'),'');
        echo CHtml::closeTag('div');
        //  cvv wrapper
        echo CHtml::openTag('div',array('id'=>'cvv_wrapper'));
        echo CHtml::label(Sii::t('sii','CVV'),'cvv',array('required'=>true));
        echo CHtml::tag('div',array('id'=>'cvv'),'');
        echo CHtml::closeTag('div');
        //end form body wrapper
        echo CHtml::closeTag('div');
        if ($this->attachSubmitButton)
            echo CHtml::submitButton(Sii::t('sii','Pay'));
        echo CHtml::endForm();
    }
    
}
