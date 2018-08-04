<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of NotificationScope
 *
 * Notification scope is to determine if the notification is having any boundary.
 * If scope class is Shop, the notfication subscription is only limited by a specific shop
 * If scope class is Account (Global), the notfication subscription is global, and notification from all shops are followed.
 * 
 * @author kwlok
 */
class NotificationScope extends CComponent 
{
    public $class;
    public $id;
    /**
     * Constructor
     * @param type $id
     * @param type $class
     */
    public function __construct($id,$class=null) 
    {
        $this->id = $id;
        if (!isset($class))
            $class = $this->globalClass;//default to global
        $this->class = $class;
    }
    /**
     * To array
     * @return type
     */
    public function toArray()
    {
        return [
            'class'=>$this->class,
            'id'=>$this->id,
        ];
    }
    /**
     * To json encoded string
     * @return type
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }
    /**
     * If the scope is global
     * @return boolean
     */
    public function getIsGlobal()
    {
        return $this->class==$this->globalClass;
    }
    /**
     * The class equivalent to global
     * @return string
     */
    public function getGlobalClass()
    {
        return get_class(Account::model());
    }
}
