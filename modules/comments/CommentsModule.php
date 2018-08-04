<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CommentsModule
 *
 * @author kwlok
 */
class CommentsModule extends SModule 
{
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'comments',
                'pathAlias'=>'comments.assets',
            ],
        ];
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'comments.models.*',
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
                'itemcolumn'=>'common.widgets.SItemColumn',
            ],
            'views'=>[
                'commentquickview'=>'common.modules.comments.views.management._quickview',
                'commentform'=>'common.modules.comments.views.management._quickform',
                'commentupdate'=>'common.modules.comments.views.management._update_body',
                'commentview'=>'common.modules.comments.views.management._view_body',
                'commentprev'=>'common.modules.comments.views.share._comment_prev',
                //account views
                'profilesidebar'=>'accounts.profilesidebar',
            ],
            'images'=>[
                   //'<imageKey>'=>array('<pathAlias>'=>'<imageFile>'),
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
                'class'=>'common.services.CommentManager',
                'model'=>'Comment',
                'ownerAttribute'=>'comment_by',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}