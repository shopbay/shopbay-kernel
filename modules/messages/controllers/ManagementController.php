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
        // check if module requisites exists
        $missingModules = $this->getModule()->findMissingModules();
        if ($missingModules->getCount()>0)
            user()->setFlash($this->getId(),[
                'message'=>Helper::htmlList($missingModules),
                'type'=>'notice',
                'title'=>Sii::t('sii','Missing Module'),
            ]);
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Message';
        $this->route = 'messages/management/index';
        $this->viewName = Sii::t('sii','Inbox');
        $this->enableViewOptions = false;
        $this->enableSearch = false;
        $this->sortAttribute = 'send_time';
        //-----------------//  
    }
    /**
     * IMPORTANT NOTE:
     * This controller is not including 'subscription' filter as 
     * User should be allowed to read messages when they are already registered
     * @return array action filters
     */
    public function filters()
    {
        $filters = parent::filters();
        foreach ($filters as $key => $value) {
            if ($value=='subscription')
                unset($filters[$key]);
        }
        return $filters;
    }     
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),[
            'view'=>[
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'modelFilter'=>'sentOrReceived',
                'pageTitleAttribute'=>'displaySubject',
                'finderMethod'=>'read',
                'accountAttribute'=>['recipient','sender'],
                'beforeRender'=>'beforeMessageView',
            ],              
            'compose'=>[
                'class'=>'common.components.actions.CreateAction',
                'model'=>$this->modelType,
                'service'=>'compose',
                'viewFile'=>'compose',
                'createModelMethod'=>'prepareCompose',
                'setAttributesMethod'=>'setComposeMetadata',
                'flashMessage'=>Sii::t('sii','Message with subject "{name}" is sent successfully'),
                'nameAttribute'=>'displaySubject',
            ],
            'reply'=>[
                'class'=>'common.components.actions.CreateAction',
                'model'=>$this->modelType,
                'service'=>'compose',
                'viewFile'=>'compose',
                'createModelMethod'=>'prepareReply',
                'setAttributesMethod'=>'setReplyMetadata',
                'flashMessage'=>Sii::t('sii','Message with subject "{name}" is sent successfully'),
                'nameAttribute'=>'displaySubject',
            ],
            'delete'=>[
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
            ],
            'sent'=>[
                'class'=>'SPageIndexAction',
                'model'=>$this->modelType,
                'route'=>$this->route,
                'pageHeading'=>Sii::t('sii','Sent'),
                'viewName'=>Sii::t('sii','Sent'),
                'enableSearch'=>$this->enableSearch,
                'enableViewOptions'=>$this->enableViewOptions,
                'defaultScope'=>'sent',
                'index'=>'index',
            ],            
            'unread'=>[
                'class'=>'SPageIndexAction',
                'model'=>$this->modelType,
                'route'=>$this->route,
                'pageHeading'=>Sii::t('sii','Unread'),
                'viewName'=>Sii::t('sii','Unread'),
                'enableSearch'=>$this->enableSearch,
                'enableViewOptions'=>$this->enableViewOptions,
                'defaultScope'=>'unread',
                'index'=>'index',
            ],            
        ]);
    } 
    
    public function prepareCompose()
    {
        if (isset($_GET['order'])){
            $order = Order::model()->mine()->orderNo($_GET['order'])->find();
            if ($order===null)
                throwError404(Sii::t('sii','Order not found'));
            $model = new $this->modelType;
            $model->subject = Sii::t('sii','Order {order_no} Enquiry',array('{order_no}'=>$order->order_no));
            SActiveSession::set(SActiveSession::MESSAGE_COMPOSE, array(
                'recipient'=>$order->shop->account_id,
                'recipient_name'=>$order->shop->displayLanguageValue('name',user()->getLocale()),
                'order_id'=>$order->id,
                'order_no'=>$order->order_no,
                'shop_id'=>$order->shop_id,
                'shop_name'=>$order->shop->displayLanguageValue('name',user()->getLocale()),
                'reference_name'=>$order->order_no,
                'sender_name'=>user()->accountProfile->first_name!=null?user()->accountProfile->first_name:Sii::t('sii','unset'),
                'sender_reference_link'=>$order->viewUrl,
                'recipient_reference_link'=>$order->getViewUrl(app()->urlManager->merchantDomain),
            ));
            $model->metadata = json_encode(SActiveSession::get(SActiveSession::MESSAGE_COMPOSE));
            return $model;
        }
        throwError404(Sii::t('sii','Page not found'));
    } 
    
    public function setComposeMetadata($model)
    {
        $model->attributes = $_POST[$this->modelType];
        $model->encryptContent();
        $model->encryptSubject();
        if (SActiveSession::exists(SActiveSession::MESSAGE_COMPOSE)){
            $metadata = SActiveSession::get(SActiveSession::MESSAGE_COMPOSE);
            $model->metadata = json_encode($metadata);
            $model->recipient = $metadata['recipient'];
        }
        //support notification by email
        $this->module->serviceManager->getNotificationManager()->send($model);
        return $model;
    }        
    
    public function prepareReply()
    {
        $key = current(array_keys($_GET));//take the first key as message key
        $message = Message::model()->findByPk($key);   
        if ($message===null)
            throwError404(Sii::t('sii','Message not found'));
        $newMessage = new $this->modelType;
        $newMessage->subject = Sii::t('sii','RE: {subject}',array('{subject}'=>$message->decryptSubject()));
        $newMessage->content = Sii::t('sii','<p>--- Original Message ---</p><p>{content}</p>',array('{content}'=>$message->decryptContent()));
        SActiveSession::set(SActiveSession::MESSAGE_COMPOSE, array(
            'recipient'=>$message->sender,
            'recipient_name'=>$message->senderName,
            'reply_message'=>$message->id,
            'reference_name'=>$message->getReferenceName(),
            'sender_name'=>$message->recipientName,//swap
            'sender_reference_link'=>$message->getReferenceLink($message->recipient),//swap
            'recipient_reference_link'=>$message->getReferenceLink($message->sender),
        ));
        $newMessage->metadata = json_encode(SActiveSession::get(SActiveSession::MESSAGE_COMPOSE));        
        return $newMessage;
    } 
    
    public function setReplyMetadata($model)
    {
        $model->attributes = $_POST[$this->modelType];
        if (SActiveSession::exists(SActiveSession::MESSAGE_COMPOSE)){
            $metadata = SActiveSession::get(SActiveSession::MESSAGE_COMPOSE);
            $model->encryptContent();
            $model->encryptSubject();
            $model->metadata = json_encode($metadata);
            $model->recipient = $metadata['recipient'];
            //support notification by email
            $this->module->serviceManager->getNotificationManager()->send($model);
        }
        return $model;
    }      
    /**
     * Before message view
     * @param integer $id the ID of the model to be displayed
     */
    public function beforeMessageView($model)
    {
        if ($model->recipient==user()->getId()){//only required when user is recipient
            $model->receive_time = time();//Update message as read
            $model->save();//set receive_time to indicate its a read message
        }
    }
    /**
     * @override
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        $filters->add('all',Helper::htmlIndexFilter('All', false));
        $filters->add('unread',Helper::htmlIndexFilter('Unread', false));
        $filters->add('sent',Helper::htmlIndexFilter('Sent', false));
        return $filters->toArray();
    }
    
}
