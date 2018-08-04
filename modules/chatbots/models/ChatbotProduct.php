<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotProduct
 *
 * @author kwlok
 */
class ChatbotProduct extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'Product';
    }
    /**
     * Get product attributes
     * @return array 
     */
    public function searchAttributes($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProductOption', 'searchAttributes',[$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get product shippings
     * @return array 
     */
    public function searchShippings($pageSize)
    {
        $result = [];
        //Get product shipping first
        $productShippings = new CMap();
        foreach ($this->model->shippings as $productShipping){
            $productShippings->add($productShipping->shipping_id,$productShipping->getSurcharge()); 
        }        
        $condition = QueryHelper::constructInCondition('id',$productShippings->getKeys());
        //Search into Shipping
        foreach(Shipping::model()->searchActive($condition,$pageSize)->data as $shipping){
            $model = new ChatbotShipping();
            $model->setModel($shipping);
            $model->setSurcharge($productShippings->itemAt($shipping->id));
            $result[] = $model;
        }
        return $result;
    }    
    /**
     * Get product price
     * @return boolean $currency If to include currency
     */
    public function getPrice($currency=true)
    {
        return $this->getChargeValue($this->model->price, $currency);
    }
}
