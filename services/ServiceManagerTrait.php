<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ServiceManagerTrait
 *
 * @author kwlok
 */
trait ServiceManagerTrait 
{
    /**
     * @return component
     */
    public function createComponent($config,$init=true,$load=false)
    {
        $component = Yii::createComponent($config);
        if ($init)
            $component->init();
        if ($load)
            $component->load();
        return $component;
    }     
    /**
     * @return WorkflowManager
     */
    public function getWorkflowManager()
    {
        return $this->createComponent('common.services.WorkflowManager');
    }  
    /**
     * @return NotificationManager
     */
    public function getNotificationManager()
    {
        return $this->createComponent('common.services.NotificationManager');
    }  
    /**
     * @return PaymentManager
     */
    public function getPaymentManager()
    {
        return $this->createComponent('common.services.PaymentManager');
    }  
    /**
     * @return InventoryManager
     */
    public function getInventoryManager()
    {
        return $this->createComponent('common.services.InventoryManager');
    }  
    /**
     * @return CampaignManager
     */
    public function getCampaignManager()
    {
        return $this->createComponent('common.services.CampaignManager');
    }  
    /**
     * @return OrderManager
     */
    public function getOrderManager()
    {
        return $this->createComponent([
                'class'=>'common.services.OrderManager',
                'model'=>['Order','ShippingOrder'],
            ]);
    }  
    /**
     * @return ShippingManager
     */
    public function getShippingManager()
    {
        return $this->createComponent('common.services.ShippingManager');
    }  
    /**
     * @return TaxManager
     */
    public function getTaxManager()
    {
        return $this->createComponent('common.services.TaxManager');
    }      
    /**
     * @return AnalyticManager
     */
    public function getAnalyticManager()
    {
        return $this->createComponent('common.services.AnalyticManager');
    }  
    /**
     * Return customer manager
     * @return CustomerManager
     */
    public function getCustomerManager()
    {
        return $this->createComponent('common.services.CustomerManager');
    }
    /**
     * @return WizardManager
     */
    public function getWizardManager()
    {
        return $this->createComponent('common.services.WizardManager');
    }
    /**
     * @return ActiveCart
     */
    public function getCart()
    {
        return $this->createComponent('common.modules.carts.components.ActiveCart', true, true);
    }         
    /**
     * @return SubscriptionManager
     */
    public function getSubscriptionManager()
    {
        return Yii::app()->serviceManager->createComponent([
            'class'=>'common.services.SubscriptionManager',
            'model'=>'Subscription',
        ]);
    }
    /**
     * @return ShopManager
     */
    public function getShopManager()
    {
        return Yii::app()->serviceManager->createComponent([
            'class'=>'common.services.ShopManager',
            'model'=>'Shop',
        ]);
    } 
    /**
     * @return MediaManager
     */
    public function getMediaManager()
    {
        return Yii::app()->serviceManager->createComponent([
            'class'=>'common.services.MediaManager',
            'model'=>'Media',
        ]);
    }     
    /**
     * @return ChatbotManager
     */
    public function getChatbotManager()
    {
        return Yii::app()->serviceManager->createComponent([
            'class'=>'common.services.ChatbotManager',
            'model'=>'Chatbot',
        ]);
    }     
    /**
     * @return MediaStorage
     */
    public function getMediaStorage()
    {
        return Yii::app()->serviceManager->createComponent([
            'class'=>'common.modules.media.components.MediaStorage',
        ]);        
    }     
    /**
     * @return ReceiptManager
     */
    public function getReceiptManager()
    {
        return $this->createComponent('common.services.ReceiptManager');
    }      
}
