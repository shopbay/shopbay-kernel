<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.orders.models.OrderItemForm");
Yii::import("common.modules.carts.components.CartData");
Yii::import("common.modules.carts.behaviors.CartItemOptionBehavior");
/**
 * CartItemForm
 *
 * @author kwlok 
 * @version 0.1
 * @package Cart
 */
class CartItemForm extends OrderItemForm 
{
    public $pkey;//base64 encoded product id
    public $ckey;//base64 encoded campaigin id
    public $addCartUrl;
    public $addCartScript = 'addcart($(this).attr(\'form\'));';
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),array(
            'itemoptionbehavior' => array(
                'class'=>'common.modules.carts.behaviors.CartItemOptionBehavior',
            ),              
        ));
    }  
    /**
     * Rules for this model
     */
    public function rules()
    {
        return array_merge(
            array(
                array('pkey', 'required','on'=>'addCart'),
                array('ckey', 'safe','on'=>'addCart'),
            ),
            parent::rules()
        );
    }
    public function assignAttributes($attributes)
    {
        logTrace(__METHOD__.' received data',$attributes);
        $this->attributes=$attributes;//INFO only safe attributes are captured by $model->attributes
        $this->product_id = base64_decode($this->pkey);
        if (isset($this->ckey))
            $this->campaign_id = base64_decode($this->ckey);
                
    }
    public function constructFormData()
    {
        $model = $this->getProductModel();
        //[1] set basic static data
        $this->shop_id = $model->shop->id;
        $this->name = $model->name;
        $this->unit_price = $model->unit_price;
        $this->currency = $model->getCurrency();
        $this->weight = $model->weight;
        $this->weight_unit = $model->getWeightUnit();
        //[2] set shipping id and shipping surcharge
        $this->assignShipping( $this->shipping_id);
        //[3] set sku
        $sku = Inventory::formatSKU($model->code, $this->options);
        if (!Yii::app()->serviceManager->getInventoryManager()->existsInventory($this->product_id,$sku))
            throw new CException(Sii::t('sii','Cart item sku {sku} not found',array('{sku}'=>$sku)));
        //[4] set options
        $this->assignOptions($this->options);
        //[5] Set item key 
        $this->itemkey = CartData::formatItemKey($sku, $this->shop_id, $this->shipping_id, $this->campaign_id);
        
        logInfo(__METHOD__.' ok', $this->getAttributes());   
    }
    /**
     * Assign shipping id and shipping surcharge if any
     * 
     * @see Product::getShippingsDataArray()/CampaignBga::getShippingsDataArray() for how shipping value is formatted 
     * @param type $encodedValue In format of shipping_id|shipping_surcharge
     */
    public function assignShipping($encodedValue)
    {
        $shipping = explode(Helper::PIPE_SEPARATOR, $encodedValue);
        $this->shipping_id = $shipping[0];
        $this->shipping_surcharge = $shipping[1];
    } 
   
}
