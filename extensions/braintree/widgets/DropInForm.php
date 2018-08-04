<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.widgets.BraintreeBaseForm');
Yii::import('common.extensions.braintree.widgets.ShopBraintreeTrait');
/**
 * Description of DropInForm
 * 
 * @author kwlok
 */
class DropInForm extends BraintreeBaseForm
{
    use ShopBraintreeTrait;
    
    protected $type = 'dropin';
    /**
     * Init widget
     */
    public function init() 
    {
        $this->loadBraintreeParams();
        parent::init();
    }
    /**
     * @return Drop-In UI configurations
     */
    public function getOptions()
    {
        return json_encode([
            'container'=>$this->containerId,
        ]);
    }
    /**
     * @return Html form
     */
    public function renderForm() 
    {
        echo CHtml::form($this->formActionUrl, 'post', array('id'=>'braintree_checkout'));
        echo CHtml::tag('div',array('id'=>$this->containerId));
        echo CHtml::submitButton(Sii::t('sii','Pay'));
        echo CHtml::endForm();
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
            'data-container'=>$this->containerId));
        $html .= CHtml::closeTag('div');
        return $html;
    }
}
