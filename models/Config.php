<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_config".
 *
 * The followings are the available columns in table 's_config':
 * @property integer $id
 * @property string $category
 * @property string $name
 * @property string $value
 *
 * @author kwlok
 */
class Config extends SActiveRecord
{
    const ON  = 'on';
    const OFF = 'off';
    const SYSTEM   = 'system';
    const BUSINESS = 'business';
    /**
     * Returns the static model of the specified AR class.
     * @return Config the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_config';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>array(
                    'enable'=>true,
                ),
            ),            
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id, category, name, value', 'required'),
            array('category', 'length', 'max'=>20),
            array('name', 'length', 'max'=>50),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, category, name, value', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'category' => Sii::t('sii','Category'),
            'name' => Sii::t('sii','Name'),
            'value' => Sii::t('sii','Value'),
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
        $criteria->compare('category',$this->category,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('value',$this->value,true);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }

    public function displayName()
    {
        return $this->name;
    }

    public function displayValue()
    {
        return $this->value;
    }
    
    public function getViewUrl() 
    {
        return url('configs/default/view/id/'.$this->id);
    }
    
    public static function getSystemSetting($param)
    {
        $value=Yii::app()->commonCache->get(self::cacheKey(Config::SYSTEM,$param));
        if($value===false)
            $value = Config::setCache(Config::SYSTEM, $param);
        return $value;
    }

    public static function getBusinessSetting($param)
    {
        $value=Yii::app()->commonCache->get(self::cacheKey(Config::BUSINESS,$param));
        if($value===false)
            $value = Config::setCache(Config::BUSINESS, $param);
        return $value;
    }

    public static function refreshSetting($category,$param)
    {
        Yii::app()->commonCache->delete(self::cacheKey($category,$param));
        Config::setCache($category, $param);
    }
    /**
     * Regenerate $value because it is not found in cache
     * and save it in cache for later use
     * 
     * @param type $category
     * @param type $param
     * @return type
     */
    private static function setCache($category, $param)
    {
        $criteria=new CDbCriteria;
        $criteria->select='value';
        $criteria->condition='category=\''.$category.'\' AND name=\''.$param.'\'';
        $value = Config::model()->find($criteria)->value;
        Yii::app()->commonCache->set(self::cacheKey($category,$param) , $value);
        return $value; 
    }
    
    private static function cacheKey($category,$param)
    {
        return $category.'_'.$param;
    }

}