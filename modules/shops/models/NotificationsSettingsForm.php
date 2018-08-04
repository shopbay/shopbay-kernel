<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.ShopSettings');
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
/**
 * Description of NotificationsSettingsForm
 *
 * @author kwlok
 */
class NotificationsSettingsForm extends BaseShopSettingsForm 
{
    public $lowInventory = 0;//default 0=No (1=yes)
    public $lowInventoryThreshold = 0.2;//default 20% of inventory quantity
    public $emailSenderName;
    /**
     * Init
     */
    public function init()
    {
        //load default values
        $this->lowInventory = ShopSetting::$defaultNotificationsLowInventory;
    }
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('lowInventory, lowInventoryThreshold', 'required'),
            array('emailSenderName', 'length', 'max'=>100),
            array('lowInventory', 'boolean'),
            array('lowInventoryThreshold', 'numerical', 'min'=>0, 'max'=>1),
        ));
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'lowInventory' => Sii::t('sii','Enable Low Inventory Notification'),
            'lowInventoryThreshold' => Sii::t('sii','Threshold hit to trigger Low Inventory Notification'),
            'emailSenderName' => Sii::t('sii','Email sender name'),
        ));
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array_merge(parent::attributeToolTips(),array(
            'lowInventoryThreshold'=>Sii::t('sii','E.g. input 0.2 to indicate thresold is below 20%'),
        ));
    }  
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),array(
            'emailSenderName'=>CHtml::tag('div',array('class'=>'data-element'),$this->emailSenderName),
            'lowInventory'=>CHtml::tag('div',array('class'=>'data-element'),Helper::getBooleanValues($this->lowInventory)),
            'lowInventoryThreshold'=>CHtml::tag('div',array('class'=>'data-element'),$this->lowInventoryThreshold),
        ));
    }     
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_notifications';       
    }         
    /**
     * OVERRIDDEN
     * This render the setting form
     * @param type $controller The controller used to render the form
     */
    public function renderActiveForm($controller)
    {
        $this->renderForm($controller);
    }    
    
    public function getSectionsData() 
    {
        $sections = new CList();
        //section 1: Low inventry 
        $sections->add(array('id'=>'inventory_setting',
                             'name'=>Sii::t('sii','Low Inventory Alerts'),
                             'heading'=>true,'top'=>true,
                             'viewFile'=>'_form_notifications_inventory','viewData'=>array('model'=>$this)));
        //section 2: Email templates
        $sections->add(array('id'=>'email_setting',
                             'name'=>Sii::t('sii','Email Templates'),
                             'heading'=>true,
                             'viewFile'=>'_form_notifications_email','viewData'=>array('model'=>$this)));
        return $sections->toArray();
    }  
    
}
