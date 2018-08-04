<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.media.behaviors.SingleMediaBehavior");
/**
 * This is the model class for table "s_brand".
 *
 * The followings are the available columns in table 's_brand':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $name 
 * @property string $description
 * @property string $slug
 * @property integer $image
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Shop $shop
 * 
 * @author kwlok
 */
class Brand extends SActiveRecord 
{ 
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Brand the static model class
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
        return Sii::t('sii','Brand|Brands',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_brand';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'label'=>Sii::t('sii','Logo'),
                'stateVariable'=>SActiveSession::BRAND_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_BRAND,
            ],
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ],  
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=> [
                    'enable'=>true,
                ],
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],                      
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],
            'brandbehavior' => [
                'class'=>'common.modules.brands.behaviors.BrandBehavior',
            ],
            'sitemap' => [
                'class'=>'common.components.behaviors.SitemapBehavior',
                'sort'=>'update_time DESC',
            ],
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=> [
                    'method'=>'getSlugValue',
                ],
                //enable SkipScenario to bypass auto sluggable behavior when slug value is presented
                'skipScenario'=>'create',//since the implementation only allow create to define slug
            ],
        ];
    }
    /**
     * Validation rules for model attributes
     * 
     * Note: model attribute (table column) wil have own validation rules following underlying table definition
     * Some attribute that are to have support of multiple locales have to actual attribute level rules specified at LanguageForm level
     * 
     * @see BrandForm
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, name', 'required'],
            ['account_id, shop_id, image', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            ['name', 'length', 'max'=>2000],
            ['image', 'safe'],
            //This column stored json encoded description in different languages.
            ['description', 'safe'],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            //slug validation
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
            ['slug', 'ruleSlugWhitelist','on'=>$this->getCreateScenario()],

            ['id, account_id, shop_id, name, image, description, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if brand has any associations
     * (1) Product has brand
     */
    public function ruleAssociations($attribute,$params)
    {
        if (Product::model()->exists('brand_id='.$this->id)){
            $this->addError('id',Sii::t('sii','"{object_name}" has associations with {association_object}. Please clear the association if you wish to delete this {object_type}.',[
                    '{object_name}'=>$this->displayLanguageValue('name'),
                    '{association_object}'=>strtolower(Product::model()->displayName(Helper::PLURAL)),
                    '{object_type}'=> strtolower($this->displayName()),
                ]
            ));
        }
    }
    /**
     * Verify category url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        if (!empty($this->slug)){
            logTrace(__METHOD__,$this->slug);
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(['shop_id'=>$this->shop_id,'slug'=>$this->slug]);
            if (Brand::model()->exists($criteria))
                $this->addError('slug',Sii::t('sii','Brand URL "{slug}" is already taken.',['{slug}'=>$this->slug]));
        }
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'products' => [self::HAS_MANY, 'Product', 'brand_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'name' => Sii::t('sii','Name'),
            'image' => Sii::t('sii','Logo'),
            'description' => Sii::t('sii','Description'),
            'slug' => Sii::t('sii','Brand URL'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;
        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider($this,[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);
    }    
    
    public function getItemColumnData($locale=null) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/brands/',Image::DEFAULT_IMAGE_BRAND);
        $list->add($this->displayLanguageValue('name',$locale),[
            'image'=>$imageData,
            'list_objects'=>$this->searchProductImages()->rawData,
        ]);
        return $list;
    }   
    
    protected function searchProductImages() 
    {
        $list = new CList();
        foreach($this->products as $product){
            $list->add(CHtml::link($product->getImageThumbnail(Image::VERSION_SMALL,['class'=>'img','title'=>$product->displayLanguageValue('name')]),$product->viewUrl));
        }
        return new CArrayDataProvider($list->toArray());
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('brand/view/'.$this->id);
    }    
    /**
     * Return product count
     */
    public function hasProducts()
    {
        return $this->getProductCount()>0;
    }
    /**
     * Return product count
     */
    public function getProductCount()
    {
        return count($this->products);
    }    

}