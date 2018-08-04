<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AddressTrait
 * For Model (Account, Shop, Order) having address fields:
 * <pre>
 * $address1
 * $address2
 * $postcode
 * city
 * state
 * country
 * </pre>
 * @author kwlok
 */
trait AddressTrait 
{
    /**
     * This combine address 1 and 2
     */
    public function getStreet()
    {
        $street = $this->address1;
        if (isset($this->address2))
            $street .= ' '.$this->address2;
        return $street;
    }

    public function getLongAddress()
    {
        $address = $this->getShortAddress().
                   $this->city.', '.
                   $this->postcode.', '.
                   ($this->state!=null?', '.$this->state:'').
                   $this->country;
        //logTrace(__METHOD__.' '.$address);
        return $address;
    }
    
    public function getShortAddress()
    {
        $address = $this->address1.($this->address2!=null?', '.$this->address2:'');
        //logTrace(__METHOD__.' '.$address);
        return $address;
    }
    /**
     * Check if necessary address fields
     */
    public function hasAddress()
    {
        return strlen($this->address1.$this->city.$this->postcode.$this->country)>0;
    }
    
    public function hasLongAddress()
    {
        return ($this->address1!=null && $this->city!=null && $this->postcode!=null && $this->country!=null);
    }
    
    public function toArray()
    {
        return [
            'address1'=>$this->address1,
            'address2'=>$this->address2,
            'postcode'=>$this->postcode,
            'city'=>$this->city,
            'state'=>$this->state,
            'country'=>$this->country,
        ];
    }
}
