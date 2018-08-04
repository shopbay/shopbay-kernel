<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TicketsModule
 *
 * @author kwlok
 */
class TicketsModule extends SModule 
{
    /**
     * @property boolean If to run module as administrator
     */
    public $runAsAdmin = false;
    /**
     * @property boolean If to show shop field in ticket form
     */
    public $enableShopField = false;
    /**
     * @property string the default controller.
     */
    public $entryController = 'management';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return array(
            'assetloader' => array(
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'tickets',
                'pathAlias'=>'tickets.assets',
            ),
        );
    }
    /**
     * Module init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport(array(
            'tickets.models.*',
            'tickets.components.*',
            'common.widgets.SButtonColumn',
            'common.widgets.spageindex.controllers.SPageIndexController',
        ));
        // import module dependencies classes
        $this->setDependencies(array(
            'modules'=>array(
                'tasks'=>array(
                    'common.modules.tasks.actions.TransitionAction',
                    'common.modules.tasks.models.*',
                ),    
            ),
            'classes'=>array(
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
            ),
            'views'=>array(
                'profilesidebar'=>'accounts.profilesidebar',
            ),
        ));             

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
        $this->setComponents(array(
            'servicemanager'=>array(
                'class'=>'common.services.TicketManager',
                'model'=>array('Ticket'),
            ),
        ));
        return $this->getComponent('servicemanager');
    }

}