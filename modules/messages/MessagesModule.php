<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of MessagesModule
 *
 * @author kwlok
 */
class MessagesModule extends SModule 
{
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return array(
            'assetloader' => array(
            'class'=>'common.components.behaviors.AssetLoaderBehavior',
            'name'=>'messages',
            'pathAlias'=>'messages.assets',
        ));
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport(array(
            'messages.models.*',
            'common.widgets.spageindex.controllers.SPageIndexController',
        ));

        // import module dependencies classes
        $this->setDependencies(array(
            'modules'=>array(),
            'classes'=>array(
                'listview'=>'common.widgets.SListView',
            ), 
            'views'=>array(
                'recent'=>'common.modules.messages.views.management._recent',
            ),
        ));  

        $this->defaultController = 'management';

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
                'class'=>'common.services.MessageManager',
                'model'=>'Message',
                'ownerAttribute'=>'recipient',
            ),
        ));
        return $this->getComponent('servicemanager');
    }
}