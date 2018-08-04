<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of OrderShippingAddressForm
 *
 * @author kwlok
 */
class OrderShippingAddressForm extends CFormModel 
{    
    const GUEST_CHECKOUT = 'guest-checkout';
    public $recipient;
    public $mobile;
    public $address1;
    public $address2;
    public $postcode;
    public $city;
    public $state;
    public $country;
    public $note;
    public $email;
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['recipient, mobile, address1, postcode, city, country', 'required'],
            ['mobile', 'numerical', 'integerOnly'=>true],
            ['recipient', 'length', 'max'=>32],
            ['mobile, postcode', 'length', 'max'=>20],
            ['address1, address2', 'length', 'max'=>100],
            ['state, city, country', 'length', 'max'=>40],
            ['note', 'length', 'max'=>200],
            ['recipient, mobile, address1, address2, postcode, city, state, country, note, email', 'safe'],
            ['email', 'length', 'max'=>200],
            ['email', 'email'],
            ['email','required','on'=>self::GUEST_CHECKOUT],
       ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'recipient' => Sii::t('sii','Recipient'),
            'mobile' => Sii::t('sii','Mobile'),
            'address1' => Sii::t('sii','Address'),
            'address2' => Sii::t('sii','Address'),
            'postcode' => Sii::t('sii','Postcode'),
            'city' => Sii::t('sii','City'),
            'state' => Sii::t('sii','State'),
            'country' => Sii::t('sii','Country'),
            'note' => Sii::t('sii','Shipping Note'),
            'email' => Sii::t('sii','Email Address'),
        ];
    }
        
}
