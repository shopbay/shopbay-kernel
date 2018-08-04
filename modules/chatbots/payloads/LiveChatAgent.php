<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of LiveChatAgent
 *
 * @author kwlok
 */
class LiveChatAgent extends CComponent
{
    public $id;//agent messenger id
    public $name;//agent name
    
    public function __construct($id,$name) 
    {
        $this->id = $id;
        $this->name = $name;
    }
    
    public function toArray() 
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
        ];
    }
    
    public function toString()
    {
        return json_encode($this->toArray());
    }
    
    public static function decode($encodedString)
    {
        $data = json_decode($encodedString,true);
        if (is_array($data) && 
            isset($data['id']) && 
            isset($data['name'])){
            return new LiveChatAgent($data['id'], $data['name']);
        }
        else
            return null;
    }
    
}