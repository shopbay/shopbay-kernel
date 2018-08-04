<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CustomerAddressData
 *
 * @author kwlok
 */
class CustomerAddressData extends CComponent
{
    public $mobile;
    public $address1;
    public $address2;
    public $postcode;
    public $city;
    public $state;
    public $country;
    /**
     * Constructor.
     */
    public function __construct($street1=null,$street2=null,$postcode=null,$city=null,$state=null,$country=null,$mobile=null)
    {
        $this->address1 = $street1;
        $this->address2 = $street2;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->mobile = $mobile;
    }
    /**
     * Return data as array
     * @return array
     */
    public function toArray()
    {
        return [
            'address1'=>$this->address1,
            'address2'=>$this->address2,
            'postcode'=>$this->postcode,
            'city'=>$this->city,
            'state'=>$this->state,
            'country'=>$this->country,
            'mobile'=>$this->mobile,
        ];
    }    
    
    public function toString()
    {
        return json_encode($this->toArray());
    }
    
    public function fillData($addressForm)
    {
        if (!($addressForm instanceof CustomerAddressForm))
            throw new CException(Sii::t('sii','Invalid address form'));
        
        foreach(array_keys($addressForm->attributes) as $attribute){
            $this->$attribute = $addressForm->$attribute;
        }
    }    
}

