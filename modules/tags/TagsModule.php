<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TagsModule
 *
 * @author kwlok
 */
class TagsModule extends SModule 
{
    /**
     * Module init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'tags.models.*',
            'tags.components.*',
        ]);
    }
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.TagManager',
                'model'=>['Tag'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}