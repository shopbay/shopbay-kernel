<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.messages.models.Message");
Yii::import("common.modules.billings.models.Billing");
/**
 * Description of Reminder
 *
 * @author kwlok
 */
class Reminder extends Message
{
    private $_b;//billing instance
    
    protected function getHasBilling()
    {
        return $this->_b!=null;
    }
    
    protected function getBilling()
    {
        if (!isset($this->_b))
            $this->_b = Billing::model()->mine($this->recipient)->find();
        return $this->_b;
    }
    
    public function getRecipientEmail()
    {
        return $this->hasBilling?$this->billing->email:parent::getRecipientEmail();
    }
    
    public function getRecipientName()
    {
        return $this->hasBilling?$this->billing->billed_to:parent::getRecipientName();
    }         
    
}
