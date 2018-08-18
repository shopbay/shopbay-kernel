<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MakerCheckerTrait
 *
 * @author kwlok
 */
trait MakerCheckerTrait 
{
    /**
     * A wrapper method to return drafted records of this model
     * @return \Plan
     */
    public function drafted() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->draftedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * A wrapper method to return submitted records of this model
     * @return \Plan
     */
    public function submitted() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->submittedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * A wrapper method to return approved records of this model
     * @return \Plan
     */
    public function approved() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->approvedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * Check if plan can be submitted
     * @param boolean $useText True if to check status againsts process text rather than code; Default to 'false' - use code
     */
    public function submitable($user=null,$useText=false)
    {
        return $this->account_id==(isset($user)?$user:user()->getId()) && $this->status==($useText?Process::getText($this->draftedStatus):$this->draftedStatus);
    }
    /**
     * Check if plan can be approved
     */
    public function approvable($user=null,$useText=false)
    {
        return $this->account_id==(isset($user)?$user:user()->getId()) && $this->status==($useText?Process::getText($this->submittedStatus):$this->submittedStatus);
    }
    /**
     * Check if plan can be subscribed
     */
    public function subscribable()
    {
        return $this->status==$this->approvedStatus;
    }
    /**
     * Check if plan is approved
     */
    public function getIsApproved()
    {
        return $this->status==$this->approvedStatus;
    }    
    /**
     * @return boolean if plan is updatable
     */
    public function updatable($user=null,$useText=false)
    {
        //allow plan to be edited even after approval
        return $this->account_id==(isset($user)?$user:user()->getId());
        //return $this->submitable($user,$useText);
    }    
    /**
     * @return boolean if plan is deletable
     */
    public function deletable($user=null,$useText=false)
    {
        return $this->submitable($user,$useText);
    } 
}
