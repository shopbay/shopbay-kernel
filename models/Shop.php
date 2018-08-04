<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Administerable");
Yii::import("common.services.workflow.behaviors.ShopWorkflowBehavior");
Yii::import("common.modules.shops.behaviors.ShopBehavior");
Yii::import("common.modules.shops.models.ShopSetting");
Yii::import("common.modules.shops.models.ShopAddress");
Yii::import("common.modules.shops.models.ShopTheme");
Yii::import('common.modules.shops.models.ShopDomainForm');
Yii::import("common.modules.shops.behaviors.ShopSearchBehavior");
Yii::import("common.modules.media.behaviors.SingleMediaBehavior");
Yii::import("common.modules.plans.models.Subscription");
Yii::import("common.modules.chatbots.models.ChatbotOwnerInterface");
/**
 * This is the model class for table "s_shop".
 *
 * The followings are the available columns in table 's_shop':
 * @property integer $id
 * @property integer $account_id
 * @property string $name
 * @property string $tagline
 * @property string $slug
 * @property integer $image
 * @property string $contact_person
 * @property string $contact_no
 * @property string $email
 * @property string $timezone
 * @property string $language
 * @property string $currency
 * @property string $weight_unit
 * @property integer $category
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Product[] $products
 * @property SAccount $account
 *
 * @author kwlok
 */
