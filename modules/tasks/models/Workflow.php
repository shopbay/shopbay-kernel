<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_workflow".
 *
 * The followings are the available columns in table 's_workflow':
 * @property integer $id
 * @property string $obj_type
 * @property string $start_process
 * @property string $action
 * @property string $decision
 * @property string $end_process
 *
 * @author kwlok
 */
class Workflow extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Workflow the static model class
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
        return 's_workflow';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['obj_type, start_process, action, end_process', 'required'],
            ['obj_type', 'length', 'max'=>30],
            ['start_process, action', 'length', 'max'=>20],
            ['end_process', 'length', 'max'=>200],
            ['decision', 'length', 'max'=>30],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, obj_type, start_process, action, decision, end_process', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'obj_type' => Sii::t('sii','Object Type'),
            'start_process' => Sii::t('sii','Start Process'),
            'action' => Sii::t('sii','Action'),
            'decision' => Sii::t('sii','Decision'),
            'end_process' => Sii::t('sii','End Process'),
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
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('start_process',$this->start_process,true);
        $criteria->compare('action',$this->action,true);
        $criteria->compare('decision',$this->decision,true);
        $criteria->compare('end_process',$this->end_process,true);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    /**
     * Get start process
     * @return type
     */
    public function getStartProcess()
    {
        return $this->start_process;
    }           
    /**
     * Get decision made for the end process
     * @param type $endProcess
     * @return type
     */
    public function getEndProcessDecision($endProcess) 
    {
        if ($this->hasDecision()){
            $processes = json_decode($this->end_process,true);
            if (is_array($processes)){//end process array exists
                $decisions = array_flip($processes);
                if (isset($decisions[$endProcess]))
                    return $decisions[$endProcess];
            }
            else
                return null;//no decision set
        }
        else 
           return null;//no decision set
    }
    /**
     * Get end process based on decision
     * @param type $decision
     * @return type
     */
    public function getEndProcess($decision=null)
    {
        if ($this->hasDecision() && isset($decision)){
            $endProcess = json_decode($this->end_process,true);
            if (is_array($endProcess)){//end process object exists
                if (isset($endProcess[$decision]))
                    return $endProcess[$decision];
                else {
                    $values = array_values($endProcess);
                    return array_shift($values);//always return first end_process
                }
            }
            else
                return $endProcess;        
        }
        else
            return $this->end_process;        
    }
    /**
     * Get all possible end processes
     */
    public function getAllEndProcess()
    {
        $allEndProcesses = json_decode($this->end_process,true);
        if (is_array($allEndProcesses)){//end process object exists
            return array_values($allEndProcesses);
        }
        else
            return $this->end_process;        
    }
    /**
     * Check if has end process
     * @param type $process
     * @return boolean
     */
    public function hasEndProcess($process)
    {
        $endProcesses = $this->getAllEndProcess();
        if (is_array($endProcesses)){//end process object exists
            return in_array($process, $endProcesses);
        }
        else
            return $process==$endProcesses;        
    }    
    /**
     * Check if has decision
     * @return boolean
     */
    public function hasDecision() 
    {
        if ($this->decision!=null)
            return true;
        return false;
    }
    /**
     * Get decisions
     * @return type
     */
    public function getDecision() 
    {
        if ($this->hasDecision()) {
            $condition = explode(WorkflowManager::DECISION_SEPARATOR, $this->decision);
            return $condition;
        }
        return null;
    }
    /**
     * @see Workflow::parseWorkflowAction
     * @return type
     */
    public function parseAction()
    {
        return Workflow::parseWorkflowAction($this->action);
    }
    /**
     * As 'return' action cannot be used due to reserved word in php
     * Have to have this helper to return correctly
     * @return type
     */
    public static function parseWorkflowAction($action)
    {
        return $action==WorkflowManager::ACTION_RETURNITEM?WorkflowManager::ACTION_RETURN:$action;    
    }
}