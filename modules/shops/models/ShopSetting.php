<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.SettingRecordTrait');
Yii::import("common.modules.pages.models.PageTrait");
/**
 * This is the model class for table "s_shop_setting".
 *
 * The followings are the available columns in table 's_shop_setting':
 * @property integer $id
 * @property integer $shop_id
 * @property string $marketing e.g. add shop to facebook page, manage facebook ads etc
 * @property string $checkout e.g. returns and refunds policy, how to process orders, getting customer to accept marketing emails
 * @property string $payments e.g. allow manual payment, online payments etc
 * @property string $shipping e.g. allow manual shipping, third-party fulfillment services etc
 * @property string $tax e.g. tax hanlding, gst etc
 * @property string $navigation e.g. how navigation menu is structured and display in shop storefront
 * @property string $notifications e.g. indicate which notifications to receive, and customize email notification template
 * @property string $brands
 * @property string $seo
 * @property string $chatbot
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Shop $shop
 *
 * @author kwlok
 */
class ShopSetting extends SActiveRecord 
{
    use SettingRecordTrait, PageTrait;
    
    public static function getList()
    {
        return [
            static::$checkout,static::$orders,
            static::$navigation,static::$notifications,static::$marketing,static::$brand,
            static::$seo,static::$chatbot,
        ];
    }
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->fallbackPageTitle = false;
    }    
    /**
     * @inheritdoc
     */
    protected function getMetaTagAttribute()
    {
        return 'seo';
    }
    /**
     * @inheritdoc
     */
    protected function getSeoModel()
    {
        return $this->shop;
    }
   /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_shop_setting';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shop Settings',[$mode]);
    }         
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],              
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountSource'=>'shop',
            ], 
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
                'shopProxy'=>true,
            ], 
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
                //'iconUrlSource'=>'shop',
            ],
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>true,
                'label'=>Sii::t('sii','Favicon'),
                'stateVariable'=>SActiveSession::SHOP_FAVICON,
                'imageDefault'=>Image::DEFAULT_IMAGE_SHOP,
                'setMediaOwnerMethod'=>'saveFavicon',
            ],
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop_id', 'required'],
            ['shop_id', 'numerical', 'integerOnly'=>true],
            ['marketing, checkout, orders, navigation, notifications, brand', 'length', 'max'=>5000],            
            ['id, shop_id, marketing, checkout, orders, navigation, notifications, brand, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),$this->settingAttributeLabels(),[
            'shop_id' => Sii::t('sii','Shop'),
        ]);
    }
    /**
     * @return array customized attribute description (name=>desc)
     */
    public function attributeDescriptions()
    {
        return [
            static::$checkout=>Sii::t('sii','This controls how customer checkout from their shopping cart and other purchasing settings.'),
            static::$orders=>Sii::t('sii','This controls how you want to process order.'),
            static::$navigation=>Sii::t('sii','This setup your shop\'s navigation menu.'),
            static::$notifications=>Sii::t('sii','This controls how you want to get notifiied for shop events.'),
            static::$marketing=>Sii::t('sii','This gives you additional marketing power to get your shop better known and reach out to more customer.'),
            static::$brand=>Sii::t('sii','This gives you additional branding power to your shop by having custom shop url and favicon.'),
            static::$seo=>Sii::t('sii','This control let customers to be able to find your shop easier at all search engines, e.g. Google, Bing etc.'),
            static::$chatbot=>Sii::t('sii','This gives you additional sales channel to reach more customers on IM apps, e.g. Facebook Messenger.'),
        ];
    }
    
    public function facebookPage($pageId) 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'marketing like \'%"fbPageData":{"'.$pageId.'":"1"}%\'']);
        return $this;
    }  
    
    public function orderProcessing($processType) 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'orders like \'%"processEachItems":"'.$processType.'"%\'']);
        return $this;
    }  
        
    public function lowInventoryEnabled() 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'notifications like \'%lowInventory":"1%\'']);
        return $this;
    }  
    /**
     * Get form instance by $attribute
     * @param type $attribute
     * @param type $loadValues If to load values
     * @return type
     */
    public function getForm($attribute,$loadValues=true)
    {
        return $this->getFormInstance($attribute,$loadValues,'shop_id');
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('shop/settings/'.$this->shop->slug);
    }  
}