class Shop extends Administerable implements ChatbotOwnerInterface
{
    const SCENARIO_MANUAL_SLUG = 'api-create';//this is to inline with app\models\ApiModel, and also to support manual slug
    const DEMO_SHOP = -1;
    const NAME_SUFFIX = '_';//used by shop prototype form
    public $suspendedStatus = Process::SHOP_SUSPENDED;
    public $suspendableStatus = Process::SHOP_ONLINE;
    protected $sub;//subscription instance
    private $_t;//store any found theme instance
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shop the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shop|Shops',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_shop';
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
            ],         
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ],         
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
            ],
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=>[
                    'method'=>'getSlugValue',
                ],
                'skipScenario'=>self::SCENARIO_MANUAL_SLUG,//refer to \app\models\ApiModel::SCENARIO_CREATE,
                'maxLength'=>50,
            ],
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>true,
                'label'=>Sii::t('sii','Logo'),
                'stateVariable'=>SActiveSession::SHOP_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_SHOP,
            ],
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::SHOP_ONLINE,
                'inactiveStatus'=>Process::SHOP_OFFLINE,
            ], 
            'transitionWorkflow' => [
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
                'subTransitionModelsCallback'=>'prepareTransitionAssociatedMedia',
            ],              
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.ShopWorkflowBehavior',
            ],              
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>[
                    'enable'=>true,
                ],
            ],
            'searchable' => [
                'class'=>'common.modules.search.behaviors.SearchableBehavior',
                'searchModel'=>'SearchShop',
            ],        
            'shopsearch' => [
                'class'=>'common.modules.shops.behaviors.ShopSearchBehavior',
            ],
            'configurable' => [
                'class'=>'common.modules.shops.behaviors.ShopConfigBehavior',
            ],
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],                
            'shopbehavior' => [
                'class'=>'ShopBehavior',
            ],                           
            'chatbotconfigbehavior' => [
                'class'=>'common.modules.chatbots.behaviors.ChatbotConfigBehavior',
            ],        
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, contact_person, contact_no, email, status, timezone, language, currency, weight_unit, category', 'required'],
            
            ['account_id, image, category', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            ['name, tagline', 'length', 'max'=>1000],
            ['name', 'ruleNameUnique'],
            ['slug', 'length', 'max'=>50],
            ['slug', 'ruleUnique'],
            ['slug', 'ruleSlugAsSubdomain'],
            ['slug', 'ruleSlugWhitelist'],
            ['email', 'unique'],
            ['email', 'length', 'max'=>100],
            ['category', 'length', 'max'=>8],
            ['contact_person', 'length', 'max'=>32],
            ['contact_no', 'numerical', 'min'=>8, 'integerOnly'=>true],
            ['email', 'email'],
            ['currency, weight_unit', 'length', 'max'=>3],
            ['status', 'length', 'max'=>10],
            ['timezone, language, contact_no', 'length', 'max'=>20],
            //image check is done via ShopForm
            //array('image', 'required','message'=>Sii::t('sii','Please upload logo for this shop')),

            //activate scenario
            ['id, name, status, create_time', 'safe', 'on'=>'activate'],
            ['status', 'ruleActivation','on'=>'activate'],

            // The following rule is used by search().
            ['id, account_id, name, tagline, ,image, contact_person, contact_no, email, slug, timezone, language, currency, weight_unit, category, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Check if shop name is unique
     */
    public function ruleNameUnique($attribute,$params)
    {
        $nameUniqueCheck = function($localeValue,$localeKey=null){
            $criteria = new CDbCriteria();
            if ($localeKey==null)
                $testString = $localeValue;
            else 
                $testString = '"'.$localeKey.'":'.json_encode($localeValue);

            $criteria->compare('name',$testString,true,'AND',true);
            logTrace(__METHOD__.' $nameUniqueCheck '.$testString,$criteria);
            $count = Shop::model()->count($criteria);
            if ($count>=1)                
                $this->addError('name',Sii::t('sii','Shop name with langauge {locale} is already taken.',['{locale}'=>$localeKey]));
        };
        
        $names = json_decode($this->name,true);
        if (is_array($names)){
            if (($this->isNewRecord))
                foreach ($names as $lang => $value)
                    if (!empty($value))
                        $nameUniqueCheck($value,$lang);
                
            if ($this->hasOldAttributes){//update record
                $oldNames = json_decode($this->oldAttributes['name'],true);
                foreach ($names as $lang => $value){
                    if (!empty($value) && strcasecmp($oldNames[$lang],$value)!=0)
                        $nameUniqueCheck($value,$lang);
                }
            }
        }
        else {
            $nameUniqueCheck($this->name);
        }
    }       
    /**
     * Verify shop slug is complying with the subdomain validation rules
     * Here we will be making slug = subdomain 
     */
    public function ruleSlugAsSubdomain($attribute,$params)
    {
        if ($this->prototype()){//only check when is in prototype status - since slug can be edited here
            $domainForm = new ShopDomainForm();
            $domainForm->shop_id = $this->id;
            $domainForm->customDomain = $this->slug;
            if (!$domainForm->validate(['customDomain']))
                $this->addError('slug',$domainForm->getError('customDomain'));
        }
    }        
    /**
     * Activation Check
     * (1) Verify that need at least 1 product shipping must be online
     */
    public function ruleActivation($attribute,$params)
    {
        if ($this->searchProducts(Process::PRODUCT_ONLINE)->itemCount==0) {
            $this->addError('status',Sii::t('sii','At least one product must be online'));
        }
        if ($this->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE)->itemCount==0) {
            $this->addError('status',Sii::t('sii','Shop has no online payment method'));
        }
        if ($this->searchShippings(Process::SHIPPING_ONLINE)->itemCount==0) {
            $this->addError('status',Sii::t('sii','Shop has no online shipping'));
        }
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'products' => [self::HAS_MANY, 'Product', 'shop_id'],
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'address' => [self::HAS_ONE, 'ShopAddress', 'shop_id'],
            'settings' => [self::HAS_ONE, 'ShopSetting', 'shop_id'],
            'themes' => [self::HAS_MANY, 'ShopTheme', 'shop_id'],
        ];
    }

    public function approved() 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'status IN (\''.Process::SHOP_ONLINE.'\',\''.Process::SHOP_OFFLINE.'\')']);
        return $this;
    }  

    public function notOnline() 
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'status IN (\''.Process::SHOP_APPROVED.'\',\''.Process::SHOP_PROTOTYPE.'\',\''.Process::SHOP_OFFLINE.'\')']);
        return $this;
    }  
    
    public  function getNameColumnData() 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/shops/',Image::DEFAULT_IMAGE_SHOP);        
        $list->add($this->name,['image'=>$imageData]);
        return $list;
    }  
    /**
     * TODO: Is this method still valid and in used?
     */
    public function getPaymentMethod($method)
    {
        return PaymentMethod::model()->shopAndMethod($this->id,$method)->find();
    }      
    /**
     * Get the current active theme
     * @param string $status Default to ONLINE; But if pass in null, will pick up the first found(can be offline), 
     * use case will be when shop is using default theme, and theme is not yet saved as 'ONLINE'
     * @return ShopTheme
     */
    public function getThemeModel($status=Process::THEME_ONLINE)
    {
        if (!isset($this->t)){
            $this->_t = ShopTheme::model()->locateShop($this->id)->status($status)->find();//Online means the current theme
        }
        return $this->_t;
    }      
    /**
     * Check if shop has already own this theme
     * @param type $theme
     * @return boolean
     */
    public function hasTheme($theme)
    {
        foreach ($this->themes as $model) {
            if ($model->theme == $theme)
                return true;
        }
        return false;
    }

    public function getTaxes($status=Process::TAX_ONLINE)
    {
        Yii::import("common.modules.taxes.models.Tax");
        return Tax::model()->shopAndStatus($this->id,$status)->findAll();
    }      
    
    public function findByDomain($domain, $active=true)
    {
        $getShop = function($setting) use ($active) {
            if ($active)
                return $setting->shop->online()?$setting->shop:null;
            else
                return $setting->shop;
        };
        
        $setting = ShopSetting::model()->customDomain($domain)->find();  
        if ($setting!=null){
            logTrace(__METHOD__.' Use custom domain',$domain);
            return $getShop($setting);
        }
        else {
            //search into shop own domain
            $setting = ShopSetting::model()->myDomain($domain)->find();  
            if ($setting!=null){
                logTrace(__METHOD__.' Use shop own domain',$domain);
                return $getShop($setting);
            }
            else
                return null;
        }
    }      
    /**
     * @return ShopSetting Get ShopSetting model instance; Create one if not exists
     */
    public function getSettingsModelInstance()
    {
        if ($this->settings===null){
            $settings = new ShopSetting();
            $settings->shop_id = $this->id;
            $settings->insert();
            $this->settings = $settings;
        }
        return $this->settings;
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'name' => Sii::t('sii','Name'),
            'tagline' => Sii::t('sii','Tagline'),
            'slug' => Sii::t('sii','Shop URL'),
            'image' => Sii::t('sii','Logo'),
            'contact_person' => Sii::t('sii','Contact Person'),
            'contact_no' => Sii::t('sii','Contact No'),
            'email' => Sii::t('sii','Email'),
            'timezone' => Sii::t('sii','Time Zone'),
            'language' => Sii::t('sii','Default Language'),
            'currency' => Sii::t('sii','Accepted Currency'),
            'weight_unit' => Sii::t('sii','Weight Unit'),
            'category' => Sii::t('sii','Business Category'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Date'),
            'update_time' => Sii::t('sii','Update Date'),
        ];
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return [
            'name' => Sii::t('sii','Give your shop a name'),
            'slug' => Sii::t('sii','Give your shop url that is of your choice'),
            'contact_person' => Sii::t('sii','The main contact person for any queries'),
            'contact_no' => Sii::t('sii','The contact number for any queries'),
            'email' => Sii::t('sii','The email to receive orders, queries and notifications'),
            'timezone' => Sii::t('sii','The time zone where your shop will operate in'),
            'language' => Sii::t('sii','The default language used in your shop'),
            'currency' => Sii::t('sii','The currency that your shop will accept'),
            'weight_unit' => Sii::t('sii','The weight unit used for shipping fee calculation'),
            'category' => Sii::t('sii','The business that your shop is doing'),
        ];
    }  
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($mineOnly=true)
    {
        $criteria=new CDbCriteria;
        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('tagline',$this->tagline,true);
        $criteria->compare('contact_person',$this->contact_person,true);
        $criteria->compare('contact_no',$this->contact_no,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('timezone',$this->timezone,true);
        $criteria->compare('language',$this->language,true);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('weight_unit',$this->weight_unit,true);
        $criteria->compare('category',$this->category,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        if ($mineOnly)
            $criteria->mergeWith($this->mine()->getDbCriteria());

        return new CActiveDataProvider($this,['criteria'=>$criteria]);
    }
   
    public function hasPaymentMethod($method)
    {
        foreach ($this->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE)->data as $data) {
            if ($method==$data->method)
                return true;
        }
        return false;
    }
     
    public function updatable($user=null)
    {
        return $this->account_id==(isset($user)?$user:user()->getId()) && ($this->operational() || $this->prototype());
    }

    public function deletable($user=null)
    {
        return $this->account_id==(isset($user)?$user:user()->getId()) && $this->operational();
    }         
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('shop/view/'.$this->id);
    }          
    /**
     * Update slug as subdomain; 
     * Here we make both slug equals subdomain
     */
    public function updateSlugAsSubdomain()
    {
        if ($this->slug!=null){
            $subdomain = $this->settingsModelInstance->getValue(ShopSetting::$brand,'customDomain');
            if ($subdomain==null){//only update once when valud is null; if value is not null, skip update
                $brandSettings = $this->settingsModelInstance->getSettings(ShopSetting::$brand);
                $brandSettings['customDomain'] = $this->slug;
                $this->settingsModelInstance->brand = json_encode($brandSettings);
                $this->settingsModelInstance->update(['brand']);
                logTrace(__METHOD__.' Shop subdomain updated to '.$this->slug,$this->settingsModelInstance->attributes);
            }
            else 
                logTrace(__METHOD__.' Skip subdomain update since already defined');
        }
    }
    /**
     * Update shop address, validation has to be done first before calling this method
     */
    public function updateAddress()
    {
        if ($this->address!=null){
            $found = ShopAddress::model()->find('shop_id='.$this->id);
            if ($found==null){//record not found
                $this->address->shop_id = $this->id;
                $this->address->save();
                logTrace(__METHOD__.' shop address created successfully',$this->address->getAttributes());
            }
            else{
                $found->attributes = $this->address->attributes;
                $found->update();
                logTrace(__METHOD__.' shop address updated successfully',$found->getAttributes());
            }
        }
    }
    
    public function hasAddress()
    {
        if ($this->address!=null)
            return $this->address->hasLongAddress();
        return false;
    }
    
    private $_c;//store any found campaign id
    public function getCampaign()
    {
        if (!isset($this->_c)) 
            $this->_c = Yii::app()->serviceManager->getCampaignManager()->checkCampaignSale($this->id);
        return $this->_c;
    }
    public function getCampaigns()
    {
        return Yii::app()->serviceManager->getCampaignManager()->checkCampaignSales($this->id);
    }
    public function hasCampaign()
    {
        return Yii::app()->serviceManager->getCampaignManager()->existsCampaignSale($this->id);
    }
    public function countCampaign()
    {
        return Yii::app()->serviceManager->getCampaignManager()->countCampaignSale($this->id);
    }    
    public function hasPromocodes()
    {
        return Yii::app()->serviceManager->getCampaignManager()->existsCampaignPromocode($this->id);
    }
    public function hasPromocode($code)
    {
        return Yii::app()->serviceManager->getCampaignManager()->existsPromocode($this->id,$code);
    }    
    public function getPromocodeCampaign($code)
    {
        return Yii::app()->serviceManager->getCampaignManager()->checkCampaignPromocode($this->id,$code);
    }
    /**
     * This selects shops with active and non-expired subscription
     * @return $this
     */
    public function withSubscription()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.*';
        $criteria->join = 'INNER JOIN '.Subscription::model()->tableName().' s ON t.id = s.shop_id and t.status = \''.Process::SHOP_ONLINE.'\'';
        $criteria->condition = 's.status=\''.Process::SUBSCRIPTION_ACTIVE.'\' AND \''.Helper::getMySqlDateFormat(time()).'\' between s.start_date AND s.end_date';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Get the shop subscription plan
     * @return type
     */
    public function getSubscription()
    {
        if (!isset($this->sub))
            $this->sub = Subscription::model()->mine($this->account_id)->locateShop($this->id)->active()->notExpired()->find();
        return $this->sub;
    }
    /**
     * Check if shop has subscription at all
     * @return boolean
     */
    public function getHasSubscription()
    {
        return $this->subscription!=null;
    }
    /**
     * @see ShopPrototypeForm for how name is formed, and parsed here
     */
    public function parseName($locale)
    {
        $name = $this->displayLanguageValue('name',$locale);
        if ($this->prototype()){
            $prototypeName = explode(static::NAME_SUFFIX, $name);
            $name = Sii::tl('sii',$prototypeName[0],$locale);//remove suffix $prototypeName[1]
            if (isset($prototypeName[2]) && (int)$prototypeName[2]>1){//counter exists
                $name .= ' '.$prototypeName[2];//suffix counter
            }
        }
        return $name;
    }    
    
    public function getLogo($htmlOptions=['style'=>'height:60px'],$version=Image::VERSION_ORIGINAL)
    {
        return $this->getImageThumbnail($version,$htmlOptions,$this->displayLanguageValue('name',$this->language));
    }
    
    public function gotoUrl($route)
    {
        return url('goto/'.$this->slug.'/'.$route);
    }
    /**
     * Only return media associated to this model only 
     * @return array
     */
    public function prepareTransitionAssociatedMedia($action)
    {
        $mediaArray = [];
        foreach ($this->searchMediaAssociation()->data as $assoc) {
            //since owner only has single image, so the media status will follow owner's
            if (($assoc->media->online() && $this->offline()) || 
                ($assoc->media->offline() && $this->online()))
                $mediaArray[] = $assoc->media;
        }
        return $mediaArray;
    }
    /**
     * Interface method
     * @inheritdoc
     */
    public function getClientAttribte($attribute) 
    {
        if ($this->settings!=null)
            return $this->settings->getValue('chatbot',$attribute);
        else
            return null;
    }
    
    public function getCategoryName()
    {
        if (!isset(Shop::getCategories()[$this->category]))
            return Sii::t('sii','unset');
        else
            return Shop::getCategories()[$this->category];    
    }
    
    public static function getCategories()
    {
        return include Yii::getPathOfAlias('common.modules.shops.data').DIRECTORY_SEPARATOR.'categories.php';
    }
}