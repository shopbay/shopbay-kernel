<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CustomersModule
 *
 * @author kwlok
 */
class CustomersModule extends SModule 
{ 
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'customers',
                'pathAlias'=>'customers.assets',
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
            'customers.models.*',
            'customers.components.*',
            'common.widgets.SButtonColumn',
            'common.widgets.spageindex.controllers.SPageIndexController',
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'modules'=>[],
            'classes'=>[
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
            ],
            'images'=>[
                'datepicker'=>['common.assets.images'=>'datepicker.gif'],
            ],            
        ]);             

        $this->defaultController = 'management';

        $this->registerScripts();
    }
    /**
     * Module display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Customer|Customers',[$mode]);
    }    
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.CustomerManager',
                'model'=>['Customer','CustomerAccount'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
}