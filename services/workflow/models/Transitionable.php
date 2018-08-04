<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of Transitionable
 *
 * @author kwlok
 */
abstract class Transitionable extends SActiveRecord 
{
    /*
     * Indicate if this object is administerable
     * @see Administerable
     */
    protected $administerable = false;
    /**
     * Indicate if should implement soft delete (it means record is not physical deleted)
     * @var boolean 
     */
    protected $enableSoftDelete = true;
    /**
     * Call back to delete childs for hard delete
     * @var boolean 
     */
    protected $deleteChildsCallback;
    /**
     * Status to indicate soft delete
     * @var string 
     */
    protected $softDeleteStatus = Process::DELETED;
    /**
     * A wrapper method to return all records of this model
     * @return \SActiveRecord
     */
    public function all() 
    {
        if ($this->enableSoftDelete){
            $criteria = new CDbCriteria();
            if (is_array($this->softDeleteStatus))
                $criteria->addNotInCondition('status',$this->softDeleteStatus);
            else
                $criteria->condition = 'status!=\''.$this->softDeleteStatus.'\'';
            $this->getDbCriteria()->mergeWith($criteria);
        }
        return $this;
    }   
    /**
     * Finds object under soft-delete conditions
     * @See CActiveRecord::find()
     * @return static the record found. Null if no record is found.
     */
    public function findByPk($pk,$condition='',$params=[])
    {
        if ($this->enableSoftDelete){
            $criteria = new CDbCriteria();
            if (is_array($this->softDeleteStatus))
                $criteria->addNotInCondition('status',$this->softDeleteStatus);
            else
                $criteria->condition = 'status!=\''.$this->softDeleteStatus.'\'';

            if (strlen($condition)>0){
                $criteria->mergeWith($condition);
            }
        }
        return parent::findByPk($pk,$criteria,$params);
    }    
    /**
     * Override delete: support both soft and hard delete
     */
    public function delete()
    {
        if ($this->enableSoftDelete){
            if (is_array($this->softDeleteStatus))
                $this->status = $this->softDeleteStatus[0];//take the first one
            else
                $this->status = $this->softDeleteStatus;
            $this->update();
        }
        else {
            if (isset($this->deleteChildsCallback))
                call_user_func([$this,$this->deleteChildsCallback]);
            parent::delete();
        }
    }    
    /**
     * Check if model is administerable
     * @return type
     */
    public function isAdministerable()
    {
        return $this->administerable;
    }
    /**
     * Default url to work on task for this model
     * @return string url
     */
    public function getTaskUrl($action)
    {
        return url('tasks/'.strtolower(get_class($this))).'/'.strtolower($action);
    }      
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchTransition($id,$pagesize=10)
    {
        return  new CActiveDataProvider(Transition::model()->objType($this->tableName())->objId($id),[
                    'criteria'=>['order'=>'transition_time DESC'],
                    'pagination'=>['pageSize'=>$pagesize],
                    'sort'=>false,
                ]);
    }        
    /**
     * Search transition perform by user
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchMyTransition($id=null,$pagesize=10)
    {
        return  new CActiveDataProvider(Transition::model()->mine()->objType($this->tableName())->objId($id),[
                    'criteria'=>['order'=>'transition_time DESC'],
                    'pagination'=>['pageSize'=>$pagesize],
                    'sort'=>false,
                ]);
    }        

    public function status($process) 
    {
        if ($process==null)
            return $this;//nothing
        
        $criteria = new CDbCriteria();
        if (is_array($process))
            $criteria->addInCondition('status',$process);
        else
            $criteria->condition = 'status =\''.$process.'\'';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

    public function getStatusText($color=true)
    {
        if (strpos($this->status,';')===false)//status is already in display text (in api mode)
            return $color?Process::getTextInColor($this->status):$this->status;
        else
            return $color?Process::getDisplayTextWithColor($this->status):Process::getDisplayText(Process::getText($this->status));
    }

    public function getStatusColor()
    {
        return Process::getColor($this->status);
    }
    
    public function countByStatus($status) 
    {
        $criteria = new CDbCriteria();
        if (is_array($status))
            $criteria->addInCondition('status',$status);
        else
            $criteria->condition = 'status=\''.$status.'\'';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this->count();
    }

    public function getTransitionCondition() 
    {
        $criteria=new CDbCriteria;
        $criteria->select='condition1,condition2';
        $criteria->addColumnCondition(['obj_type'=>$this->tableName(),'obj_id'=>$this->id,'process_to'=>$this->status]);
        $transition = Transition::model()->find($criteria);
        return $transition;
    }        
        
    public function getOnSoftDelete()
    {
        return $this->enableSoftDelete;
    }
}