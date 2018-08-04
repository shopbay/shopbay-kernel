<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
Yii::import('common.modules.orders.components.OrderNumberGenerator');
/**
 * Description of OrdersSettingsForm
 *
 * @author kwlok
 */
class OrdersSettingsForm extends BaseShopSettingsForm 
{
    /*
     * Indicate if to process each items in an order individually
     */
    public $processEachItems;
    /*
     * Indicate which fashion of purchase order number generator to use
     */
    public $poNumPrefix;
    /*
     * Indicate which random string fashion of purchase order number generator to use
     */
    public $poNumRandomString;
    /*
     * Specify the purchase number separator
     */
    public $poNumSeparator;
    /*
     * Specify the purchase number template
     */
    public $poNumTemplate;
    /*
     * Specify the purchase number counter (when fashion is OrderNumberGenerator::COUNTER)
     */
    public $poNumCounter;//default null
    /*
     * Indicate which fashion of shipping order number generator to use
     */
    public $soNumPrefix;
    /*
     * Indicate which random string fashion of shipping order number generator to use
     */
    public $soNumRandomString;
    /*
     * Specify the shipping number template
     */
    public $soNumSeparator;
    /*
     * Specify the shipping number template
     */
    public $soNumTemplate;
    /*
     * Specify the shipping number counter (when fashion is OrderNumberGenerator::COUNTER)
     */
    public $soNumCounter;
    /**
     * Init
     */
    public function init()
    {
        //load default values
        $default = ShopSetting::getOrdersDefaultSetting();
        $this->processEachItems = $default['processEachItems'];
        $this->poNumPrefix = $default['poNumPrefix'];
        $this->poNumRandomString = $default['poNumRandomString'];
        $this->poNumSeparator = $default['poNumSeparator'];
        $this->poNumTemplate = $default['poNumTemplate'];
        $this->poNumCounter = $default['poNumCounter'];
        $this->soNumPrefix = $default['soNumPrefix'];
        $this->soNumRandomString = $default['soNumRandomString'];
        $this->soNumSeparator = $default['soNumSeparator'];
        $this->soNumTemplate = $default['soNumTemplate'];
        $this->soNumCounter = $default['soNumCounter']; 
    }
    
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['processEachItems', 'required'],
            ['processEachItems', 'numerical', 'integerOnly'=>true],
            ['processEachItems', 'ruleProcessing'],
            ['poNumRandomString, soNumRandomString', 'required'],
            ['poNumSeparator, soNumSeparator', 'required'],
            ['poNumTemplate, soNumTemplate', 'length','max'=>60],
            ['poNumPrefix, soNumPrefix', 'length', 'max'=>2],
            ['poNumTemplate, soNumTemplate', 'ruleTemplate'],
        ]);
    }    
    /**
     * Item processings rule check
     * (1) Verify that need at least 1 product shipping must be online
     */
    public function ruleProcessing($attribute,$params)
    {
        foreach (Item::model()->locateShop($this->shop_id)->findAll() as $item) {
            if (in_array($item->status,Item::model()->getItemInProcessing())){
                logTrace(__METHOD__.' item status = '.$item->status);
                $this->addError($attribute,Sii::t('sii','There are items pending processed. You are only allowed to switch processing mode when all items are fully processed.'));
                break;
            }
        }
    }
    /**
     * Order number template validation
     * {checkum} and {randomstring} must be present
     * {prefix} and {separator} is optional
     */
    public function ruleTemplate($attribute,$params)
    {
        if (!preg_match('/^([\w]|{|}|-)+$/', $this->$attribute))//only allow alphanumeric or "{" or "}" or "-"
            $this->addError($attribute,Sii::t('sii','Template contains invalid characters.'));
        
        if (strpos($this->$attribute, '{checksum}')==false)
            $this->addError($attribute,Sii::t('sii','{pattern} must be present to form part of order number.',['{pattern}'=>'{checksum}']));
        
        if (strpos($this->$attribute, '{randomstring}')==false)
            $this->addError($attribute,Sii::t('sii','{pattern} must be present to form part of order number.',['{pattern}'=>'{randomstring}']));
        
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'processEachItems' => Sii::t('sii','Process each items'),
            'poNumRandomString' => Sii::t('sii','PO Number Random String'),
            'poNumSeparator' => Sii::t('sii','PO Number Separator'),
            'poNumTemplate' => Sii::t('sii','PO Number Template'),
            'poNumPrefix' => Sii::t('sii','PO Number Prefix'),
            'poNumCounter' => Sii::t('sii','PO Number Sequence Number'),
            'soNumRandomString' => Sii::t('sii','SO Number Random String'),
            'soNumSeparator' => Sii::t('sii','SO Number Separator'),
            'soNumTemplate' => Sii::t('sii','SO Number Template'),
            'soNumPrefix' => Sii::t('sii','SO Number Prefix'),
            'soNumCounter' => Sii::t('sii','SO Number Sequence Number'),
        ]);
    }
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),[
            'processEachItems'=>OrdersSettingsForm::getItemProcessingOptions($this->processEachItems),
            'poNumRandomString'=>OrdersSettingsForm::getOrderNumRandomStringOptions($this->poNumRandomString),
            'poNumPrefix'=>CHtml::tag('div',['class'=>'data-element'],$this->poNumPrefix),
            'poNumSeparator'=>OrdersSettingsForm::getOrderNumSeparatorOptions($this->poNumSeparator),
            'poNumTemplate'=>CHtml::tag('div',['class'=>'data-element'],$this->poNumTemplate),
            'poNumCounter'=>CHtml::tag('div',['class'=>'data-element'],OrderNumberGenerator::formatCounter($this->poNumCounter)),
            'soNumRandomString'=>OrdersSettingsForm::getOrderNumRandomStringOptions($this->soNumRandomString),
            'soNumPrefix'=>CHtml::tag('div',['class'=>'data-element'],$this->soNumPrefix),
            'soNumSeparator'=>OrdersSettingsForm::getOrderNumSeparatorOptions($this->soNumSeparator),
            'soNumTemplate'=>CHtml::tag('div',['class'=>'data-element'],$this->soNumTemplate),
            'soNumCounter'=>CHtml::tag('div',['class'=>'data-element'],OrderNumberGenerator::formatCounter($this->soNumCounter)),
        ]);
    }     
        
    public static function getItemProcessingOptions($mode=null)
    {
        if (!isset($mode)){
            return [
                ShopSetting::$itemProcessSkip=>Sii::t('sii','No, process all items as a whole'),
                ShopSetting::$itemProcess1Step=>Sii::t('sii','Yes, use simple workflow (Ship)'),
                ShopSetting::$itemProcess3Step=>Sii::t('sii','Yes, use 3-steps workflow (Pick, Pack, Ship)'),
            ];
        }
        else {
            $modes = self::getItemProcessingOptions();
            return $modes[$mode];
        }
    }
    
    public static function getOrderNumRandomStringOptions($mode=null)
    {
        if (!isset($mode)){
            return [
                OrderNumberGenerator::ALPHABETS=>Sii::t('sii','Alphabet letters'),
                OrderNumberGenerator::ALPHANUMERIC=>Sii::t('sii','Alphabet letters and numeric digits'),
                OrderNumberGenerator::DATETIME=>Sii::t('sii','Date and time (Format:YYYYMMDDHHmmss)'),
                OrderNumberGenerator::COUNTER=>Sii::t('sii','Running sequence number'),
            ];
        }
        else {
            $modes = self::getOrderNumRandomStringOptions();
            return $modes[$mode];
        }
    }    
    
    public static function getOrderNumSeparatorOptions($mode=null)
    {
        if (!isset($mode)){
            return [
                OrderNumberGenerator::SEPARATOR_DASH=>OrderNumberGenerator::SEPARATOR_DASH,
            ];
        }
        else {
            $modes = self::getOrderNumSeparatorOptions();
            return $modes[$mode];
        }
    }  
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_orders';       
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
        //section 1: Orders Processing 
        $sections->add(['id'=>'orders_processing',
                             'name'=>Sii::t('sii','Orders Processing'),
                             'heading'=>true,'top'=>true,
                             'viewFile'=>'_form_orders_processing','viewData'=>['model'=>$this]]);
        //section 2: Purchase Order
        $sections->add(['id'=>'po_setting',
                             'name'=>Sii::t('sii','PO Number'),
                             'heading'=>true,
                             'viewFile'=>'_form_orders_ponum','viewData'=>['model'=>$this]]);
        //section 3: Shipping Order
        $sections->add(['id'=>'so_setting',
                             'name'=>Sii::t('sii','SO Number'),
                             'heading'=>true,
                             'viewFile'=>'_form_orders_sonum','viewData'=>['model'=>$this]]);
        return $sections->toArray();
    }     
}
