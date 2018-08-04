<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of OrderBehavior
 *
 * @author kwlok
 */
class OrderBehavior extends CActiveRecordBehavior 
{
    public $orderIdAttribute = 'id';
    public $paymentReferenceAttribute = 'order_no';
    
    public function orderNo($orderNo) 
    {
        $this->getOwner()->getDbCriteria()->mergeWith(array(
            'condition'=>'order_no=\''.$orderNo.'\'',
        ));
        return $this->getOwner();
    }

    public function searchPayments()
    {
        return new CActiveDataProvider('Payment',array(
            'criteria'=>array('condition'=>'reference_no=\''.$this->getOwner()->{$this->paymentReferenceAttribute}.'\''),
            'sort'=>false,
        ));
    }          
    public function searchShippingAddress()
    {
        return new CActiveDataProvider('OrderAddress',array(
            'criteria'=>array('condition'=>'order_id=\''.$this->getOwner()->{$this->orderIdAttribute}.'\''),
            'sort'=>false,
        ));
    }      
    
    public function existsMerchant($shop_id)
    {
        return $shop_id==$this->shop_id;
    }    
    /**
     * Return the first purchased product of this order
     * @return type
     */
    public function getProduct() 
    {
        return Item::model()->order($this->getOwner()->{$this->orderIdAttribute})->find()->product;
    }    
    
    public function getPayment() 
    {
        return Payment::model()->invoice(Payment::SALE,$this->getOwner()->{$this->paymentReferenceAttribute})->find();
    }
    /**
     * Get the order number random string fashion
     */
    public function orderNumRandomStringFashion()
    {
        return $this->getOwner()->shop->getOrdersSetting($this->orderNumType().'NumRandomString');
    }      
    /**
     * Get the order number prefix
     */
    public function orderNumPrefix()
    {
        return $this->getOwner()->shop->getOrdersSetting($this->orderNumType().'NumPrefix');
    }      
    /**
     * Get the order number separator
     */
    public function orderNumSeparator()
    {
        return $this->getOwner()->shop->getOrdersSetting($this->orderNumType().'NumSeparator');
    }          
    /**
     * Get the order number template
     */
    public function orderNumTemplate()
    {
        return $this->getOwner()->shop->getOrdersSetting($this->orderNumType().'NumTemplate');
    }          
    /**
     * Get the order number counter
     */
    public function orderNumCounter()
    {
        return $this->getOwner()->shop->getOrdersSetting($this->orderNumType().'NumCounter');
    }         
    /**
     * Update the order number counter
     * Create one if record does not exist
     */
    public function updateOrderNumCounter($counter)
    {
        $key = $this->orderNumType().'NumCounter';
        $ordersSettings = json_decode($this->getOwner()->shop->settings->orders,true);
        if ($ordersSettings==null)
            $ordersSettings = [];//new record
        $ordersSettings[$key] = $counter;//only override counter
        $this->getOwner()->shop->settings->orders = json_encode($ordersSettings);//json encode back, existing setting remain intact
        Yii::app()->serviceManager->shopManager->updateSettings($this->getOwner()->shop->account_id,$this->getOwner()->shop->settings,'orders');
        logTrace(__METHOD__." ok",$ordersSettings);
    }     
    /**
     * @return string either po or so
     */
    protected function orderNumType()
    {
        if ($this->getOwner() instanceof ShippingOrder)
            return 'so';
        else //if ($this->getOwner() instanceof Order)
            return 'po';
    }
    /**
     * To search into purchase order or shipping order items
     * @param type $itemName
     * @param type $modelFilter
     * @return type
     */
    public function constructItemsInCondition($itemName,$modelFilter='mine')
    {
        if (empty($itemName))
            return null;
        $orders = new CList();
        $itemCriteria=new CDbCriteria;
        $itemCriteria->select='order_id';
        $itemCriteria = QueryHelper::parseLocaleNameSearch($itemCriteria, 'name', $itemName);
        $itemCriteria->mergeWith(Item::model()->{$modelFilter}()->getDbCriteria());
        logTrace(__METHOD__,$itemCriteria);
        
        $items = Item::model()->findAll($itemCriteria);
        foreach ($items as $item)
            $orders->add($item->order_id); 
        
        if ($this->getOwner() instanceof ShippingOrder)
            return QueryHelper::constructInCondition('order_id',$orders);
        else 
            return QueryHelper::constructInCondition('id',$orders);
    }         
    /**
     * To search into purchase order by shop name
     * @param type $shopName
     * @param type $modelFilter
     * @return type
     */
    public function constructShopsInCondition($shopName,$modelFilter='mine')
    {
        if (empty($shopName))
            return null;
        $orders = new CList();
        $shopCriteria=new CDbCriteria;
        $shopCriteria->select='id';
        $shopCriteria = QueryHelper::parseLocaleNameSearch($shopCriteria, 'name', $shopName);
        logTrace(__METHOD__,$shopCriteria);
        
        $items = Shop::model()->findAll($shopCriteria);
        foreach ($items as $item)
            $orders->add($item->id); 
        
        return QueryHelper::constructInCondition('shop_id',$orders);
    }         
}
