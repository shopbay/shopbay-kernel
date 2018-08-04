<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ChildModelBehavior
 *
 * @author kwlok
 */
class ChildModelBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of parent attribute.
     */
    public $parentAttribute = 'undefined';
    /**
     * This stores all the childs in one data array
     * Example data:
     * array(
     *   array(
     *     'childAttribute'=>'categories',
     *     'childModelClass'=>'ProductCategory',
     *     'childUpdatableAttributes'=>array('category_id','subcategory_id'),
     *   ),
     *   array(
     *     'childAttribute'=>'shippings',
     *     'childModelClass'=>'ProductShipping',
     *     'childUpdatableAttributes'=>array('shipping_id','surcharge'),
     *   ),
     * )
     * @var array settings of all childs in one sibling array
     */
    public $siblings = array();
    /**
     * @var string The name of child attribute.
     */
    public $childAttribute = 'undefined';
    /**
     * @var string The name of child model class.
     */
    public $childModelClass = 'undefined';
    /**
     * @var array The array of child attributes that are updatable
     */
    public $childUpdatableAttributes = array();
    /**
     * @var string The scenario to set when instantiate a new child.
     */
    public $childCreateScenario;
    /**
     * @var string The scenario to set when found an existing child.
     */
    public $childUpdateScenario;
    /**
     * @var string after insert call back
     */
    public $afterInsert;
    /**
     * @var string after update call back
     */
    public $afterUpdate;
    /**
     * Rules to validate childs
     */
    public function ruleChilds($errorKey) 
    {
        $this->_ruleChildsInternal($this->childAttribute, $this->childUpdatableAttributes, $errorKey);
    }        
    /**
     * Insert childs, validation has to be done first before calling this method
     */
    public function insertChilds()
    {
        $this->_insertChildsInternal($this->childAttribute,$this->afterInsert);
    }
    /**
     * Update childs, validation has to be done first before calling this method
     */
    public function updateChilds()
    {      
        $this->_updateChildsInternal($this->childModelClass, $this->childAttribute, $this->childUpdatableAttributes, $this->afterUpdate);
    }

    public function deleteChilds($excludes=array()) 
    {
        $this->_deleteChildsInternal($this->childModelClass, $excludes);
    } 
    
    /**
     * Rules to validate siblings
     */
    public function ruleSiblings($errorKey) 
    {
        if (!empty($this->siblings)){
            foreach ($this->siblings as $data) {
                $this->_ruleChildsInternal($data['childAttribute'], $data['childUpdatableAttributes'], $errorKey);
            }
        }
    }        
    /**
     * Insert siblings, validation has to be done first before calling this method
     */
    public function insertSiblings()
    {      
        if (!empty($this->siblings)){
            foreach ($this->siblings as $data) {
                $this->_insertChildsInternal($data['childAttribute']);
            }
        }
    }   
    /**
     * Update siblings, validation has to be done first before calling this method
     */
    public function updateSiblings()
    {      
        if (!empty($this->siblings)){
            foreach ($this->siblings as $data) {
                $this->_updateChildsInternal($data['childModelClass'], $data['childAttribute'], $data['childUpdatableAttributes']);
            }
        }
    }   
    
    public function deleteSiblings($excludes=array()) 
    {
        if (!empty($this->siblings)){
            foreach ($this->siblings as $data) {
                $this->_deleteChildsInternal($data['childModelClass'], $excludes);
            }
        }
    } 
    
    private function _insertChildsInternal($childAttribute,$afterInsert=null)
    {
        foreach ($this->getOwner()->$childAttribute as $child){
            unset($child->id);//set id to null to have auto increment key
            $child->{$this->parentAttribute} = $this->getOwner()->id;
            $child->insert();
        } 
        if (isset($afterInsert))
            $this->getOwner()->$afterInsert();
    }
    
    private function _updateChildsInternal($childModelClass,$childAttribute,$childUpdatableAttributes,$afterUpdate=null)
    {      
       $deleteExcludeList = new CList();
       foreach ($this->getOwner()->$childAttribute as $child){
            $found = $childModelClass::model()->findByPk($child->id);
            if ($found==null){//record not found
                $c = new $childModelClass();//$child->id is auto incremented value in db
                $c->{$this->parentAttribute}=$this->getOwner()->id;
                $c->attributes = $child->getAttributes($childUpdatableAttributes);
                if (isset($this->childCreateScenario))
                    $c->setScenario($this->childCreateScenario);
                $scenario = $c->getScenario();//capture scenario before get changed after insert
                $c->insert();//after insert scenario will get updated to "update" - yiiframwork
                $deleteExcludeList->add($c->id);
                logTrace(__METHOD__.' child with scenario "'.$scenario.'" created successfully',$c->getAttributes());
            }
            else{
                $found->attributes = $child->getAttributes($childUpdatableAttributes);
                if (isset($this->childUpdateScenario))
                    $found->setScenario($this->childUpdateScenario);
                $scenario = $found->getScenario();//capture scenario before get changed after insert
                $found->update();
                $deleteExcludeList->add($found->id);
                logTrace(__METHOD__.' child with scenario "'.$scenario.'" updated successfully',$found->getAttributes());
            }
        } 
        $this->_deleteChildsInternal($childModelClass,$deleteExcludeList->toArray());
        if (isset($afterUpdate))
            $this->getOwner()->$afterUpdate();
    }    

    private function _deleteChildsInternal($childModelClass,$excludes=array()) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array($this->parentAttribute=>$this->getOwner()->id));
        if (empty($excludes)){
            $childModelClass::model()->deleteAll($criteria);
        }
        else {
            $criteria->addNotInCondition('id',$excludes);
            logTrace(__METHOD__.' delete childs',$criteria);
            foreach ($childModelClass::model()->findAll($criteria) as $unwanted){
               try {
                    //delete db record
                    logTrace(__METHOD__.' delete unwanted '.$unwanted->id,$unwanted->getAttributes());
                    $unwanted->delete();
                } catch (CException $e) {
                    logError(__METHOD__.' unwanted delete error ',$e->getTrace());
                }
            }
        }        
    }     
    
    private function _ruleChildsInternal($childAttribute,$childUpdatableAttributes,$errorKey) 
    {
       foreach ($this->getOwner()->$childAttribute as $child) {
            if (!$child->validate($childUpdatableAttributes)){
                foreach ($childUpdatableAttributes as $field){
                    if ($child->hasErrors($field))
                        $this->getOwner()->addError($errorKey,$child->getError($field));
                }
            }
       }//end for loop
    }        
    
}
