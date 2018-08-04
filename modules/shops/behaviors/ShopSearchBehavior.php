<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopSearchBehavior
 *
 * @author kwlok
 */
class ShopSearchBehavior extends CActiveRecordBehavior 
{  
    public function searchRecently($limit=1,$pagination=false)
    {
        $finder = $this->getOwner()->recently($limit);
        
        //logTrace(__METHOD__,$finder->getDbCriteria());
        
        return new CActiveDataProvider($finder, array(
            'criteria'=>$finder->getDbCriteria(),
            'pagination'=>$pagination?array('pageSize'=>$limit):false,
        ));
    }  
    
    public function searchProducts($status=null,$extraCriteria=null,$pageSize=null)
    {
        return $this->_searchShopObjects('Product', $status, $extraCriteria, $pageSize);
    }    
        
    public function searchPaymentMethods($status=null,$extraCriteria=null,$pageSize=null)
    {
        return $this->_searchShopObjects('PaymentMethod', $status, $extraCriteria, $pageSize);
    }  
    
    public function searchShippings($status=null,$extraCriteria=null,$pageSize=null)
    {
        return $this->_searchShopObjects('Shipping', $status, $extraCriteria, $pageSize);
    }   
    
    public function searchZones($extraCriteria=null)
    {
        return $this->_searchShopObjects('Zone', null, $extraCriteria);
    } 
    
    public function searchCategories($extraCriteria=null,$pageSize=null)
    {
        return $this->_searchShopObjects('Category', null, $extraCriteria, $pageSize);
    } 
    
    public function searchBrands($extraCriteria=null,$pageSize=null)
    {
        return $this->_searchShopObjects('Brand', null, $extraCriteria, $pageSize);
    } 
    
    public function searchNews($status=null,$pageSize=null)
    {
        $extraCriteria = new CDbCriteria();
        $extraCriteria->order = 'create_time DESC';
        return $this->_searchShopObjects('News', $status, $extraCriteria, $pageSize);
    }

    public function retrieveNews($newsId)
    {
        return News::model()->locateShop($this->getOwner()->id)->active()->findByPk($newsId);
    }
    
