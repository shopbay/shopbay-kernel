<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends SPageIndexController
{
    public function init()
    {
        parent::init();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Ticket';
        $this->viewName = Sii::t('sii','Tickets');
        $this->route = 'tickets/management/index';
        //$this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->sortAttribute = 'create_time';
        if ($this->isAdminApp)
            $this->modelFilter = 'admin';
        //-----------------//
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),array(
            'view'=>array(
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'pageTitleAttribute'=>'subject',
                'loadModelMethod'=>'loadViewModel',
                'beforeRender'=>'beforeRenderView',
            ),                    
            'create'=>array(
                'class'=>'common.components.actions.CreateAction',
                'model'=>$this->modelType,
                'setAttributesMethod'=>'setModelAttributes',
                'service'=>'create',
                'viewFile'=>'create',
            ),
            'close'=>array(
                'class'=>'TransitionAction',
                'modelType'=>$this->modelType,
                'nameAttribute'=>'subject',
                'flashTitle'=>Sii::t('sii','Close Ticket'),
                'flashMessage'=>Sii::t('sii','Ticket "{name}" is closed successfully.'),
            ),
        ));
    } 
    /**
     * Before render view page
     * @param type $model
     * @return type
     */
    public function beforeRenderView($model)
    {
        if($model->isClosed) {
            user()->setFlash(get_class($model),array(
                        'message'=>Sii::t('sii','To re-open this ticket, simply reply a message! '),
                        'type'=>'notice',
                        'title'=>Sii::t('sii','This ticket has been closed')));
        }
    }   
    /**
     * Load view model data 
     * @param type $model
     * @return type
     */
    public function loadViewModel()
    {
        try {
            $search = current(array_keys($_GET));//take the first key as search attribute
            
            if ($this->isAdminApp)
                $model = Ticket::model()->retrieve($search)->find();
            else
                $model = Ticket::model()->mine()->retrieve($search)->find();
                
            if($model===null){
                throw new CHttpException(404,Sii::t('sii','Page not found'));
            }
            return $model;
            
        } catch (CException $e) {
            logError(__METHOD__.' '.$e->getMessage());
            throwError404(Sii::t('sii','The requested page does not exist'));
        }
    }     
    /**
     * Set model attributes for write/edit action
     * @param type $model
     * @return type
     */
    public function setModelAttributes($model)
    {
        if(isset($_POST[$this->modelType])) {
            $model->attributes = $_POST[$this->modelType];
            return $model;
        }
        throwError400(Sii::t('sii','Bad Request'));
    } 
    /**
     * Return page menu (with auto active class)
     * @param type $model
     * @return type
     */
    public function getPageMenu($model)
    {
        return array(
            array('id'=>'create','title'=>Sii::t('sii','Create Ticket'),'subscript'=>Sii::t('sii','create'), 'url'=>array('create'),'visible'=>$model->updatable()),
            array('id'=>'ticket','title'=>Sii::t('sii','Close Ticket'),'subscript'=>Sii::t('sii','close'), 'visible'=>$model->updatable(), 
                  'linkOptions'=>array('submit'=>url('tickets/management/close',array('Ticket[id]'=>$model->id)),
                                     'onclick'=>'$(\'.page-loader\').show();',
                                     'confirm'=>Sii::t('sii','Are you sure you want to close this ticket?'),
            )),
        );
    }
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        if ($this->isAdminApp){
            $filters->add('admin',Helper::htmlIndexFilter('All', false));
        }
        else {
            $filters->add('all',Helper::htmlIndexFilter('All', false));
            $filters->add('submitted',Helper::htmlIndexFilter('Open', false));
            $filters->add('closed',Helper::htmlIndexFilter('Closed', false));
        }
        return $filters->toArray();
    }    
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeDescription($scope)
    {
        switch ($scope) {
            case 'all':
                return Sii::t('sii','This lists all the support tickets that you have raised.');
            case 'submitted':
                return Sii::t('sii','This lists all the support tickets that are not yet closed.');
            case 'closed':
                return Sii::t('sii','This lists all the support tickets that are closed.');
            case 'admin':
                return Sii::t('sii','This lists all the support tickets that customers has submitted and pending reply.');
            default:
                return null;
        }
    }     
    /**
     * Reply ticket.
     * If reply is successful, the browser will be redirected to the 'view' page.
     */
    public function actionReply()
    {
        if(isset($_POST['TicketReplyForm'])) {
            
            $form = new TicketReplyForm('create');
            $form->attributes = $_POST['TicketReplyForm'];
            
            if($form->validate()){

                try {
                    $model = $this->module->getServiceManager()->reply(user()->getId(),$form);
                    $flash = $this->getFlashAsString('success',Sii::t('sii','Reply is posted successfully.'),Sii::t('sii','Ticket Reply'));
                    $form->unsetAttributes(array('content'));//keep obj_type and obj_id as need to echo back
                    $status = 'success';
                    $replyview = $this->renderPartial('_view_reply',array('data'=>$this->getReplyData($model),'cssClass'=>'reply-wrapper'),true);

                } catch (CException $e) {
                    $flash = $this->getFlashAsString('error',$e->getMessage(),Sii::t('sii','Ticket Reply Error'));
                    logError(__METHOD__.' ServiceManager error: '.$e->getMessage());
                }
            }
            else {
                $flash = $this->getFlashAsString('error',Helper::htmlErrors($form->getErrors()),Sii::t('sii','Reply Ticket Error'));
                logError(__METHOD__.' form validation error', $form->getErrors());
            }

            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>isset($status)?$status:'failure',
                'content'=>isset($replyview)?$replyview:null,
                'flash'=>$flash,
            ));
            Yii::app()->end();  

        }
        throwError404(Sii::t('sii','The requested page does not exist'));

    }        
    
    public function getAllReplyData($model)
    {
        $data = array();
        foreach ($model->searchReplies()->data as $reply) {
            foreach($this->getReplyData($reply) as $array)
                $data[] = $array;
        }
        return $data;
    }

    public function getReplyData($model)
    {
        $data = array();
        $header = CHtml::openTag('span',array('class'=>'reply-header'));
        $header .= CHtml::tag('span',array('class'=>'reply-from'),Sii::t('sii','Reply from {account}',array('{account}'=>$model->account->name)));
        $header .= CHtml::tag('span',array('class'=>'reply-date'),$model->formatDatetime($model->create_time,true));
        $header .= CHtml::closeTag('span');
        $data[] = array('type'=>'raw','cssClass'=>'content-data','value'=>$header);
        $data[] = array('type'=>'raw','cssClass'=>'content-data','value'=> Helper::purify($model->content));
        $data[] = array('type'=>'raw','cssClass'=>'line-break','value'=>'');
        return $data;
    }
    
    public function getReplyForm($model)
    {
        $form = new TicketReplyForm(app()->id,$model->id);
        $form->group = $model->shop_id;
        return $form;
    }
    
    public function getShopName($model)
    {
        if ($model->shop!=null)
            return $model->shop->displayLanguageValue('name',user()->getLocale());
        else
            return '';
    }    
    
    public function getIsAdminApp()
    {
        return $this->module->runAsAdmin;
    }     
}
