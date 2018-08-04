<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.TimestampBehavior");
/**
 * This is the model class for table "s_product_shipping".
 *
 * The followings are the available columns in table 's_product_shipping':
 * @property integer $id
 * @property integer $product_id
 * @property integer $shipping_id
 * @property string $surcharge
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Product $product
 *
 * @author kwlok
 */
class ProductShipping extends CActiveRecord
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
        return Sii::t('sii','Shipping|Shippings',array($mode));
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_product_shipping';
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
            array('product_id, shipping_id', 'required'),
            array('id, product_id, shipping_id', 'numerical', 'integerOnly'=>true),
            array('surcharge', 'length', 'max'=>10),
            array('surcharge', 'type', 'type'=>'float'),
            array('surcharge', 'default', 'setOnEmpty'=>true, 'value' => null),
            array('surcharge', 'safe'),
            
            array('id, product_id, shipping_id, surcharge, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
            'shipping' => array(self::BELONGS_TO, 'Shipping', 'shipping_id'),
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
            'shipping_id' => Sii::t('sii','Shipping'),
            'surcharge' => Sii::t('sii','Surcharge'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
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
        $criteria->compare('shipping_id',$this->shipping_id);
        $criteria->compare('surcharge',$this->surcharge,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }

    public function hasSurcharge()
    {
        return ($this->surcharge!=null && $this->surcharge>0);
    }  
    
    public function getSurcharge()
    {
        return $this->surcharge==null?0:$this->surcharge;
    }

}