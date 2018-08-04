<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_metric".
 *
 * The followings are the available columns in table 's_metric':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $fact
 * @property integer $value
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Metric extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Metric the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Custom constructor
     */
    public function __construct($obj_type,$obj_id=null,$fact=null)
    {
        if($obj_type===null) //surrogate to $scenario; internally used by populateRecord() and model()
            parent::__construct(null);
	else {
            parent::__construct('insert');
	    $this->obj_type = $obj_type;
            $this->obj_id = $obj_id;
            $this->fact = $fact;
            logTrace(__METHOD__, $this->getAttributes());
        }
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_metric';
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
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['obj_type, obj_id, fact, value', 'required'],
            ['obj_id', 'length', 'max'=>12],
            ['obj_type', 'length', 'max'=>20],
            ['fact', 'length', 'max'=>30],
            ['value', 'length', 'max'=>50],
            
            ['id, obj_type, obj_id, fact, value', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'obj_type' => Sii::t('sii','Obj Type'),
            'obj_id' => Sii::t('sii','Obj'),
            'fact' => Sii::t('sii','Fact'),
            'value' => Sii::t('sii','Value'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    /**
     * Finder method for generic metrics
     * @param type $obj_type
     * @param type $obj_id
     * @param type $fact (Optional) Metric fact name; If null, will return all metrics for $account_id
     * @return \Metric
     */
    public function genericMetric($obj_type,$obj_id,$fact=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array(
            'obj_type'=>$obj_type,
            'obj_id'=>$obj_id,
        ));
        if (isset($fact)){
            $criteria->addColumnCondition(array(
                'fact'=>$fact,
            ));
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Finder method for account metrics
     * @param type $account_id
     * @param type $fact (Optional) Metric fact name; If null, will return all metrics for $account_id
     * @return \Metric
     */
    public function accountMetric($account_id,$fact=null) 
    {
        $accountClass = Account::getAccountClass($account_id);
        return $this->genericMetric(SActiveRecord::restoreTablename($accountClass), $account_id, $fact);
    }  
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;
        $criteria->compare('id',$this->id);
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('fact',$this->fact,true);
        $criteria->compare('value',$this->value,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    /*
     * List of Metirc facts prefix
     */
    const PREFIX_UNIT_PO       = 'unit_po_';//purchase order
    const PREFIX_UNIT_SO       = 'unit_so_';//shipping order
    const PREFIX_UNIT_ITEM     = 'unit_item_';
    /*
     * List of Metirc facts
     */
    const UNIT_ITEM_PURCHASE   = 'unit_item_purchase';
    const UNIT_ITEM_PICK       = 'unit_item_pick';
    const UNIT_ITEM_PACK       = 'unit_item_pack';
    const UNIT_ITEM_SHIP       = 'unit_item_ship';
    const UNIT_PO_PURCHASE     = 'unit_po_purchase';//purchase order
    const TOTAL_ORDER_SPENT    = 'total_order_spent';
    const COUNT_LIKE           = 'count_like';
    const COUNT_COMMENT        = 'count_comment';
    public function getFacts()
    {
        return array(
            self::TOTAL_ORDER_SPENT => Sii::t('sii','Total Order Spent'),
            self::COUNT_LIKE => Sii::t('sii','Likes Count'),
            self::COUNT_COMMENT => Sii::t('sii','Comments Count'),
            self::UNIT_ITEM_PURCHASE => Sii::t('sii','Items Purchased Unit'),
            self::UNIT_ITEM_PICK => Sii::t('sii','Item Picked Unit'),
            self::UNIT_ITEM_PACK => Sii::t('sii','Item Packed Unit'),
            self::UNIT_ITEM_SHIP => Sii::t('sii','Item Shipped Unit'),
            self::UNIT_PO_PURCHASE => Sii::t('sii','Purchase Order Unit'),
            //other metric facts yet to be supported...
        );
    }        
    public function getFactDesc()
    {
        $metrics = $this->getFacts();
        return isset($metrics[$this->fact])?$metrics[$this->fact]:Sii::t('sii','not set');
    }
    
}