    public function searchQuestions($status=null,$pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>Shop::model()->tableName()));
        $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
        $criteria->order = 'question_time DESC';
        if (isset($status))
            $criteria->addColumnCondition(array('status'=>$status));
        return new CActiveDataProvider(Question::model(),array(
                    'criteria'=>$criteria,
                    'pagination'=>array('pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page')),
                ));
    }    

    public function searchCampaignBgas($status=null,$exceptOfferXOnly=true,$pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'create_time DESC';
        $criteria->addColumnCondition(array('shop_id'=>$this->getOwner()->id));
        if (isset($status))
            $criteria->addColumnCondition(array('status'=>$status));
        
        $finder = CampaignBga::model()->notExpired();
        if ($exceptOfferXOnly)
            $finder = $finder->exceptOfferBuyXOnly();
        $finder->getDbCriteria()->mergeWith($criteria);
        //logTrace(__METHOD__.' criteria',$finder->getDbCriteria());
        
        return new CActiveDataProvider(CampaignBga::model(),array(
                    'criteria'=>$finder->getDbCriteria(),
                    'pagination'=>array('pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('bga_per_page')),
                ));
    }
    
    public function searchRecentLikedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.*'; 
        $criteria->join = 'INNER JOIN '.Like::model()->tableName().' s ON t.id = s.obj_id and t.shop_id = s.obj_src_id';
        $criteria->condition = 's.status=\''.Process::YES.'\'';
        $criteria->order = 's.update_time desc';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }    
    
    public function searchMostLikedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.*, count(t.id) most_counter'; 
        $criteria->join = 'INNER JOIN '.Like::model()->tableName().' s ON t.id = s.obj_id and t.shop_id = s.obj_src_id';
        $criteria->condition = 's.status=\''.Process::YES.'\'';
        $criteria->group = 't.id';
        $criteria->order = 'most_counter desc';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }    
    
    public function searchRecentDiscussedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->distinct = true; 
        $criteria->select = 't.*, b.create_time comment_time'; 
        $criteria->join = 'INNER JOIN (SELECT c.obj_id, c.create_time FROM '.Comment::model()->tableName().' c WHERE c.obj_type = \''.Product::model()->tableName().'\' ORDER BY c.create_time DESC) b on b.obj_id = t.id';
        $criteria->group = 't.id';
        $criteria->order = 'comment_time desc';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }
    
    public function searchMostDiscussedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->distinct = true; 
        $criteria->select = 't.*, b.most_counter'; 
        $criteria->join = 'INNER JOIN (SELECT c.obj_id, count(c.obj_id) most_counter FROM '.Comment::model()->tableName().' c WHERE c.obj_type = \''.Product::model()->tableName().'\' GROUP BY c.obj_id ORDER BY most_counter desc) b on b.obj_id = t.id';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }
    
    public function searchRecentPurchasedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->distinct = true; 
        $criteria->select = 't.*, b.create_time purchase_time'; 
        $criteria->join = 'INNER JOIN (SELECT c.product_id, c.shop_id, c.create_time FROM '.Item::model()->tableName().' c ORDER BY c.create_time DESC) b on b.product_id = t.id and b.shop_id = t.shop_id';
        $criteria->group = 't.id';
        $criteria->order = 'purchase_time desc';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }    
    
    public function searchMostPurchasedProducts($pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->distinct = true; 
        $criteria->select = 't.*, b.most_counter'; 
        $criteria->join = 'INNER JOIN (SELECT c.product_id, c.shop_id, count(c.product_id) most_counter FROM '.Item::model()->tableName().' c GROUP BY c.product_id ORDER BY most_counter desc) b on b.product_id = t.id and b.shop_id = t.shop_id';
        return $this->getOwner()->searchProducts(Process::PRODUCT_ONLINE,$criteria,$pageSize);
    }    
    /**
     * Search product by category
     * @param type $categoryKey This key follows the format as described in @see CategorySub::toKey() and parseKey()
     * @return \CActiveDataProvider
     */
    public function searchProductsByCategory($categoryKey,$productStatus,$otherCriteria=null,$pageSize=null)
    {
        try {
            $key = CategorySub::model()->parseKey($categoryKey);
        } catch (CException $ex) {
            logWarning(__METHOD__.' category key not found',$ex->getMessage());
            $key = [-9];//set to something to make the search return zero records
        }
        
        if (isset($key[0]) && isset($key[1])){
            $query = "categories.category_id=$key[0] AND categories.subcategory_id=$key[1]";
        }
        else{
            $query = "categories.category_id=$key[0]";
        }

        $criteria = new CDbCriteria();
        $criteria->order = 't.create_time DESC';
        $criteria->together = true;
        $criteria->with = array(
            'categories'=>array(
                'select'=>false,// we don't want to select categories
                'joinType'=>'INNER JOIN',
                'condition'=>$query,
            ),
        );
        if (isset($otherCriteria))
            $criteria->mergeWith($otherCriteria);

        return $this->getOwner()->searchProducts($productStatus,$criteria,$pageSize);
    }        
    
    public function searchProductsByBrand($brandId,$productStatus,$pageSize=null)
    {
        if ($brandId==null)
            $brandId = -99;//set to a non-existence value
        $criteria = new CDbCriteria();
        $criteria->order = 't.create_time DESC';
        $criteria->condition = 't.brand_id='.$brandId; 
        return $this->getOwner()->searchProducts($productStatus,$criteria,$pageSize);
    }        
    /**
     * A boilerplate for shop objects search
     * @param string $objModelClass
     * @param string $objStatus If the object has status field; Optional
     * @param CDbCriteria $extraCriteria
     * @param string $pageSize 
     * @return \modelClass
     */
    private function _searchShopObjects($objModelClass,$objStatus=null,$extraCriteria=null,$pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 't.shop_id='.$this->getOwner()->id;
        if (isset($objStatus))
            $criteria->condition .= ' and t.status=\''.$objStatus.'\'';

        if (isset($extraCriteria))
            $criteria->mergeWith($extraCriteria);

        //logTrace(__METHOD__.' criteria',$criteria);
        return new CActiveDataProvider($objModelClass,[
            'criteria'=>$criteria,
            'pagination'=>[
                'pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page'),
            ],
        ]);
    }
    /**
     * Search subscribed notification of this shop
     * @param type $channel
     * @param type $subscriber
     * @param type $pageSize
     * @return \CActiveDataProvider
     */
    public function searchNotificationSubscriptions($channel,$subscriber,$pageSize=null)
    {
        Yii::import('common.modules.notifications.models.NotificationSubscription');
        Yii::import('common.modules.notifications.models.NotificationScope');
        $scope = new NotificationScope($this->getOwner()->id,get_class($this->getOwner()));//set to shop level scope
        $finder = NotificationSubscription::model()->subscribed()->notifyBy($channel)->scopeBy($scope,$subscriber);
        return new CActiveDataProvider($finder, [
            'criteria'=>$finder->getDbCriteria(),
            'pagination'=>array('pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page')),
        ]);
    }    
}
