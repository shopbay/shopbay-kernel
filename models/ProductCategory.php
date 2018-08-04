<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.TimestampBehavior");
/**
 * This is the model class for table "s_product_category".
 *
 * The followings are the available columns in table 's_product_category':
 * @property integer $id
 * @property integer $product_id
 * @property integer $category_id
 * @property integer $subcategory_id
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Product $product
 *
 * @author kwlok
 */
class ProductCategory extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ProductShipping the static model class
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
        return Sii::t('sii','Category|Categories',array($mode));
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_product_category';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ),
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('product_id, category_id', 'required'),
            array('product_id, category_id, subcategory_id', 'numerical', 'integerOnly'=>true),
            
            array('id, product_id, category_id, subcategory_id, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
            'category' => array(self::BELONGS_TO, 'Category', 'category_id'),
            'subcategory' => array(self::BELONGS_TO, 'CategorySub', 'subcategory_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'product_id' => Sii::t('sii','Product'),
            'category_id' => Sii::t('sii','Category'),
            'subcategory_id' => Sii::t('sii','Subcategory'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    /**
     * Category finder method
     * @param $category_id
     * @return CComponent
     */
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
        $criteria->compare('product_id',$this->product_id);
        $criteria->compare('category_id',$this->category_id);
        $criteria->compare('subcategory_id',$this->subcategory_id);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }

    public function getHasSubcategory()
    {
        return $this->subcategory_id!=null;
    }
    /**
     * @see CategorySub::toKey()
     * @return string
     */
    public function toKey()
    {
        $key = $this->category_id;
        if (isset($this->subcategory_id))
            $key .= '.'.$this->subcategory_id;
        return $key;
    }
    
    public function toString($locale)
    {
        if ($this->hasSubcategory)
            return $this->subcategory->toString($locale);
        else
            return $this->category->toString($locale);
    }
}