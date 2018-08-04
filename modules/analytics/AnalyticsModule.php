<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AnalyticsModule
 *
 * @author kwlok
 */
class AnalyticsModule extends SModule 
{
    /**
     * parentShopModelClass (model classname) means that products module needs to be attached to shop module 
     * as all products objects creation/update is assuming having shop_id in session 
     * 
     * parentShopModelClass (null or false) means that products module needs to define which shop products objects 
     * belongs to during creation/update 
     * 
     * @see SActiveSession::SHOP_ACTIVE
     * @property boolean whether parentShopModelClass is required.
     */
    public $parentShopModelClass = 'Shop';    
    /**
     * @property default dashboard controller behavior to load in welcome view.
     */
    public $dashboardControllerBehavior;
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'analytics',
                'pathAlias'=>'analytics.assets',
            ],
        ];
    }
    /**
     * Init module
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'analytics.components.*',
            'analytics.models.*',
            'analytics.charts.*',
            'analytics.widgets.*',
            'analytics.widgets.chart.Chart',
            'common.widgets.spageindex.controllers.SPageIndexController',            
            'common.modules.shops.controllers.ShopParentController',
        ]);

        // import module dependencies classes
        $this->setDependencies([
            'modules'=>[],
            'classes'=>[
                'listview'=>'common.widgets.SListView',     
            ],
            'views'=>[
                'widget'=>'common.modules.analytics.views.management._widget',
            ],
            'images'=>[
                   //'<imageKey>'=>array('<pathAlias>'=>'<imageFile>'),
            ],
        ]);  
        
        $this->defaultController = 'management';

        $this->registerScripts();

    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.AnalyticManager',
                'model'=>'Metric',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}