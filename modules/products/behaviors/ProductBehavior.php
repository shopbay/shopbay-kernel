<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.products.behaviors.ProductBaseBehavior");
/**
 * Description of ProductBehavior
 *
 * @author kwlok
 */
class ProductBehavior extends ProductBaseBehavior 
{
    public function getBrandsArray($locale=null)
    {
        $data = new CMap();
        foreach (Brand::model()->mine()->locateShop($this->getOwner()->shop->id)->findAll() as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();
    }
    
    public function getCategoriesArray($locale=null)
    {
        $data = new CMap();
        foreach (Category::model()->mine()->locateShop($this->getOwner()->shop->id)->findAll() as $model) {
            $data->add($model->id,$model->toString($locale));
            foreach ($model->subcategories as $submodel) {
                $data->add($submodel->toKey(),$submodel->toString($locale));
            }
        }
        return $data->toArray();
    }
    /**
     * Return list of subcategories with grouping by Category.
     * However, Category is not selectable as a limitation when using html select OPTGROUP
     * @param type $locale
     * @return type
     */
    public function getSubcategoriesArray($locale=null)
    {
        $data = [];
        foreach (Category::model()->mine()->locateShop($this->getOwner()->shop->id)->findAll() as $model) {
            foreach ($model->subcategories as $submodel) {
                $data[] = ['id'=>$model->id.'.'.$submodel->id,
                           'text'=>$submodel->displayLanguageValue('name',$locale),
                           'group'=>$model->displayLanguageValue('name',$locale),
                          ];
            }
        }
        return CHtml::listData($data,'id','text','group');
    }    
    
    public function getShippingsArray($locale=null)
    {
        $data = new CMap();
        foreach (Shipping::model()->mine()->locateShop($this->getOwner()->shop->id)->findAll() as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();
    }      
    
    public function getBaseUrl($secure=false)
    {
        return $this->getOwner()->getShopUrl($secure).'/products';
    } 
    /**
     * This is public accessible url
     * @return type
     */
    public function getUrl($secure=false)
    {
        return $this->getOwner()->getBaseUrl($secure).'/'.$this->getOwner()->slug;
    }        

    public function getReturnUrl($secure=false)
    {
        return $this->getOwner()->getUrl($secure);
    }        

    public function getUrlForSocialMedia()
    {
        return $this->getOwner()->shop->isSocialMediaShareAllowed()?$this->getOwner()->url:null;        
    }    
    /**
     * To search into product shipping names
     * @param type $shippingName
     * @param type $shopId
     * @return type
     */
    public function constructShippingInCondition($shippingName,$shopId)
    {
        if (empty($shippingName))
            return null;
        //[1] Pickup all product shipping in products
        $productShippings = [];
        $productShippingCriteria = new CDbCriteria();
        $productShippingCriteria->select = 't.*'; 
        $productShippingCriteria->join = 'INNER JOIN '.Product::model()->tableName().' p ON p.shop_id = '.$shopId;
        $productShippingCriteria->condition = 't.product_id = p.id';
        logTrace(__METHOD__.' $productShippingCriteria',$productShippingCriteria);
        foreach (ProductShipping::model()->findAll($productShippingCriteria) as $ps){
            $key = $ps->product_id.'-'.$ps->shipping_id;//composite key
            $productShippings[$key] = $ps->shipping_id; 
        }
        
        if (empty($productShippings))
            return null;
        
        //[2] Search into all shop shippings first, and extract products match with the shipping name
        $products = new CList();
        $shippingCriteria = new CDbCriteria;
        $shippingCriteria->select = 'id';
        $shippingCriteria = QueryHelper::parseLocaleNameSearch($shippingCriteria, 'name', $shippingName);
        $shippingCriteria->mergeWith(Shipping::model()->mine()->locateShop($shopId)->getDbCriteria());
        logTrace(__METHOD__.' $shippingCriteria',$shippingCriteria);
        foreach (Shipping::model()->findAll($shippingCriteria) as $s){
            foreach ($productShippings as $key => $value) {
                if ($s->id==$value){
                    $product = explode('-', $key);
                    $products->add($product[0]); 
                }
            }
        }
        
        return QueryHelper::constructInCondition('id',$products);
    }       
}
