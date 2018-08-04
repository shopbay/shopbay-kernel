<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.models.AddressTrait");
/**
 * Description of CustomerAddressForm
 *
 * @author kwlok
 */
class CustomerAddressForm extends CFormModel 
{
    use AddressTrait;
    
    public $mobile;
    public $address1;//street 1
    public $address2;//street 2
    public $postcode;
    public $city;
    public $state;
    public $country;
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['mobile', 'numerical', 'integerOnly'=>true],
            ['mobile, postcode', 'length', 'max'=>20],
            ['address1, address2', 'length', 'max'=>100],
            ['state, city, country', 'length', 'max'=>40],
            ['mobile, address1, address2, postcode, city, state, country', 'safe'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'mobile' => Sii::t('sii','Mobile'),
            'address1' => Sii::t('sii','Address'),
            'address2' => Sii::t('sii','Address 2'),
            'postcode' => Sii::t('sii','Postcode'),
            'city' => Sii::t('sii','City'),
            'state' => Sii::t('sii','State'),
            'country' => Sii::t('sii','Country'),
        ];
    }
    
    public function fillForm($addressData)
    {
        if (!($addressData instanceof CustomerAddressData))
            throw new CException(Sii::t('sii','Invalid address data object'));
        
        foreach(array_keys($this->attributes) as $attribute){
            $this->$attribute = $addressData->$attribute;
        }
    }
}
