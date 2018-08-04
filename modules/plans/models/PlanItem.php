<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.services.workflow.models.Administerable");
/**
 * This is the model class for table "s_plan_item".
 *
 * The followings are the available columns in table 's_plan_item':
 * @property integer $id
 * @property integer $plan_id
 * @property string $name
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class PlanItem extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
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
        return Sii::t('sii','Plan Item|Plan Items',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_plan_item';
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
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],    
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['plan_id, name', 'required'],
            ['plan_id', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>500],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'plan' => [self::BELONGS_TO, 'Plan', 'plan_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'plan_id' => Sii::t('sii','Plan'),
            'name' => Sii::t('sii','Item Name'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * @return CActiveRecord 
     */
    public function locateItem($planId,$itemName) 
    {
        $condition = 'plan_id = '.$planId.' AND name LIKE \'%'.$itemName.'%\'';        
        $this->getDbCriteria()->mergeWith(['condition'=>$condition]);
        return $this;
    }   

    public function getViewUrl() 
    {
        return url('plan/item/'.$this->id);
    }
    /**
     * Return account profile
     * @return type
     */
    public function getAccountProfile()
    {
        return $this->plan->account->profile;
    }    
    /**
     * Check if plan can be subscribed
     */
    public function getIsSubscribable()
    {
        return $this->plan->isApproved;
    }
}