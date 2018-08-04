<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.products.behaviors.CategorySubBehavior");
/**
 * This is the model class for table "s_category_sub".
 * It represents the sub category of model class Category.
 * 
 * The followings are the available columns in table 's_category_sub':
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $slug
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class CategorySub extends SActiveRecord
{
    const KEY_SEPARATOR = '.';
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return CategorySub the static model class
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
        return Sii::t('sii','Subcategory|Subcategories',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_category_sub';
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
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],       
            'categorybehavior' => [
                'class'=>'CategorySubBehavior',
            ], 
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=> [
                    'method'=>'getSlugValue',
                ],
                //enable SkipScenario to bypass auto sluggable behavior when slug value is presented
                'skipScenario'=>'skipSlug',
            ],
        ];
    }
    /**
     * Validation rules for model attributes
     * 
     * Note: model attribute (table column) wil have own validation rules following underlying table definition
     * Some attribute that are to have support of multiple locales have to actual attribute level rules specified at LanguageForm level
     * 
     * @see CategorySubForm
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['category_id, name', 'required'],
            ['id, category_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 1000 chars.
            ['name', 'length', 'max'=>2000],
            ['name', 'ruleExists'],
            //slug validation
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
            ['slug', 'ruleSlugWhitelist','on'=>$this->getCreateScenario()],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'], 
            
            ['id, category_id, name, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if attribute exisits
     */
    public function ruleExists($attribute,$params)
    {
        $checkExists = function ($category,$value) use ($attribute) {
            if ($this->findModelByLanguageName($value,['category_id'=>$category])!=false)
                $this->addError($attribute,Sii::t('sii','Subcategory "{name}" is already taken.',['{name}'=>$value]));
        };        
        
        $existingSubcategory = CategorySub::model()->findByPk($this->id);
        //for existing subcategory trying to modify name
        if ($existingSubcategory!=null){
            logTrace(__METHOD__.' $existingSubcategory found');
            if (strlen($this->$attribute)==0){
                $this->addError($attribute,Sii::t('sii','Subcategory name cannot be empty.'));
            }
            elseif (strpos($existingSubcategory->name,json_encode($this->$attribute))===false){
                logTrace(__METHOD__.' trying to modify existing name...');
                $checkExists($this->category_id,$this->$attribute);
            }
        }
        elseif (!empty($this->$attribute) && $existingSubcategory===null){//for new subcategory 
            logTrace(__METHOD__.' $existingSubcategory not found');
            $checkExists($this->category_id,$this->$attribute);
        }
    }     
    /**
     * Verify subcategory url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        $existingSubcategory = CategorySub::model()->findByPk($this->id);
        //only verify slug value of new subcategory
        if (!empty($this->slug) && $existingSubcategory===null){
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(['category_id'=>$this->category_id]);
            foreach (CategorySub::model()->findAll($criteria) as $subcategory) {
                if ($subcategory->slug==$this->slug){
                    $this->addError('slug',Sii::t('sii','Subcategory URL "{slug}" is already taken.',['{slug}'=>$this->slug]));
                    break;
                }
            }
        }
    }     
    /**
     * Validate if category has any associations
     * (1) Subcategory has product
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->hasProducts()){
            $this->addError('id',Sii::t('sii','"{object_name}" has {association_object}. Please detach {association_object} if you wish to delete this {object_type}.',['{object_name}'=>$this->displayLanguageValue('name'),
                    '{association_object}'=>strtolower(Product::model()->displayName(Helper::PLURAL)),
                    '{object_type}'=> strtolower($this->displayName()),
                ]
            ));
        }
    }    
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'category' => [self::BELONGS_TO, 'Category', 'category_id'],
        ];
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'category_id' => Sii::t('sii','Category'),
            'name' => Sii::t('sii','Name'),
            'slug' => Sii::t('sii','Subcategory URL'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }

    public function locateCategory($category_id)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('category_id='.$category_id);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }     
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('category_id',$this->category_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('slug',$this->slug,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
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
        $criteria->distinct = true;
        $criteria->addColumnCondition(['category_id'=>$this->category_id]);
        $criteria->addColumnCondition(['subcategory_id'=>$this->id]);
        foreach (ProductCategory::model()->findAll($criteria) as $subcateogory){
            $products[] = $subcateogory->product;
        }
        return $products;
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
        $criteria=new CDbCriteria;
        $criteria->addColumnCondition(['category_id'=>$this->category_id]);
        $criteria->addColumnCondition(['subcategory_id'=>$this->id]);
        return ProductCategory::model()->count($criteria);
    }
    
    public function toKey()
    {
        return $this->category_id.self::KEY_SEPARATOR.$this->id;
    }
    
    public function hasKey($key)
    {
        try {
            $key = $this->parseKey($key);
            return isset($key[1]);//key [1] is subcategory key
        } catch (CException $ex) {
            return false;
        }
    } 
    
    public function parseKey($key)
    {
        if (!is_numeric($key)){
            //category keys are numeric 
            logError(__METHOD__.' category key is not a valid (numeric) key');
            throw new CException(Sii::t('sii','Invalid category'));
        }
        return explode(self::KEY_SEPARATOR, $key);
    }    
    
    public function toString($locale,$separator=Helper::SINGLE_ARROW_CHAR)
    {
        return $this->category->toString($locale).' '.$separator.' '.$this->displayLanguageValue('name',$locale);
    }

}