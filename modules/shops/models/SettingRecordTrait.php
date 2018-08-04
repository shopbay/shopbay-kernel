<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.orders.components.OrderNumberGenerator');
/**
 * Description of SettingRecordTrait

 * @author kwlok
 */
trait SettingRecordTrait 
{
    public static $checkout      = 'checkout';
    public static $orders        = 'orders';
    public static $navigation    = 'navigation';
    public static $notifications = 'notifications';
    public static $marketing     = 'marketing';
    public static $brand         = 'brand';
    public static $seo           = 'seo';
    public static $chatbot       = 'chatbot';
    /*
     * For value 0: all items in an order will be processed as a whole
     */
    public static $itemProcessSkip  = 0;
    /*
     * For value 1: each items in an order needs to be processed seperately and using 1 step (Ship) workflow
     */
    public static $itemProcess1Step  = 1;
    /*
     * For value 2: each items in an order needs to be processed seperately and using 3 steps (Pick, Pack, Ship) workflow
     */
    public static $itemProcess3Step  = 2;
    /*
     * Default setting values
     */
    public static $defaultNotificationsLowInventory = 0;//default 0=No (1=yes)
    public static $defaultCheckoutCartItemsLimit = 99;//default 99
    public static $defaultCheckoutQtyLimit = 8;//default 8
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ShopAddress the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function settingAttributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            static::$navigation => self::getLabel(static::$navigation),
            static::$notifications => self::getLabel(static::$notifications),
            static::$brand => self::getLabel(static::$brand),
            static::$seo => self::getLabel(static::$seo),
            static::$chatbot => self::getLabel(static::$chatbot),
            static::$checkout => self::getLabel(static::$checkout),
            static::$marketing => self::getLabel(static::$marketing),
            static::$orders => self::getLabel(static::$orders),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }

    public function getAttributeDescription($attribute) 
    {
        $descs = $this->attributeDescriptions();
        if (isset($descs[$attribute]))
            return $descs[$attribute];
        else
            return null;
    }

    public function customDomain($domain) 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'brand like \'%customDomain":"'.$domain.'%\'']);
        return $this;
    }  
    
    public function myDomain($domain) 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'brand like \'%myDomain":"'.$domain.'%\'']);
        return $this;
    }  
    /**
     * Get form instance by $attribute
     * @param type $attribute
     * @param type $loadValues If to load values
     * @return type
     */
    public function getFormInstance($attribute,$loadValues,$formKeyAttribute)
    {
        $formClass = ucfirst($attribute).'SettingsForm';
        $formName = $formClass;
        $formInstance = new $formName();
        $formInstance->$formKeyAttribute = $this->$formKeyAttribute;
        if ($loadValues)
            $formInstance = $this->getFormValues($formInstance, $attribute);
        return $formInstance;
    }
    /**
     * Load form instance values by attribute
     * @param type $form
     * @param type $attribute
     * @return type
     */
    public function getFormValues($form,$attribute)
    {
        if ($this->$attribute!=null){
            $data = $this->getSettings($attribute);
            foreach ($data as $key => $value) {
                $form->$key = $value;
            }
        }
        return $form;
    }
    /**
     * Get setting by attribute
     * @param type $attribute
     * @return array of setting values
     */
    public function getSettings($attribute)
    {
        $settings = json_decode($this->$attribute,true);
        if (empty($settings))
            return [];
        return $settings;
    }
    /**
     * Get particular setting value
     * @param type $attribute
     * @param type $field
     * @param type $useDefault Use default value if true; Else, return null if not field value found
     * @return type
     */
    public function getValue($attribute,$field,$useDefault=false)
    {
        $settings = $this->getSettings($attribute);
        if ($useDefault){
            if (isset($settings[$field]))
                return $settings[$field];
            else {
                logInfo(__METHOD__." Loading $attribute->$field default value not found: Use form default value");
                return $this->getForm($attribute,false)->$field;
            }
        }
        else
            return isset($settings[$field])?$settings[$field]:null;
    }
    /**
     * This method is used by activity recording
     * @return type
     */
    public function getName()
    {
        return $this->displayName();
    }
    /**
     * This method is used for shop favicon
     * @return type
     */
    public function getImage()
    {
        $brand = $this->getSettings(static::$brand);
        return isset($brand['favicon'])?$brand['favicon']:null;
    }
    /**
     * This method is a callback to save favicon
     * @return type
     */
    public function saveFavicon($image)
    {
        $brand = $this->getSettings(static::$brand);
        $brand['favicon'] = $image;
        //Note: this method is used by bypass 'afterSave()' event
        $this->updateByPk($this->id,[static::$brand=>json_encode($brand)]);
        logTrace(__METHOD__.' ok',$brand);
    }

    public static function getLabel($attribute)
    {
        if ($attribute==static::$seo)
            return Sii::t('sii','SEO');
        else
            return Sii::t('sii',ucfirst($attribute));
    }
    
    public static function getLocaleValue($value,$lang)
    {
        return isset($value[$lang])?$value[$lang]:'';
    }
    /**
     * Get order setting default value
     * @see OrdersSettingForm for each field definition
     */
    public static function getOrdersDefaultSetting($field=null)
    {
        $default = [
            'processEachItems' => static::$itemProcessSkip,
            'poNumPrefix' => OrderNumberGenerator::PREFIX_PO,
            'poNumRandomString' => OrderNumberGenerator::ALPHANUMERIC,//default value
            'poNumSeparator' => OrderNumberGenerator::SEPARATOR_DASH,//default separator
            'poNumTemplate' => OrderNumberGenerator::TEMPLATE,//default template
            'poNumCounter' => null,//default null
            'soNumPrefix' => OrderNumberGenerator::PREFIX_SO,//default value
            'soNumRandomString' => OrderNumberGenerator::ALPHANUMERIC,//default value
            'soNumSeparator' => OrderNumberGenerator::SEPARATOR_DASH,//default separator
            'soNumTemplate' => OrderNumberGenerator::TEMPLATE,//default template
            'soNumCounter' => null,//default null            
        ];
        
        if (array_key_exists($field, $default))
            return $default[$field];
        else
            return $default;
    }       

}
