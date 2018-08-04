<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of MerchantBehavior
 * Requires AccountBehavior to be attached to owner
 *
 * @author kwlok
 */
class MerchantBehavior extends CActiveRecordBehavior 
{
   /**
    * @var string The name of the attribute to shop store for the merchant. Defaults to 'shop_id'
    */
    public $merchantAttribute = 'shop_id';
   /**
    * @var boolean If to use shop as proxy to retrieve merchant objects. Defaults to 'false'
    */
    public $shopProxy = false;
    
    public function merchant($shop_id=null) 
    {
        if ($this->shopProxy){
            if ($shop_id!=null)
                $condition = $this->merchantAttribute.'='.$shop_id;
            else {
                $list = new CList();
                foreach (Shop::model()->mine()->findAll() as $key => $value)
                    $list->add($value->id);
                $condition = QueryHelper::constructInCondition($this->merchantAttribute, $list->toArray());
            }
            $criteria = new CDbCriteria();
            $criteria->addCondition($condition);
            $this->getOwner()->getDbCriteria()->mergeWith($criteria);
            return $this->getOwner();
        }
        else if ($this->getOwner() instanceof Question){
            $productlist = new CList();
            $campaignlist = new CList();
            //[1] capture shop questions
            if ($shop_id!=null){
                $condition = $this->merchantAttribute.'='.$shop_id;
                foreach (Product::model()->mine()->findAll('shop_id='.$shop_id) as $key => $value)
                    $productlist->add($value->id);
                foreach (CampaignBga::model()->mine()->findAll('shop_id='.$shop_id) as $key => $value)
                    $campaignlist->add($value->id);
            }
            else {
                $shoplist = new CList();
                foreach (Shop::model()->mine()->findAll() as $key => $value){
                    $shoplist->add($value->id);
                    foreach (Product::model()->mine()->findAll('shop_id='.$value->id) as $key2 => $value2)
                        $productlist->add($value2->id);
                    foreach (CampaignBga::model()->mine()->findAll('shop_id='.$value->id) as $key2 => $value2)
                        $campaignlist->add($value2->id);
                }
                $condition = QueryHelper::constructInCondition($this->merchantAttribute, $shoplist->toArray());
            }
            $criteria = new CDbCriteria();
            $criteria->addCondition($condition);
            $criteria->addCondition('obj_type = \''.Shop::model()->tableName().'\'');
            $this->getOwner()->getDbCriteria()->mergeWith($criteria);
            //[2] capture product questions
            $criteria2 = new CDbCriteria();
            $condition2 = QueryHelper::constructInCondition($this->merchantAttribute, $productlist->toArray());
            $criteria2->addCondition($condition2);
            $criteria2->addCondition('obj_type = \''.Product::model()->tableName().'\'');
            $this->getOwner()->getDbCriteria()->mergeWith($criteria2,'OR');
            //[3] capture campaign questions
            $criteria3 = new CDbCriteria();
            $condition3 = QueryHelper::constructInCondition($this->merchantAttribute, $campaignlist->toArray());
            $criteria3->addCondition($condition3);
            $criteria3->addCondition('obj_type = \''.CampaignBga::model()->tableName().'\'');
            $this->getOwner()->getDbCriteria()->mergeWith($criteria3,'OR');
            
            //logTrace(__METHOD__.' criteria',$criteria);
            return $this->getOwner();
        }
        else
            return $this->getOwner()->mine();
    }
    public function locateShop($shop_id) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition($this->merchantAttribute.'='.$shop_id);
        //logTrace('MerchantBehavior.locateShop().criteria',$criteria);
        $this->getOwner()->getDbCriteria()->mergeWith($criteria);
        return $this->getOwner();
    }        
    public function locateProduct($product_id) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition($this->merchantAttribute.'='.$product_id);
        $this->getOwner()->getDbCriteria()->mergeWith($criteria);
        return $this->getOwner();
    }        
   
}