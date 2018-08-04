<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.components.behaviors.*");
/**
 * This is the model class for table "s_tax".
 *
 * The followings are the available columns in table 's_tax':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id 
 * @property string $name
 * @property string $rate
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Tax extends Transitionable
{
    const DEMO_TAX = -1;
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
        return Sii::t('sii','Tax|Taxes',array($mode));
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_tax';
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
            'account' => array(
              'class'=>'common.components.behaviors.AccountBehavior',
            ),            
            'merchant' => array(
              'class'=>'common.components.behaviors.MerchantBehavior',
            ),     
            'locale' => array(
              'class'=>'common.components.behaviors.LocaleBehavior',
            ),              
            'transition' => array(
              'class'=>'common.components.behaviors.TransitionBehavior',
              'activeStatus'=>Process::TAX_ONLINE,
              'inactiveStatus'=>Process::TAX_OFFLINE,
            ),
            'workflow' => array(
              'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ),              
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                //'iconUrlSource'=>'shop',
                'buttonIcon'=>true,
            ),
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),             
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, name, rate, status', 'required'),
            array('account_id, shop_id', 'numerical', 'integerOnly'=>true),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            array('name', 'length', 'max'=>1000),
            array('rate', 'length', 'max'=>10),
            array('status', 'length', 'max'=>10),                    
            // validate field 'type' to make sure correct rate is entered
            array('rate', 'type', 'type'=>'float','allowEmpty'=>false),

            //on delete scenario, id field here as dummy
            array('id', 'ruleAssociations','params'=>array(),'on'=>'delete'),

            //on deactivate scenario, id field here as dummy
            array('status', 'ruleDeactivation','params'=>array(),'on'=>'deactivate'),

            // The following rule is used by search().
            array('id, account_id, shop_id, name, rate, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * Deactivation Check
     * (1) Verify that need shop must be offline for tax deactivation
     */
    public function ruleDeactivation($attribute,$params)
    {
        //no specific rule here
    }        
    /**
     * Validate if tax has any associations
     * (1) Payment method is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',array('{object}'=>$this->name)));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'name' => Sii::t('sii','Tax Name'),
            'rate' => Sii::t('sii','Tax Rate'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    public function shopAndStatus($shop,$status) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('shop_id'=>$shop));
        $criteria->addColumnCondition(array('status'=>$status));
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

        //$criteria->compare('id',$this->id);
        //$criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('name',$this->name,true);
        //$criteria->compare('rate',$this->rate,true);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider(
                                        'Tax',
                                        array(
                                            'criteria'=>$criteria,
                                            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                        ));

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Get tax payable
     * @param decimal $amount
     * @return real
     */
    public function getPayable($amount=0,$format=false)
    {
        $payable = $this->rate * $amount;
        if ($format)
            return $this->formatCurrency($payable);
        else
            return $payable;
    }  
    /**
     * Get tax text
     * @return string
     */
    public function getTaxText($locale=null)
    {
        return $this->displayLanguageValue('name',$locale).' '.$this->formatPercentage($this->rate);
    }  
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('tax/view/'.$this->id);
    } 

}