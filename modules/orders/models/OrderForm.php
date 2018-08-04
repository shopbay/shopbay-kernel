<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Form to be filled when submitting order
 * 
 * @author kwlok
 */
class OrderForm extends CFormModel 
{    
    public $order_no;//system assigned value
    public $status;//system assigned value
    public $account_id;//buyer id
    public $shop_id;
    public $item_total;
    public $item_count;
    public $item_weight;
    public $item_shipping;
    public $shipping_total;
    public $discount;//store discount data, including campaign information
    public $tax;//stare tax data
    public $grand_total;
    public $payment_method;    
    public $currency;
    public $weight_unit;
    public $remarks;
    public $extraPaymentData;//optional, mainly to store payment gateway response data
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'paymentformbehavior' => [
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ],
        ];
    }     
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, item_total, item_count, item_shipping, shipping_total, discount, tax, grand_total, payment_method, currency, weight_unit', 'required'],
            ['account_id, shop_id', 'numerical', 'integerOnly'=>true],
            ['item_weight, item_total, shipping_total, grand_total', 'length', 'max'=>10],
            ['currency, weight_unit', 'length', 'max'=>3],
            ['item_count', 'length', 'max'=>127],//tinyint datatype
            ['item_shipping', 'length', 'max'=>2500],
            ['payment_method', 'length', 'max'=>1500],
            ['discount', 'length', 'max'=>2000],
            ['remarks', 'length', 'max'=>100],
            ['tax', 'length', 'max'=>500],
        ];
    }
    
    public function getAssignableAttributeNames()
    {
        $excludes = array('order_no','status','extraPaymentData');
        return array_diff($this->attributeNames(), $excludes);// remove the elements of $excludes
    }
    
    private $_addr;//storing OrderShippingAddressForm
    public function setShippingAddress($addrForm)
    {
        if (!($addrForm instanceof OrderShippingAddressForm))
            throw new CException(Sii::t('sii','Invalid type of order shipping address form'));
        $this->_addr = $addrForm;
    }

    public function getShippingAddress()
    {
        return $this->_addr;
    }
    
    private $_items;//storing OrderItemForm
    public function addItem($itemForm)
    {
        if (!($itemForm instanceof OrderItemForm))
            throw new CException(Sii::t('sii','Invalid type of order item form'));
        if ($this->_items==null)
            $this->_items = new CList();
        $this->_items->add($itemForm);
    }

    public function getItems()
    {
        return $this->_items->toArray();
    }
    
}
