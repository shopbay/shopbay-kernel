<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_transition".
 *
 * The followings are the available columns in table 's_transition':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $process_from
 * @property string $process_to
 * @property string $action
 * @property string $decision
 * @property string $condition1
 * @property string $condition2
 * @property string $transition_by
 * @property integer $transition_time
 *
 * @author kwlok
 */
class Transition extends SActiveRecord
{
    /**
     * Initializes this model.
     * This method is invoked when an AR instance is newly created and has
     * its {@link scenario} set.
     * You may override this method to provide code that is needed to initialize the model (e.g. setting
     * initial property values.)
     */
    public function init()
    {
        //nothing
    }        
    /**
     * Returns the static model of the specified AR class.
     * @return Lifecycle the static model class
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
        return Sii::t('sii','Transition|Transitions',[$mode]);
    }     
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_transition';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
                'createAttribute'=>'transition_time',
                'updateEnable'=>false,
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'transition_by',                
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'transition_by',                
            ],
        ];
    }       
    /**
     * Scenario with Condition1 required only
     */
    const SCENARIO_C1 = 'c1';
    /**
     * Scenario with both Condition1 and Condition2 required
     */
    const SCENARIO_C1_C2 = 'c1-c2';
    /**
     * Scenario with Condition1 required and has decision
     */
    const SCENARIO_C1_D = 'c1-decision';
    /**
     * Scenario with both Condition1 and Condition2 required, and has decision
     */
    const SCENARIO_C1_C2_D = 'c1-c2-decision';
    /**
     * Scenario is set at views/task/workflow.php based on Begin Process (process_from)
     * 
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            //default scenario "SCENARIO_C1"
            ['decision', 'default', 'setOnEmpty'=>true, 'value' => null],
            ['action, condition1','required'],
            ['condition1, condition2', 'length', 'max'=>20000],//put a limit
            //Additionally, for Scenario "SCENARIO_C1_D"
            ['decision', 'required','on'=>self::SCENARIO_C1_D],
            //Additionally, for Scenario "SCENARIO_C1_C2"
            ['condition2', 'required','on'=>self::SCENARIO_C1_C2],
            //Additionally, for Scenario "SCENARIO_C1_C2_D"
            ['condition2, decision', 'required','on'=>self::SCENARIO_C1_C2_D],

            //for action pay / refund (paying unpaid order and deferred payment
            //Condition1 is now 'Payment Amount' 
            //Condition2 is the supporting information
            ['condition1', 'length', 'max'=>10,'on'=>WorkflowManager::ACTION_PAY],
            ['condition1', 'numerical', 'min'=>0.01,'on'=>WorkflowManager::ACTION_PAY],
            ['condition2', 'required','on'=>WorkflowManager::ACTION_PAY],
            ['condition2', 'length', 'max'=>1000,'on'=>WorkflowManager::ACTION_PAY],
            
            //for action ship or 1-step item processing
            //Condition1 is now 'Carrier and Tracking No' of shipment
            //Condition2 is tracking url for Ship action;
            //array('condition2', 'required','on'=>WorkflowManager::ACTION_SHIP),//comment to make it optional
            ['condition2', 'ruleShip','on'=>WorkflowManager::ACTION_SHIP],

            //for decision to accept item return 
            //Condition2 is the inventory update method
            ['condition2', 'required','on'=>WorkflowManager::DECISION_RETURN],//comment to make it optional
            ['condition2', 'ruleReturn','on'=>WorkflowManager::DECISION_RETURN],
            
            //for transition for Interrupt
            ['condition1', 'length', 'max'=>2000,'on'=>Process::INTERRUPT],
            ['condition2', 'length', 'max'=>200,'on'=>Process::INTERRUPT],

            ['obj_id, process_from, action, decision, condition1, condition2', 'safe'],
            
            ['id, obj_type, obj_id, process_from, process_to, action, decision, condition1, condition2, transition_by, transition_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Extra specific condition2 validation for Shipping
     */
    public function ruleShip($attribute,$params)
    {
        if ($this->$attribute!=null) {
            $validator = new CUrlValidator();
            if (!$validator->validateValue($this->$attribute))
                $this->addError($attribute, Sii::t('sii','{attribute} is not a valid tracking url.',['{attribute}'=>$this->_getLabel('Condition2')]));
        }
    }
    /**
     * Extra specific condition2 validation for Return
     */
    public function ruleReturn($attribute,$params)
    {
        if (!in_array($this->$attribute,array_keys(Inventory::getHandlingMethods()))) {
        logTrace(__METHOD__.' $this->$attribute',$this->$attribute);
            $this->addError($attribute, Sii::t('sii','{attribute} method is not supported.',['{attribute}'=>$this->_getLabel('Condition2')]));
        }
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'obj_type' => Sii::t('sii','Object Type'),
            'obj_id' => Sii::t('sii','Object'),
            'process_from' => Sii::t('sii','From'),
            'process_to' => Sii::t('sii','To'),
            'action' => Sii::t('sii','Action'),
            'decision' => Sii::t('sii','Decision'),
            'condition1' => $this->_getLabel('Condition1'),
            'condition2' => $this->_getLabel('Condition2'),
            'transition_by' => Sii::t('sii','Transition By'),
            'transition_time' => Sii::t('sii','Process Time'),
        ];
    }

    private function _getLabel($which)
    {
        if ($this->getScenario()==self::SCENARIO_C1 ||
            $this->getScenario()==self::SCENARIO_C1_D ||
            $this->getScenario()==self::SCENARIO_C1_C2 ||
            $this->getScenario()==self::SCENARIO_C1_C2_D ||
            $this->getScenario()==WorkflowManager::DECISION_RETURN ||
            $this->getScenario()==WorkflowManager::ACTION_PAY ||
            $this->getScenario()==WorkflowManager::ACTION_SHIP ||
            $this->getScenario()==Process::INTERRUPT) {
            return $this->getObject()->{'get'.$which.'Label'}($this->decision);
        }
        else
            return $which;
    }
    public function getObject()
    {
        $type = SActiveRecord::resolveTablename($this->obj_type);
        if ($type==null)
            throw new CException(Sii::t('sii','Transition object type not found'));
        $model = $type::model()->find('id='.$this->obj_id);
        if ($model===null)
            throw new CException(Sii::t('sii','Transition model not found'));
        return $model;
    }
    /**
     * Set Transition conditions
     * @param mixed $conditions string or array
     */
    public function setConditions($conditions) 
    {
        if (!empty($conditions)){
            if (is_array($conditions)){
                if (isset($conditions[self::MESSAGE]))
                    $this->setMessage($conditions[self::MESSAGE]);
                if (isset($conditions[self::PAYLOAD]))
                    $this->setPayload($conditions[self::PAYLOAD]);
                //below is to directly set condition1 and condition2
                //if not using predefined data structure MESSAGE and PAYLOAD
                if (isset($conditions['condition1']))
                    $this->condition1 = $conditions['condition1'];
                if (isset($conditions['condition2']))
                    $this->condition2 = $conditions['condition2'];
            }
            else 
                $this->setMessage($this->condition1);
        }
    } 
    /**
     * Below constant for custom transition data strucutre to be stored in condition1 or conditon2
     * 
     * @see MESSAGE is more for use in displaying process history
     * @see PAYLOAD is more for use in ServiceManager and workflow behaviour 
     */
    const MESSAGE = 'message';
    const PAYLOAD = 'payload';
    /**
     * Set message data structure
     * @param mixed $message
     */
    public function setMessage($message) 
    {
        $this->condition1 = json_encode([self::MESSAGE=>$message]);
    }
    /**
     * Get message data structure
     * @return mixed 
     */
    public function getMessage($keepObject=false) 
    {
        $data = json_decode($this->condition1);
        if (is_object($data)){//MESSAGE object exists
            $message = $data->{self::MESSAGE};
            if (is_object($message))//embedded object inside MESSAGE
                if ($keepObject)
                    return $message;
                else
                    return Helper::htmlSmartKeyValues($message);
            else {
                return $message;
            }
        }
        else 
            return $this->condition1;
    }
    /**
     * Set payload data structure
     * @param mixed $payload
     */
    public function setPayload($payload) 
    {
        $this->condition2 = json_encode(array(self::PAYLOAD=>$payload));
    }
    /**
     * Get payload data structure
     * @return mixed 
     */
    public function getPayload() 
    {
        return json_decode($this->condition2)->{self::PAYLOAD};
    }
    /**
     * Check if transition is viewable;
     * Merchant always return true;
     * @param type $user
     */
    public function isViewable($user,$strict=false)
    {
        if ($user->currentRole==Role::MERCHANT && !$strict)
            return true;
        else
            return $this->transition_by==$user->getId();
    }
    /**
     * Set object model to Rollback mode
     * Field action = ACTION_ROLLBACK to indicate rollback mode
     */
    public function setRollback($objectModel)
    {
        $this->obj_id = $objectModel->id;
        $this->obj_type = $objectModel->tableName();
        $this->process_from = $objectModel->status;
        $this->action = WorkflowManager::ACTION_ROLLBACK;
        $this->decision = WorkflowManager::getDecisionBeforeProcess($this->obj_type, $this->process_from);
        $this->condition1 = Sii::t('sii','Manual-triggered rollback');
    }
    /**
     * Indicate if current transition is under rollback mode
     */
    public function onRollback()
    {
        return $this->action==WorkflowManager::ACTION_ROLLBACK;
    }
    /**
     * Check if transition has decision
     * 
     * @return boolean
     */
    public function hasDecision() 
    {
       return $this->decision!=null;
    }         
    /**
     * Indicate the begin process of transition 
     */
    public function beginProcess()
    {
        return $this->process_from;
    }
    /**
     * Indicate the end process of transition 
     */
    public function endProcess()
    {
        return $this->process_to;
    }

    public function objType($type) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'obj_type=\''.$type.'\'',
            'order'=>'transition_time ASC',
        ]);
        return $this;
    }
    public function objId($id=null) 
    {
        if (isset($id)){
            $this->getDbCriteria()->mergeWith([
                'condition'=>'obj_id='.$id,
                'order'=>'transition_time ASC',
            ]);
        }
        return $this;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('process_from',$this->process_from,true);
        $criteria->compare('process_to',$this->process_to,true);
        $criteria->compare('action',$this->action,true);
        $criteria->compare('decision',$this->decision,true);
        $criteria->compare('condition1',$this->condition1,true);
        $criteria->compare('condition2',$this->condition2,true);
        $criteria->compare('transition_by',$this->transition_by);
        $criteria->compare('transition_time',$this->transition_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function getViewUrl() 
    {
        //put blank on purpose; not in used
    }

    public function getProcessedBy($user)
    {
        if ($this->isViewable($user,true)){
            if (Account::isSubType($user->getId()))
                return $this->account->email;
            else
                return $this->account->name;
        }
        else {
            $workflow = WorkflowManager::getProcess($this->obj_type, $this->process_from, $this->action);
            return Sii::t('sii',$workflow->start_by);
        }
    }
}