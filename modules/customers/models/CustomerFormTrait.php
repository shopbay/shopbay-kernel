<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.customers.models.CustomerAddressForm');
/**
 * Description of CustomerFormTrait
 *
 * @author kwlok
 */
trait CustomerFormTrait 
{
    public $first_name;
    public $last_name;
    public $alias_name;
    public $mobile;
    public $address;//this field is used to store the customer address retrieved from order
    
    public function initAddress() 
    {
        $this->address = new CustomerAddressForm();
    }
    /**
     * Validation rules
     */
    public function formRules() 
    {
        return [
            ['first_name, last_name, alias_name', 'length', 'max'=>50],
            ['mobile', 'numerical', 'min'=>8, 'integerOnly'=>true],
            ['mobile', 'length', 'max'=>20],
            ['address','ruleAddress'],
        ];       
    }
    /**
     * Validation rules for address
     * @param type $attribute
     * @param type $params
     */
    public function ruleAddress($attribute,$params)
    {
        $this->address->validate();
    }    
    /**
     * Declares attribute labels.
     */
    public function formAttributeLabels()
    {
        return [
            'first_name' => Sii::t('sii','First Name'),
            'last_name' => Sii::t('sii','Last Name'),
            'alias_name' => Sii::t('sii','Alias Name'),
            'mobile' => Sii::t('sii','Mobile'),
            'address' => Sii::t('sii','Address'),
            'address2' => Sii::t('sii','Address 2'),
            'postcode' => Sii::t('sii','Postal Code'),
            'city' => Sii::t('sii','City'),
            'state' => Sii::t('sii','State'),
            'country' => Sii::t('sii','Country'),
        ];
    }
    /**
     * Get address information in array, 
     * @param type $extraFields If to include extra information, default to include mobile
     * @return type
     */
    public function toAddressArray($extraFields=['mobile'])
    {
        $address = $this->address->toArray();
        foreach ($extraFields as $field) {
            $address[$field] = $this->$field;
        }
        return $address;
    }
    
    public function hasFirstName()
    {
        return $this->first_name!=null;
    }

    public function hasLastName()
    {
        return $this->last_name!=null;
    }

    public function hasAliasName()
    {
        return $this->alias_name!=null;
    }
}
