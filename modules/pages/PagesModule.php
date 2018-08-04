<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PagesModule
 *
 * @author kwlok
 */
class PagesModule extends SModule 
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
     * @property string the default controller.
     */
    public $entryController = 'management';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'pages',
                'pathAlias'=>'pages.assets',
            ],
        ];
    }
    /**
     * Module init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'pages.models.*',
            'pages.components.*',
            'pages.widgets.pagelayouteditor.PageLayoutEditor',
            'common.modules.shops.controllers.ShopParentController',
            'common.widgets.SButtonColumn',
            'common.widgets.spageindex.controllers.SPageIndexController',
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'modules'=>[
                'tasks'=>[
                    'common.modules.tasks.actions.TransitionAction',
                    'common.modules.tasks.models.*',
                ],    
            ],
            'classes'=>[
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
            ],
            'images'=>[
                'datepicker'=> ['common.assets.images'=>'datepicker.gif'],                
            ],
        ]);             

        $this->defaultController = $this->entryController;

        //load layout and common css/js files
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
                'class'=>'common.services.PageManager',
                'model'=>['Page','PageLayout'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}