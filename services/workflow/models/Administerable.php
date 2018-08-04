<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * Description of Administerable
 *
 * @author kwlok
 */
abstract class Administerable extends Transitionable
{
    public $suspendedStatus = 'undefined';
    public $suspendableStatus = 'undefined';
    
    protected $administerable = true;
    private $_officer;
    public function getOfficer() 
    {
       return $this->_officer;
    }
    
    public function setOfficer($account_id) 
    {
        $this->_officer=$account_id;
    }
    
    public function getOfficerAccount() 
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'id';
        $criteria->addColumnCondition(array('id'=>$this->getOfficer()));
        return Account::model()->find($criteria); 
    }  
    
    public function isSuspended()
    {
        return $this->getOwner()->status == $this->suspendedStatus;
    }
    
    public function isSuspendable()
    {
        if (is_array($this->suspendableStatus))
            return in_array($this->getOwner()->status, $this->suspendableStatus);
        else
            return $this->getOwner()->status == $this->suspendableStatus;
    }
    
    public function disableAdministerable() 
    {
       return $this->administerable = false;
    }        
    /**
     * Resume model from suspended status
     * @param type $targetStatus Should be the offline status
     * @throws CException
     */
    public function resume($targetStatus)
    {
        if (!$this->isSuspended())
            throw new CException(Sii::t('sii','Model cannot be resumed.'));
        
        $this->status = $targetStatus;
        $this->update();
        foreach ($this->searchMediaAssociation()->data as $assoc) {
            if ($assoc->media->online()){
                $assoc->media->status = Process::MEDIA_OFFLINE;//deactivate those online
                $assoc->media->update();
            }
        }
    }        
}
