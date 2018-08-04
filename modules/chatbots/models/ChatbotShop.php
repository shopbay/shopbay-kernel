<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.*');
Yii::import('common.modules.notifications.models.Notification');
Yii::import('common.modules.notifications.models.NotificationScope');
Yii::import('common.modules.notifications.models.NotificationSubscription');
Yii::import('common.modules.tasks.models.Process');
/**
 * Description of ChatbotShop
 *
 * @author kwlok
 */
class ChatbotShop extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'Shop';
    }    
    /**
     * Get shop navigation menu
     * @see getMainMenu::getMainMenu()
     * @see ShopNavigation for menu format
     * [ 
     *   'id'=>'',
     *   'type'=>'',
     *   'heading'=>[<locale values>],
     *   'url'=>''
     *   'items'=>[]//submenus
     * ]
     * @return array
     */
    public function getNavMenu()
    {
        $navMenu = $this->model->mainMenu;
        if (!isset($navMenu)){//no data found, use back default main menu
            $navMenu = $this->model->defaultMainMenu;
        }
        return json_decode($navMenu,true);
    }
    /**
     * Get shop news
     * @return array 
     */
    public function searchNews($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotNews', 'searchNews',[Process::NEWS_ONLINE,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop brands
     * @return array 
     */
    public function searchBrands($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotBrand', 'searchBrands', [null,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get products by brand
     * @param type $brand
     * @param type $currentPage
     * @param type $pageSize
     * @return type
     */
    public function searchProductsByBrand($brand,$currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchProductsByBrand', [$brand,Process::PRODUCT_ONLINE,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop categories
     * @return array 
     */
    public function searchCategories($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotCategory', 'searchCategories', [null,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get products by category
     * @param type $categoryKey
     * @param type $currentPage
     * @param type $pageSize
     * @return type
     */
    public function searchProductsByCategory($categoryKey,$currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchProductsByCategory', [$categoryKey,Process::PRODUCT_ONLINE,null,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop products
     * @return array 
     */
    public function searchProducts($query,$currentPage,$pageSize)
    {
        if ($this->searchMethod==self::ELASTIC_SEARCH)
            return $this->searchProductsByES($query, $pageSize);
        else {
            $criteria = new CDbCriteria();
            if (isset($query)){
                $criteria->compare('name', $this->encodeQuery($query,false), true);//encoding escape is handled by CDbCriteria
                logInfo(__METHOD__.' query criteria',$criteria);
            }
            return $this->searchProductsByDB($criteria, $currentPage,$pageSize);
        }
    }
    /**
     * Get shop products created for last n hours
     * @param integer $hours the last n hours (production creation)
     * @return array 
     */
    public function searchLastestProducts($hours,$currentPage,$pageSize)
    {
        if (!isset($hours))
            $hours = 24;//default to last 24 hours

        $criteria = new CDbCriteria();
        $criteria->condition = "create_time > UNIX_TIMESTAMP(NOW() - INTERVAL $hours HOUR)";
        logInfo(__METHOD__.' criteria',$criteria);
        return $this->searchProductsByDB($criteria, $currentPage,$pageSize);
    }
    /**
     * Search shop products by elasticsearch
     * TODO: support pagination
     * @return array Array of ProductTemplates
     */
    protected function searchProductsByES($query,$pageSize)
    {
        $products = [];
        $elasticsearch = new ProductSearch($this->id);
        if (!isset($query)){
            $query = '*';//wildcard search
        }
        $dataProvider = $elasticsearch->search($this->encodeQuery($query), null, $pageSize);
        foreach ($dataProvider->rawData as $data) {
            $model = new ChatbotProduct();
            $model->setModel($data->model);
            $products[] = $model;
        }
        return $products;
    }    
    /**
     * Search shop products by database
     * @return array Array of ProductTemplates
     */
    protected function searchProductsByDB($criteria,$currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchProducts', [Process::PRODUCT_ONLINE,$criteria,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }    
    /**
     * Encode query to make it searchable in utf-8
     * @param type $query
     * @return type
     */
    protected function encodeQuery($query,$escape=true)
    {
        $result = Helper::leftRightTrim(json_encode($query),'"','"');
//        if ($escape)
//            $result = str_replace('\\\\', '\\', $result);
//        
//        logInfo(__METHOD__.' result',$result);
        return $result;
    }
    /**
     * Get recently liked products
     * @return array 
     */
    public function searchRecentlyLikedProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchRecentLikedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get recently purchased products
     * @return array 
     */
    public function searchRecentlyPurchasedProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchRecentPurchasedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get recently discussed products
     * @return array 
     */
    public function searchRecentlyDiscussedProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchRecentDiscussedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get most likes products
     * @return array 
     */
    public function searchMostLikesProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchMostLikedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }    
    /**
     * Get most purchased products
     * @return array 
     */
    public function searchMostPurchasedProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchMostPurchasedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get most discussed products
     * @return array 
     */
    public function searchMostDiscussedProducts($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotProduct', 'searchMostDiscussedProducts', [$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Check if shop has active campaign sale promotion
     * @return type
     */
    public function getHasCampaignSale()
    {
        return $this->model->getCampaign()!=null;
    }
    /**
     * Get the shop active campaign sale promotion
     * @return type
     */
    public function getCampaignSale()
    {
        $model = new ChatbotCampaignSale();
        $model->setModel($this->model->getCampaign());
        return $model;
    }
    /**
     * Get the bga campaigns
     * @return array 
     */
    public function searchCampaignBgas($currentPage,$pageSize)
    {
        $exceptOfferXOnly = false;
        return $this->searchModelTemplate('ChatbotCampaignBga', 'searchCampaignBgas', [Process::CAMPAIGN_ONLINE,$exceptOfferXOnly,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop shippings
     * @return array 
     */
    public function searchShippings($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotShipping', 'searchShippings',[Process::SHIPPING_ONLINE,null,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop payment methods
     * @return array 
     */
    public function searchPaymentMethods($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotPaymentMethod', 'searchPaymentMethods',[Process::PAYMENT_METHOD_ONLINE,null,$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Get shop subscribed notifications
     * @return array 
     */
    public function searchNotificationSubscriptions($context,$account_id,$currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotNotificationSubscription', 'searchNotificationSubscriptions',[Notification::$typeMessenger,$this->getSubscriber($context,$account_id),$pageSize],$this->constructPagination($currentPage, $pageSize));
    }
    /**
     * Subscribe to notification
     * @param type $notification
     * @param type $context
     * @param type $account_id optional
     */
    public function subscribeNotification($notification,$context,$account_id=null)
    {
        $scope = new NotificationScope($this->id,$this->modelClass);//set to shop level scope
        Yii::app()->serviceManager->notificationManager->subscribe($this->getSubscriber($context,$account_id),$notification,$scope,Notification::$typeMessenger);
    }
    /**
     * Unsubscribe notification
     * @param type $notification
     * @param type $context
     */
    public function unsubscribeNotification($notification,$context)
    {
        $scope = new NotificationScope($this->id,$this->modelClass);//set to shop level scope
        Yii::app()->serviceManager->notificationManager->unsubscribe($this->getSubscriber($context),$notification,$scope,Notification::$typeMessenger);
    }
    /**
     * Parsing subscriber with capability to detect a login account 
     * @param type $context
     * @param type $account_id
     * @return type
     */
    protected function getSubscriber($context,$account_id=null)
    {
        if (!isset($account_id) && Yii::app()->user->hasSessionId($context))
            $account_id = Yii::app()->user->getSessionAccount($context);
            
        if (isset($account_id) && $account_id!=Account::GUEST)
            return json_encode(['account_id'=>$account_id,'account_messenger'=>$context->sender]);
        else
            return json_encode(['messenger'=>$context->sender]);
    }
    
}
