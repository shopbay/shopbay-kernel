<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TutorialsModule
 *
 * @author kwlok
 */
class TutorialsModule extends SModule 
{
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
                'name'=>'tutorials',
                'pathAlias'=>'tutorials.assets',
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
            'tutorials.models.*',
            'tutorials.components.*',
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
                'tags'=>[
                    'common.modules.tags.models.Tag',
                ],                
            ],
            'classes'=>[
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
            ],
            'views'=>[
                'tutorialview'=>'common.modules.tutorials.views.management._view_body',
                'tutoriallist'=>'common.modules.tutorials.views.management._tutorial_listview',
                //tasks views
                'history'=>'tasks.processhistory',
                //accounts views
                'profilesidebar'=>'accounts.profilesidebar',
            ],
        ]);             

        $this->defaultController = $this->entryController;

        //load layout and common css/js files
        $this->registerScripts();
        
        //load process css
        $this->registerProcessCssFile();
    }
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.TutorialManager',
                'model'=>['Tutorial','TutorialSeries'],
                'runMode'=>$this->serviceMode,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}