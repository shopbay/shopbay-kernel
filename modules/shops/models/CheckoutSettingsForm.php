<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
/**
 * Description of CheckoutSettingsForm
 *
 * @author kwlok
 */
class CheckoutSettingsForm extends BaseShopSettingsForm 
{
    public $cartItemsLimit = 99;
    public $checkoutQtyLimit = 8;
    public $allowReturn = 1;//default yes
    public $guestCheckout = 1;//default yes
    public $registerToViewMore = 1;//default yes; Need to be a member signed in to view more items
    public $productOverlayView = 0;//default no; If true, product will be rendered in modal view, instead of direct
    /**
     * Init
     */
    public function init()
    {
        //load default values
        $this->cartItemsLimit = ShopSetting::$defaultCheckoutCartItemsLimit;
        $this->checkoutQtyLimit = ShopSetting::$defaultCheckoutQtyLimit;
    }
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['cartItemsLimit, checkoutQtyLimit, allowReturn, guestCheckout, registerToViewMore, productOverlayView', 'required'],
            ['cartItemsLimit, checkoutQtyLimit', 'numerical', 'min'=>1, 'max'=>100, 'integerOnly'=>true],
            ['allowReturn, guestCheckout, registerToViewMore, productOverlayView', 'boolean'],
        ]);
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'cartItemsLimit' => Sii::t('sii','Shopping cart items limit'),
            'checkoutQtyLimit' => Sii::t('sii','Checkout quantity limit per item'),
            'allowReturn' => Sii::t('sii','Allow returns'),
            'guestCheckout' => Sii::t('sii','Allow guest checkout'),
            'registerToViewMore' => Sii::t('sii','Guest must register to view more products'),
            'productOverlayView' => Sii::t('sii','Use quick view to display product'),
        ]);
    }
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),[
            'allowReturn'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->allowReturn)),
            'guestCheckout'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->guestCheckout)),
            'registerToViewMore'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->registerToViewMore)),
            'productOverlayView'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->productOverlayView)),
        ]);
    }     
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_checkout';       
    }         
    
}
