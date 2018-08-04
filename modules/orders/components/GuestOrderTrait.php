<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of GuestOrderTrait
 *
 * @author kwlok
 */
trait GuestOrderTrait 
{
    public function guest() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'account_id='.Account::GUEST,
        ]);
        return $this;
    }
    
    public function getBuyerName()
    {
        if ($this instanceof Item)
            $order = $this->order;
        else //if ($this instanceof Order)
            $order =  $this;
        return isset($order->address->recipient)?$order->address->recipient:(isset($this->account->profile->name)?$this->account->profile->name:$this->account->name);
    }
    /**
     * @param type $nullable if true, it will not search into account email if order email not found, and return null
     * @return type
     */
    public function getBuyerEmail($nullable=true)
    {
        if ($this instanceof Item)
            $order = $this->order;
        else //if ($this instanceof Order)
            $order =  $this;

        if ($nullable)
            return $order->address->email;
        else
            return isset($order->address->email)?$order->address->email:$this->account->email;
    }
    /**
     * @return boolean if the order is purchased by guest customer
     */
    public function byGuestCustomer()
    {
        return $this->account_id == Account::GUEST;
    }
    /**
     * Url to track this model
     * @return string url
     */
    public function getGuestAccessUrl($domain=null)
    {
        $type = get_class($this);
        if ($type=='Order')
            return $type::getAccessUrl($this->order_no,true,$domain);
        elseif ($type=='Item')
            return $type::getAccessUrl($this,$domain);
        else
            return null;
    }    
    /**
     * @return boolean if the order can be run task workflow by guest
     */
    public function allowGuestTaskWorkflow()
    {
        //below method will not work if shop previously turn on guest checkout, but later disable it
        //in between buyer come and want to pay order
        //return $this->byGuestCustomer() && $this->shop->isGuestCheckoutAllowed();
        return $this->byGuestCustomer() && $this->buyerEmail!=null;
    }
    /**
     * This is used for NotificationManager to send emails
     * for using RECIPIENT_CLASSMETHOD
     * @return type
     */
    public function getNotification()
    {
        return [
            'email'=>$this->getBuyerEmail(false),
            'emailSenderName'=>$this->shop->getEmailSenderName(),//is for merchant to send to customer
            'recipient'=>$this->getBuyerName(),
        ];
    }    
}

