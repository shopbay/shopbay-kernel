<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LikesModule
 *
 * @author kwlok
 */
class LikesModule extends SModule 
{
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'likes',
                'pathAlias'=>'likes.assets',
            ],
        ];
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'likes.models.*',
            'common.widgets.spageindex.controllers.SPageIndexController',
        ]);

        // import module dependencies classes
        $this->setDependencies([
            'modules'=>[
                'tutorials'=>[
                    'common.modules.tutorials.models.Tutorial',
                ],
                'questions'=>[
                    'common.modules.questions.models.Question',
                ],
            ],
            'classes'=>[
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
            ],
            'views'=>[         
                'fan'=>'common.modules.likes.views.management._fan',
                'button'=>'common.modules.likes.views.management._button',
                'buttonform'=>'common.modules.likes.views.management._buttonform',
                'profilesidebar'=>'accounts.profilesidebar',
            ],
            'images'=>[
                'like.png'=>['common.assets.images'=>'like.png'],
                'dislike.png'=>['common.assets.images'=>'dislike.png'],
            ],
        ]);  

        $this->defaultController = 'management';

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
                'class'=>'common.services.LikeManager',
                'model'=>'Like',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}
