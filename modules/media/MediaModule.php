<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MediaModule
 *
 * @author kwlok
 */
class MediaModule extends SModule
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
        return array(
            'assetloader' => array(
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'media',
                'pathAlias'=>'media.assets',
            ),
        );
    }
    /**
     * Module init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'media.components.*',
            'media.models.*',
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
            'views'=>[
                'profilesidebar'=>'accounts.profilesidebar',
            ],
            'images'=>[
                'loading'=>['common.assets.images'=>'loading.gif'],
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
                'class'=>'common.services.MediaManager',
                'model'=>['Media'],
                'htmlError'=>true,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}