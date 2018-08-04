<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SearchModule
 * 
 * This modules requires both Yii 1.x and 2.x framework to work
 * As it uses Yii 2.x \yii\yii2-elasticsearch
 *
 * @author kwlok
 */
class SearchModule extends SModule 
{
    /**
     * @property boolean Indicate if to load module assets. Default to "true"
     */    
    public $loadAssets = true;
    /**
     * @property string the default controller.
     */
    public $entryController = 'DefaultController';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        if ($this->loadAssets)
            return array(
                'assetloader' => array(
                    'class'=>'common.components.behaviors.AssetLoaderBehavior',
                    'name'=>'search',
                    'pathAlias'=>'search.assets',
                ),
            );
        else
            return array();
    }
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport(array(
            'search.components.*',
            'search.models.*',
            'common.widgets.SListView',
        ));
        
        // import module dependencies classes
        $this->setDependencies(array(
            'modules'=>array(),
            'views'=>array(
                'default'=>'common.modules.search.views.default.index',
                'searchbar'=>'common.modules.search.views.default._searchbar',
            ),             
        ));             

        $this->defaultController = $this->entryController;

        if ($this->loadAssets){

            //load layout and common css/js files
            $this->registerScripts();
            
            logTrace(__METHOD__.' assets registered');
        }
        
    }
    /**
     * @return ElasticSearch
     */
    public function getElasticSearch()
    {
        // Set the required components.
        $this->setComponents(array(
            'elasticsearch'=>array(
                'class'=>'common.modules.search.components.ElasticSearch',
            ),
        ));
        return $this->getComponent('elasticsearch');
    }
    
}