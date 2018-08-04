<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of NotificationsModule
 *
 * @author kwlok
 */
class NotificationsModule extends SModule 
{
    /**
     * @property string the default controller.
     */
    public $entryController = 'undefined';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'notifications',
                'pathAlias'=>'notifications.assets',
            ],
        ];
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'notifications.components.*',
            'notifications.models.*',
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'modules'=>[],
            'classes'=>[
                'gridview'=>'common.widgets.SGridView',
            ],
        ]);
              
        $this->defaultController = $this->entryController;
    }
    
}