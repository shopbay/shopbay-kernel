<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TasksModule
 *
 * @author kwlok
 */
class TasksModule extends SModule 
{
    /**
     * @property string the default controller.
     */
    public $entryController = 'undefined';
    /**
     * @property boolean If to run module for buyer
     */
    public $runAsBuyer = false;
    /**
     * @property string the action class used for attachment upload.
     */
    public $attachmentUploadAction = 'common.modules.media.actions.AttachmentUploadAction';
    /**
     * @property string the class used for attachment form.
     */
    public $attachmentForm = 'common.modules.media.models.AttachmentForm';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return array(
            'assetloader' => array(
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'tasks',
                'pathAlias'=>'tasks.assets',
            ),
        );
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport(array(
            'tasks.models.*',
            'tasks.components.*',
            'tasks.controllers.TransitionController',
            $this->attachmentUploadAction,
            $this->attachmentForm,    
            'common.widgets.SButtonColumn',
            'common.widgets.spagemenu.SPageMenu',
            'common.services.workflow.models.Workflowable',
        ));
        // import module dependencies classes
        $this->setDependencies(array(
            'modules'=>array(
                'payments'=>array(  
                    'common.modules.payments.models.PaymentMethod',
                    'common.modules.payments.models.PaymentForm',
                ),                            
                'messages'=>array(  
                    'common.modules.messages.models.Message',
                ),                            
                'orders'=>array(  
                    'common.modules.orders.models.ShippingOrder',
                ),                   
                'news'=>array(
                    'common.modules.news.models.News',
                ),
                'taxes'=>array(
                    'common.modules.taxes.models.Tax',
                ),
                'questions'=>array(
                    'common.modules.questions.models.Question',
                    'common.modules.questions.models.QuestionForm',
                    'common.modules.questions.models.AnswerForm',
                ),
                'shops'=>array(
                    'shops.models.ShopForm',
                    'shops.models.ShopApplicationForm',
                ),
                'tutorials'=>array(
                    'common.modules.tutorials.models.Tutorial',
                ),
            ),
            'views'=>array(
                'processhistory'=>'common.modules.tasks.views.common._process_history',
                'tasklist'=>'common.modules.tasks.views.merchant._task_listview',
                'taskgroup'=>'common.modules.tasks.views.merchant._task_group',
                'task'=>'common.modules.tasks.views.merchant._task',
                'itemtasks'=>'common.modules.tasks.views.item._items',
                'transitionform'=>'common.modules.tasks.views.workflow._transitionform',
                //orders view
                'ordertotal'=>'orders.merchanttotal',
                'ordershipping'=>'orders.merchantshipping',
                'orderinventory'=>'orders.merchantinventory',
                'orderaddress'=>'orders.merchantaddress',
                'orderpayment'=>'orders.merchantpayment',
                'orderhistory'=>'orders.merchanthistory',
                'orderattachment'=>'orders.merchantattachment',
                //questions view
                'question'=>'questions.question',
                'questionform'=>'questions.questionform',
                'answer'=>'questions.answer',
                'answerform'=>'questions.answerform',
                //shop view
                'shop'=>'shops.shopapply',
                'shopapplyform'=>'shops.shopapplyform',
                'shoplocaleform'=>'shops.shoplocaleform',
                //payments view
                'fundtransferform'=>'payments.fundtransferform',
                //account view
                'profilesidebar'=>'accounts.profilesidebar',
                //comments view
                'commentform'=>'comments.commentupdate',
                //items view
                'receiveform'=>'items.receiveform',
                'item' => 'items.itemview',
            ),
            'classes'=>array(
                'listview'=>'common.widgets.SListView',
                'gridview'=>'common.widgets.SGridView',
                'groupview'=>'common.widgets.SGroupView',
                'itemcolumn'=>'common.widgets.SItemColumn',
                'imagecolumn'=>'common.widgets.EImageColumn',                            
            ),
            'images'=>array(
                'info'=>array('common.assets.images'=>'info.png'),
            ),
        ));  

        $this->defaultController = $this->entryController;

        $this->registerScripts();

        $this->publishSUploadAssets();//used by attachment upload
    }

    //tweak: extract from SUpload.publishAssets()
    private function publishSUploadAssets()
    {
        Yii::import('common.extensions.supload.SUpload');
        $supload = new SUpload();
        $supload->publishAssets();
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager($model,$owner=null)
    {
        logTrace(__METHOD__.' Requesting '.get_class($model).' service manager...'.(isset($owner)?' by owner='.$owner:''));
        $module = Yii::app()->serviceManager->parseModule(get_class($model));
        if (Yii::app()->hasModule($module))
            return Yii::app()->getModule($module)->getServiceManager($owner);
        
        throw new CException(Sii::t('sii','Service Manager for {module} not available or not installed',array('{module}'=>$module)));
        
    }

}
