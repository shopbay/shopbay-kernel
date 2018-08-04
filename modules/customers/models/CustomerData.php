<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CustomerData, CustomerShopData and CustomerAddressData
 *
 * @author kwlok
 */
class CustomerData extends CComponent
{
    public $last_order_id;
    public $last_shop_id;
    public $shop_data = [];
    /**
     * Check if has data
     * Sample check two fields not to be null
     * 
     * @return boolean
     */
    public function hasData()
    {
        return $this->last_order_id!=null && $this->last_shop_id!=null;
    }
    /**
     * Assign data usign json encoded data
     * @param $json encoded string
     */
    public function assignData($json)
    {
        $data = json_decode($json,true);
        if (isset($data['last_shop_id']))
            $this->last_shop_id = $data['last_shop_id'];
        if (isset($data['last_order_id']))
            $this->last_order_id = $data['last_order_id'];
        if (isset($data['shop_data']))
            $this->shop_data = $data['shop_data'];
    }
    public function addShopData($shop_id,$shopData)
    {
        if (!($shopData instanceof CustomerShopData))
            throw new CException(Sii::t('sii','Invalid shop data object'));
        
        if (!isset($this->shop_data))
            $this->shop_data = [$shop_id=>$shopData];//first shop data
        else {
            $this->shop_data[$shop_id] = $shopData;//keep existing, and add new data
        }
    }
    public function hasShopData()
    {
        return $this->shop_data!=null;
    }
    public function getShopData()
    {
        return $this->shop_data;
    }
    /**
     * Return data as array
     * @return array
     */
    public function toArray()
    {
        return [
            'last_shop_id'=>$this->last_shop_id,
            'last_order_id'=>$this->last_order_id,
            'shop_data'=>$this->shop_data,
        ];
    }    
    
}

class CustomerShopData extends CComponent
{
    public $shop_id;
    public $total_spent=0.0;
    public $total_orders=0;
    public $last_order_id;
    /**
     * Constructor.
     */
    public function __construct($totalSpent=0.0,$totalOrders=0,$lastOrderId=null)
    {
        $this->total_spent = $totalSpent;
        $this->total_orders = $totalOrders;
        $this->last_order_id = $lastOrderId;
    }
    /**
     * Return data as array
     * @return array
     */
    public function toArray()
    {
        return [
            'last_order_id'=>$this->last_order_id,
            'total_orders'=>$this->total_orders,
            'total_spent'=>$this->total_spent,
        ];
    }    
    
    private $_s;
    /**
     * Return shop visited by this customer
     */
    public function getShop()
    {
        if (isset($this->shop_id)){
            if ($this->_s===null){
                $this->_s = Shop::model()->findByPk($this->shop_id);
            }
        }
        return $this->_s;
    }    
    /**
     * Return shop link visited by this customer
     */
    public function getShopLink()
    {
        return CHtml::link($this->shop->displayLanguageValue('name',user()->getLocale()),$this->shop->viewUrl);
    }    
    
    private $_o;
    /**
     * Return last order purchased by this customer
     */
    public function getLastOrder()
    {
        if (isset($this->last_order_id)){
            if ($this->_o===null){
                $this->_o = Order::model()->findByPk($this->last_order_id);
            }
        }
        return $this->_o;
    }    
    /**
     * Return last order link purchased by this customer
     */
    public function getLastOrderLink()
    {
        return $this->lastOrder->orderPaid()?CHtml::link($this->lastOrder->order_no,$this->lastOrder->viewUrl):$this->lastOrder->order_no;
    }    

}
