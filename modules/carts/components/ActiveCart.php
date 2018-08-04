<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.carts.components.CartBase");
Yii::import("common.modules.carts.behaviors.CartItemTotalBehavior");
Yii::import("common.modules.carts.behaviors.CartItemOptionBehavior");
Yii::import("common.modules.carts.models.Cart");
/**
* ActiveCart class to house cart session objects and also from DB (previous sessions).
*
* @author kwlok
*/
class ActiveCart extends CartBase 
{
    public $onPreview = false;
    /**
    * Initializes the ActiveCart.
    */
    public function init() 
    {
        parent::init();
    }         
    /**
     * Load cart from previous session or db
     * @see ServiceManager::createComponent()
     */
    public function load() 
    {
       $models = Cart::model()->mine()->incomplete()->findAll(); 
       foreach($models as $model) {
            $item = new CartItemForm('addCart');
            $item->attributes = $model->getAttributes([
                'shop_id','product_id','shipping_id','itemkey',
                'name','unit_price','quantity','weight',
                'option_fee','shipping_surcharge','currency','weight_unit',
            ]);
            $item->pkey = base64_encode($item->product_id);
            //set campaign data
            $item->campaign_id = $model->getCampaignId();
            $item->setCampaignItem($model->getCampaignItem());
            $item->setAffinityKey($model->getAffinityKey());
            //set option data
            $item->options = json_decode($model->options);
            //logTrace(__METHOD__,$item->attributes);
            //check if any cart items before login
            //newly added item in cart by default is checkout=true, and quantity follow newly added quantity
            foreach ($this->getItems() as $new){
                if ($item->getKey()==$new->getKey()){
                   $item->quantity = $new->getQuantity();  
                   $item->setCheckout($new->getCheckout());           
                }
                else {
                    //even not found, always set to item checkout to be true by default
                    $item->setCheckout(true);
                }
            }
            $this->update($item,$item->quantity);
        }
    }    
    /**
     * Remove cart item
     * @return array
     */
    public function removeItem($key) 
    {
        $removeList = new CList();
        $removeList->add($key);
        //remove main item
        $this->remove($key);  
        //remove affinity item (campaign item) also
        $affinityItem = $this->getAffinityItem($key);
        if ($affinityItem!=false){
            $removeList->add($affinityItem->getKey());
            $this->remove($affinityItem->getKey());  
        }
        //remove item from db
        $criteria = new CDbCriteria();
        $criteria->addInCondition('itemkey', $removeList->toArray());//need to put \'key\' as key is reserved word in mysql
        $criteria->mergeWith(Cart::model()->mine()->incomplete()->getDbCriteria());
        logTrace(__METHOD__.' criteria',$criteria->toArray());
        Cart::model()->deleteAll($criteria); 
    } 
    /**
     * Remove all cart items (including those incomplete but persisted in db)
     * @return array
     */
    public function removeAllItems() 
    {
        $this->clearState();
        $criteria = new CDbCriteria();
        $criteria->mergeWith(Cart::model()->mine()->incomplete()->getDbCriteria());
        logTrace(__METHOD__.' criteria',$criteria->toArray());
        Cart::model()->deleteAll($criteria); 
        user()->setCartCount(null);
    }     
    /**
     * Returns array all cart items
     * @return array
     */
    public function removeCheckoutItems($shop,$keepRecord) 
    {
        if (!$keepRecord)
            $this->_removeRecord();
        $this->clearCheckoutItems($shop);
    } 
    /**
     * 
     * Removes position from the shopping cart of key
     * @param mixed $key
     */
    private function _removeRecord() 
    {
        //remove also from db
        foreach ($this->_m as $item){
            $model = Cart::model()->mine()->complete()->findByAttributes(['itemkey'=>$item->getKey()]); 
            if ($model!=null) {
                $model->delete();
                logTrace('cart record removed',$model->getAttributes());
            }
        }
    }

}