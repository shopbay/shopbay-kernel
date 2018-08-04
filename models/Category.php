<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.products.behaviors.CategoryBehavior");
/**
 * This is the model class for table "s_category".
 *
 * The followings are the available columns in table 's_category':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $name
 * @property integer $image
 * @property string $slug
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Category extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Category the static model class
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
        return Sii::t('sii','Category|Categories',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_category';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'label'=>Sii::t('sii','Image'),
                'stateVariable'=>SActiveSession::CATEGORY_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_CATEOGRY,
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
            'childbehavior' => [
                'class'=>'common.components.behaviors.ChildModelBehavior',
                'parentAttribute'=>'category_id',
                'childAttribute'=>'subcategories',
                'childModelClass'=>'CategorySub',
                'childUpdatableAttributes'=>['name','slug'],
                'childCreateScenario'=>'skipSlug',
                'childUpdateScenario'=>'skipSlug',
            ],     
            'categorybehavior' => [
                'class'=>'CategoryBehavior',
            ], 
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=>[
                    'method'=>'getSlugValue',
                ],
                //enable SkipScenario to bypass auto sluggable behavior when slug value is presented
                'skipScenario'=>'skipSlug',
            ],
            'sitemap' => [
                'class'=>'common.components.behaviors.SitemapBehavior',
                'sort'=>'update_time DESC',
            ],
        ];
    }
    /**
     * Validation rules for model attributes
     * 
     * Note: model attribute (table column) wil have own validation rules following underlying table definition
     * Some attribute that are to have support of multiple locales have to actual attribute level rules specified at LanguageForm level
     * 
     * @see CategoryForm
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
            ['name', 'ruleExists'],
            //slug validation
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
            ['slug', 'ruleSlugWhitelist','on'=>$this->getCreateScenario()],
            
            ['image', 'safe'],
            
            //validate subcategories 
            ['id', 'ruleSubcategories'],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'], 

            ['id, account_id, shop_id, name, image, slug, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if attribute exisits
     */
    public function ruleExists($attribute,$params)
    {
        $checkExists = function ($shop,$value) use ($attribute) {
            if ($this->findModelByLanguageName($value,['shop_id'=>$shop])!=false)
                $this->addError($attribute,Sii::t('sii','Category "{name}" is already taken.',['{name}'=>$value]));
        };        
        
        $existingCategory = Category::model()->findByPk($this->id);
        //for existing category trying to modify name
        if ($existingCategory!=null){
            if (strlen($this->$attribute)==0){
                $this->addError($attribute,Sii::t('sii','Category name cannot be empty.'));
            }
            elseif (strpos($existingCategory->name,json_encode($this->$attribute))===false){
                logTrace(__METHOD__.' trying to modify existing name...');
                $checkExists($this->shop_id,$this->$attribute);
            }
        }
        elseif (!empty($this->$attribute)){//for new category 
            $checkExists($this->shop_id,$this->$attribute);
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
            if (Category::model()->exists($criteria))
                $this->addError('slug',Sii::t('sii','Category URL "{slug}" is already taken.',['{slug}'=>$this->slug]));
        }
    }     
    /**
     * Validate subcategories 
     */
    public function ruleSubcategories($attribute,$params)
    {
        $this->ruleChilds('id');//use id field as proxy
    }
    /**
     * Validate if category has any associations
     * (1) Category has product
     * (2) Subcategory has product
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->hasProducts())
            $this->addError('id',Sii::t('sii','"{object_name}" has {association_object}. Please detach {association_object} if you wish to delete this {object_type}.',[
                '{object_name}'=>$this->displayLanguageValue('name'),
                '{association_object}'=>strtolower(Product::model()->displayName(Helper::PLURAL)),
                '{object_type}'=> strtolower($this->displayName()),
            ]));
            
        if ($this->hasSubcategoryProducts()){
            $this->addError('id',Sii::t('sii','"{object_name}"\'s subcategory has {association_object}. Please detach {association_object} if you wish to delete this {object_type}.',[
                '{object_name}'=>$this->displayLanguageValue('name'),
                '{association_object}'=>strtolower(Product::model()->displayName(Helper::PLURAL)),
                '{object_type}'=> strtolower($this->displayName()),
            ]));
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
            'subcategories' => [self::HAS_MANY, 'CategorySub', 'category_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'name' => Sii::t('sii','Name'),
            'image' => Sii::t('sii','Image'),
            'slug' => Sii::t('sii','Category URL'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }

    public function getItemColumnData($locale=null) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/categories/',Image::DEFAULT_IMAGE_CATEOGRY);
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
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        //$criteria->compare('account_id',$this->account_id);
        //$criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('name',$this->name,true);
        //$criteria->compare('image',$this->image);
        //$criteria->compare('slug',$this->slug,true);
        //$criteria->compare('update_time',$this->update_time);
        
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $this->create_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider($this,[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('product/category/view/'.$this->id);
    } 
    
    public function getProducts()
    {
        $products = [];
        $criteria=new CDbCriteria;
        $criteria->select = 'product_id';
        $criteria->distinct = true;
        $criteria->group = 'product_id';
        $criteria->addColumnCondition(['category_id'=>$this->id]);
        foreach (ProductCategory::model()->findAll($criteria) as $cateogory){
            $products[] = $cateogory->product;
        }
        return $products;
    }
    /**
     * @return booolean check if has products
     */
    public function hasProducts()
    {
        return $this->getProductCount()>0;
    }
    /**
     * @return integer products count
     */
    public function getProductCount()
    {
        $criteria=new CDbCriteria;
        $criteria->distinct = true;
        $criteria->addColumnCondition(['category_id'=>$this->id]);
        return ProductCategory::model()->count($criteria);
    }
    /**
     * @return booolean check if has subcategories
     */
    public function hasSubcategories()
    {
        return $this->getSubcategoryCount()>0;
    }
    /**
     * @return booolean check if has products under its subcategory
     */
    public function hasSubcategoryProducts()
    {
        foreach ($this->subcategories as $subcategory) {
            if ($subcategory->hasProducts()){
                $found = true;
                break;
            }
        }
        return isset($found)?$found:false;
    }
    /**
     * @return integer subcategories count
     */
    public function getSubcategoryCount()
    {
        return count($this->subcategories);
    }    
    /**
     * @return array subcategories array
     */
    public function getSubcategoriesToArray($locale,$showUrl=false)
    {
        $list = new CList();
        foreach ($this->subcategories as $data) {
            $list->add($data->displayLanguageValue('name',$locale).($showUrl?': '.$data->url:''));
        }
        return $list->toArray();
    }     
    
    public function searchSubcategories($extraCriteria=null,$pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 't.category_id='.$this->id;

        if (isset($extraCriteria))
            $criteria->mergeWith($extraCriteria);

        return new CActiveDataProvider(CategorySub::model(),[
            'criteria'=>$criteria,
            'pagination'=>[
                'pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page'),
            ],
        ]);
    } 
    
    public function toString($locale)
    {
        return $this->displayLanguageValue('name',$locale);
    }
    
}