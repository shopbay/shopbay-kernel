<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of NewsModule
 *
 * @author kwlok
 */
class NewsModule extends SModule 
{
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [];
    }   
    
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'news.behaviors.*',
            'news.models.*',
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
                'class'=>'common.services.NewsManager',
                'model'=>'News',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
}