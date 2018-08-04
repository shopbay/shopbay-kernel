<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.notifications.models.*");
Yii::import("common.services.notification.Dispatcher");
Yii::import("common.services.notification.events.*");
Yii::import("common.modules.inventories.models.LowInventoryDataProvider");
/**
 * Description of NotificationManager
 *
 * @author kwlok
 */
class NotificationManager extends CApplicationComponent 
{
    use NotificationTrait;

    protected $emailSenderName;
    
    public function init() 
    {
        parent::init();   
        $this->emailSenderName = readConfig('email','sender_name');
        $this->attachEventHandler('onMessage',[new Dispatcher,'send']);
        $this->attachEventHandler('onBroadcast',[new Dispatcher,'broadcast']);
        $this->attachEventHandler('onEmail',[new Dispatcher,'email']);
        $this->attachEventHandler('onGroupEmail',[new Dispatcher,'groupEmail']);
        $this->attachEventHandler('onMessenger',[new Dispatcher,'messenger']);
        logTrace(__METHOD__.' Dispatcher events attached');        
    }
    /**
     * Raises an <code>onMessage</code> event.
     * @param MessageEvent the event parameter
     * @since 1.1.0
     */
    public function onMessage($event)
    {
        $this->raiseEvent('onMessage', $event);
    }
    /**
     * Raises an <code>onBroadcast</code> event.
     * @param MessageEvent the event parameter
     * @since 1.1.0
     */
    public function onBroadcast($event)
    {
        $this->raiseEvent('onBroadcast', $event);
    }       
    /**
     * Raises an <code>onEmail</code> event.
     * @param EmailEvent the event parameter
     * @since 1.1.0
     */
    public function onEmail($event)
    {
        $this->raiseEvent('onEmail', $event);
    }    
    /**
     * Raises an <code>onGroupMail</code> event.
     * @param EmailEvent the event parameter
     * @since 1.1.0
     */
    public function onGroupEmail($event)
    {
        $this->raiseEvent('onGroupEmail', $event);
    }    
    /**
     * Raises an <code>onMessenger</code> event.
     * @param EmailEvent the event parameter
     * @since 1.1.0
     */
    public function onMessenger($event)
    {
        $this->raiseEvent('onMessenger', $event);
    }    
    /**
     * Notification gateway
     * @param type $obj
     */
    public function send($obj)
    {        
        if ($obj instanceof Account){
            $this->notifyByAccount($obj);
        }        
        elseif ($obj instanceof CustomerAccount){
            $this->notifyByCustomer($obj);
        }        
        elseif ($obj instanceof Receipt){
            $this->notifyByReceipt($obj);
        }        
        elseif ($obj instanceof ContactForm){
            $this->notifyByContactForm($obj);
        }        
        elseif ($obj instanceof Ticket){
            $this->notifyByTicket($obj);        
        }
        elseif ($obj instanceof Message){
            $this->notifyByMessage($obj);
        }        
        elseif ($obj instanceof LowInventoryDataProvider){
            $this->notifyByInventory($obj);
        }        
        elseif ($obj instanceof Transition){
            $this->notifyByTransition($obj);
        }
        elseif ($obj instanceof Workflowable||
                $obj instanceof Transitionable){
            $this->notifyByWorkflow($obj);        
        }
        else 
            logWarning('No notification object found for '.get_class($obj));        
    }
    /**
     * Trigger notification for transition events
     * @param type $transition
     */
    protected function notifyByTransition($transition) 
    {
        $notifications = Notification::model()->event($transition->obj_type,$transition->process_to)->findAll();

        if ($notifications!=null) {

            foreach ($notifications as $notification) {
                
                $type = SActiveRecord::resolveTablename($notification->event_type);

                $model = $type::model()->findByPk($transition->obj_id);
            
                $this->_notify($notification, $model);
            }            
        }
        else
            logWarning('No notification object found', $transition->getAttributes(), false);
    }
    /**
     * Trigger notification for workflowable events
     * @param type $model
     */
    protected function notifyByWorkflow($model) 
    {
        $notifications = Notification::model()->event($model->tableName(),$model->status)->findAll();
        
        if ($notifications!=null) {

            foreach ($notifications as $notification)
                $this->_notify($notification, $model);
            
        }
        else
            logWarning('No notification message object found for '.$model->tableName().' '.$model->id.' '.$model->status);
    }    
    /**
     * Trigger notification when customer sending in "contact us" form
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * @see NotificationCommand
     * @param type $form
     */
    protected function notifyByContactForm($form) 
    {
        if (Config::getSystemSetting('email_notification')==Config::ON){
            $this->onEmail(new EmailEvent($form->getMailAddressTo(),$form->getMailAddressName(),$form->getMailSubject(),$form->getMailBody()));
        }
    }
    /**
     * Trigger notification when a receipt is generated
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * @param type $model
     * @see NotificationCommand
     * 
     * @see self::notifyByTransition()
     */
    protected function notifyByReceipt($model) 
    {
        if (Config::getSystemSetting('email_notification')==Config::ON){
            
            $emailBody = $this->controller()->renderPartial($model->getEmailTemplate(),$model->getFileParams(),true);
            
            $this->onEmail(new EmailEvent(
                $model->getEmail(),
                isset($model->accountProfile->name)?$model->accountProfile->name:$model->account->name,
                Sii::t('sii','[{app}] Payment Receipt',['{app}'=>param('SITE_NAME')]),
                $emailBody,
                EmailEvent::ASYNCHRONOUS,
                $model->filepath
            ));
        }
    }    
    /**
     * Trigger notification when user reply a ticket 
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * @param type $model
     * @see NotificationCommand
     * 
     * Note: Ticket submission notification is handled at Transition
     * @see self::notifyByTransition()
     */
    protected function notifyByTicket($model) 
    {
        if ($model->isReplied){
            $notifications = Notification::model()->event($model->tableName(),Process::TICKET_REPLIED)->findAll();
            if ($notifications!=null) {
                foreach ($notifications as $notification){
                    $this->_notify($notification, $model);
                }
            }
            else
                logWarning(__METHOD__.' No notification message object found for '.$model->tableName().' '.$model->id);
        }
        else 
            logWarning(__METHOD__.' Object is not in REPLIED status: '.$model->tableName().' '.$model->id);
    }    
    /**
     * Trigger notification when user reply a message 
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * @see NotificationCommand
     * @param type $model
     */
    protected function notifyByMessage($model) 
    {
        if (Config::getSystemSetting('email_notification')==Config::ON){

            $notification = Notification::model()->event($model->tableName(),Process::OK)->find();
            if ($notification!=null) 
                $this->onEmail(new EmailEvent($model->getRecipientEmail(),$model->getRecipientName(),$model->getSubject(),$model->getContent(),EmailEvent::ASYNCHRONOUS));
        }
        else
            logWarning('No notification message object found for '.$model->tableName().' '.$model->id);
    }
    /**
     * Trigger notification when inventory is low (when Product status is set to Process::PRODUCT_INVENTORY_LOW 
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * @see InventoryCommand::actionScan
     * @param type $model
     */
    protected function notifyByInventory($model) 
    {
        $notifications = Notification::model()->event(Product::model()->tableName(),Process::PRODUCT_INVENTORY_LOW)->findAll();
        
        if ($notifications!=null) {

            foreach ($notifications as $notification) {
		//logTrace('notify',$notification->getAttributes());
                $this->_notify($notification, $model, array('data'=>$model));
            }            
        }
        else
            logWarning('No notification object found for '.Product::model()->tableName().' '.Process::PRODUCT_INVENTORY_LOW);
    }
    /**
     * Notification triggered by account events such as signup, change password etc
     * It is set to EmailEvent::SYNCHRONOUS to let user get instant notification
     * @param type $account
     */
    protected function notifyByAccount($account) 
    {
        $params = ['name'=>$account->name];
        if ($account->status == Process::PASSWORD_RESET){
            $params = array_merge(['password'=>$account->password],$params);
        }
        elseif ($account->status == Process::ACCOUNT_NEW){
            $params = array_merge(['password'=>$account->password,'activate_str'=>$account->activate_str],$params);
        }
        elseif ($account->status == Process::PRESIGNUP){
            Yii::import("common.modules.accounts.oauth.OAuth");
            $oauth = OAuth::model()->findByAttributes(array('account_id'=>$account->id));//return the first record
            $params = array_merge(['activate_str'=>$account->activate_str,'network'=>$oauth->provider],$params);
        }
        else {
            $params = array_merge(['activate_str'=>$account->activate_str],$params);
        }
        
        $notifications = Notification::model()->event($account->tableName(),$account->status)->findAll();
        
        if ($notifications!=null) {

            foreach ($notifications as $notification) {               
                $this->_notify($notification, $account, $params, EmailEvent::SYNCHRONOUS);
            }            
        }
        else
            logWarning('No notification object found for '.$account->tableName().' '.$account->status);
    }
    /**
     * Notification triggered by account events such as signup, change password etc
     * It is set to EmailEvent::SYNCHRONOUS to let user get instant notification
     * @param type $account
     */
    protected function notifyByCustomer($account) 
    {
        if ($account->status == Process::PASSWORD_RESET){
            $params = ['password'=>$account->password];
        }
        elseif ($account->status == Process::PRESIGNUP){
            Yii::import("common.modules.accounts.oauth.OAuth");
            $oauth = OAuth::model()->findByAttributes(array('account_id'=>$account->id));//return the first record
            $params = ['network'=>$oauth->provider];
        }
        else
            $params = [];
            
        $notifications = Notification::model()->event($account->tableName(),$account->status)->findAll();
        
        if ($notifications!=null) {

            foreach ($notifications as $notification) {               
                $this->_notify($notification, $account, $params, EmailEvent::SYNCHRONOUS);
            }            
        }
        else
            logWarning('No notification object found for '.$account->tableName().' '.$account->status);
    }    
    /**
     * Default notification sending mechanism
     * It is set to EmailEvent::ASYNCHRONOUS to let a cronjob handle its notification sendings;
     * 
     * Modes of recipient types (to determine how to get recipient id/email):
     * (a) RECIPIENT_USER
     * Recipient derived by model attribute; Attribute is set in column $recipient; 
     * E.g For model Question, $recipient=account_id, so deriving recipient id = $question->account_id
     * (b) RECIPIENT_OBJECTOWNER
     * Recipient derived by model object associated with it(normally defined in method relations()); 
     * E.g For model Product, $recipient=shop.account_id, so deriving recipient id = $product->shop->account_id
     * (c) RECIPIENT_ROLE
     * Recipient derived by the role user assigned to 
     * E.g Adminstrator, so user with Administrator role will receive email
     * (d) RECIPIENT_CLASSMETHOD
     * Recipient derived by the definition of model class method, the method should return array in following format
     * <pre>
     * array(
     *     'email'=><email>,
     *     'account'=><account_model>,
     *     'subject'=><subject>,
     *     'recipient'=><account_id / name>,//when email is set, recipient will be name
     *     'role'=><role_name>,//when set, this will be recipient type (c) above
     * )
     * </pre> 
     * 
     * @see NotificationCommand
     * @see NotificationTrait for modes of recipient types
     * @param type $notification
     * @param type $model
     * @param array $viewData notification view data
     */
    private function _notify($notification, $model, $viewData=[],$sendMode=EmailEvent::ASYNCHRONOUS)
    {
	logTrace(__METHOD__,$notification->getAttributes());

        $recipient = $this->findRecipient($notification, $model);
        
        $subject = $this->prepareSubject($notification, $model);

        $content = $this->controller()->renderPartial($notification->content,array_merge($viewData,['model'=>$model]),true);
        
        switch ($notification->type) {
            case Notification::$typeMessage:
                if (isset($recipient->role)){
                    $this->onBroadcast(new MessageEvent($recipient->role,$subject,$content));
                }
                else {
                    if ($this->hasSubscription($recipient, $notification)){
                        $this->onMessage(new MessageEvent($recipient->id,$subject,$content));
                    }
                    else
                	logTrace(__METHOD__." Inbox message skip! user $recipient->id has not subscribed to notification $notification->name");
                }
                break;
            case Notification::$typeEmail:
                if (Config::getSystemSetting('email_notification')==Config::ON){
                    if (isset($recipient->role)){
                        $this->onGroupEmail(new GroupEmailEvent($recipient->role,$subject,$content));
                    }
                    else {
                        if ($this->hasSubscription($recipient, $notification)){
                            $this->onEmail(new EmailEvent(
                                    $recipient->email,
                                    $recipient->name,
                                    $subject,
                                    $content,
                                    $sendMode,
                                    null,
                                    $recipient->emailSenderName));
                        }
                        else
                            logTrace(__METHOD__." Email skip! user $recipient->id has not subscribed to notification $notification->name");
                    }
                }        
                break;
            case Notification::$typeMessenger:
                //Note that $subject value in the notification record is not in used for now
                $subData = json_decode($content,true);//content contains the subscription meta data
                logTrace(__METHOD__." $notification->name meta data",$subData);
                $scope = new NotificationScope($subData['scope']['id'],$subData['scope']['class']);
                /**
                 * Json encode accound id as subscriber
                 * @see NotificationScope::scopeBy for parsing
                 */
                $subscriber = json_encode(['account_id'=>$recipient->id]);
                $sub = $this->findSubscription($subscriber,$notification->name, $notification->type, $scope);
                if ($sub!=null && $sub->online()){//no by default notification is on!
                    $event = new MessengerEvent(
                                    $scope->toString(),
                                    $sub->subscriberMessenger,
                                    $subData['payload'],//event subject field here contains payload information
                                    Messenger::TYPE_PAYLOAD,//event content field here specify the messenger method
                                    $subData['params']);
                    $this->onMessenger($event);
                }
                else
                    logTrace(__METHOD__." Messenger skip! user $recipient->id has not subscribed to notification $notification->name");
                break;
            default:
                break;
        }
    }
    /**
     * Get appropriate controller based on Application type
     * @return CController
     */
    protected function controller()
    {
        if (app() instanceof CConsoleApplication) {
            Yii::import("console.components.ConsoleController");
            return new ConsoleController('notification');
        }
        else {
            if (app()->getController()===null)
                return new CController('dummy');
            else
                return app()->getController();   
        }
    }
    /**
     * Prepare the proper subject send
     * @param type $notification
     * @param type $model
     * @return type
     */
    protected function prepareSubject($notification,$model)
    {
        $subject = self::parseSubject($model, $notification->subject, $notification->type==Notification::$typeMessage);
        //further parse subject for $recipientClassmethod
        if ($notification->recipient_type==Notification::$recipientClassmethod){
            $ref = $model->{$notification->recipient};
            if (is_array($ref))
                $subject = isset($ref['subject'])?$ref['subject']:$subject;
        }

        logTrace(__METHOD__. ' subject='.$subject);

        return $subject;
    }
    /**
     * Find the corresponding recipient contact information
     * @param type $model
     * @return stdClass contains attributes: name, email, emailSenderName, role 
     */
    protected function findRecipient($notification,$model)
    {
        $recipient = ['emailSenderName'=>null];//default emailSenderName is null
        switch ($notification->recipient_type) {
            case Notification::$recipientUser:
                $recipient['id'] = $model->{$notification->recipient};
                logTrace(__METHOD__.' Recipient Type '.Notification::$recipientUser.' for model',  get_class($model));
                if ($model instanceof Account){
                    $recipient['name'] = isset($model->profile->name)?$model->profile->name:$model->name;
                    $recipient['email'] = $model->{$notification->recipient};
                    $recipient['emailSenderName'] = $this->emailSenderName;
                }
                elseif ($model instanceof CustomerAccount){
                    $recipient['name'] = isset($model->profile->name)?$model->profile->name:$model->email;
                    $recipient['email'] = $model->{$notification->recipient};
                    $recipient['emailSenderName'] = $model->shopName;
                }
                else {
                    if (Account::getAccountClass($recipient['id'])=='CustomerAccount'){
                        $account = CustomerAccount::model()->retrieve(Account::decodeId($recipient['id']))->find();
                        $recipient['name'] = $account->nickname;
                        $recipient['email'] = $account->email;
                        $recipient['emailSenderName'] = $account->shopName;
                    }
                    else {
                        $account = Account::model()->findByPk($model->{$notification->recipient});
                        $recipient['name'] = isset($account->profile->name)?$account->profile->name:$account->name;
                        $recipient['email'] = $account->email;
                        $recipient['emailSenderName'] = $this->emailSenderName;
                    }
                }
                break;
            case Notification::$recipientObjectowner:
                $recipientObject = explode('.', $notification->recipient);
                $modelClass = ucfirst($recipientObject[0]);
                $objectOwner = $modelClass::model()->findbyPk($model->{$recipientObject[1]});
                $recipient['id'] = $objectOwner->account_id;
                $recipient['name'] = isset($objectOwner->account->profile->name)?$objectOwner->account->profile->name:$objectOwner->account->name;
                $recipient['email'] = $objectOwner->account->email;
                break;
            case Notification::$recipientRole:
                $recipient['role'] = $notification->recipient;
                break;
            case Notification::$recipientClassmethod:
                $ref = $model->{$notification->recipient};
                if (is_array($ref)){
                    if (isset($ref['recipient'])){
                        $recipient['id'] = $ref['recipient'];
                    }
                    //further parsing
                    if (isset($ref['email']) && $ref['recipient']){
                        if (isset($ref['emailSenderName']))
                            $this->emailSenderName = $ref['emailSenderName'];
                        $recipient['name'] = $ref['recipient'];
                        $recipient['email'] = $ref['email'];
                        $recipient['emailSenderName'] = $this->emailSenderName;
                    }
                    elseif (isset($ref['account']) && $ref['account'] instanceof Account){
                        $recipient['name'] = isset($ref['account']->profile->name)?$ref['account']->profile->name:$ref['account']->name;
                        $recipient['email'] = $ref['account']->email;
                    }
                    elseif (isset($ref['role']))
                        $recipient['role'] = $ref['role'];
                }
                break;
            default:
                return null;
        }
        
        logTrace(__METHOD__,$recipient);
        return (object)$recipient;
    }
    /**
     * Find if recipient has subscribe to notification
     * It checks notification name, type and subscriber
     * @todo Check scope e.g. for a particular shop 
     * 
     * @param type $recipient
     * @param type $notification
     */
    protected function hasSubscription($recipient,$notification)
    {
        if (!isset($recipient->id)){
            logError(__METHOD__.' Recipient has no id!',$recipient);
            return false;
        }
        
        $scope = new NotificationScope($recipient->id);//default to use global socpe
        //todo need to accomdate extra logic to determine scope, e.g. how to find shop id?
        $sub = $this->findSubscription($recipient->id,$notification->name, $notification->type, $scope);
        if ($sub==null){
            logTrace(__METHOD__." Recipient $recipient->id has not subscribed to $notification->name type $notification->type but by default all notifications are turned on");
            //by default all notification are turned on. If not found any subscription, return true
            return true;
        }
        else {
            return $sub->online();
        }
    }
    /**
     * Subscribe to a notification
     * @see NotificationManager::saveSubscription()
     */
    public function subscribe($subscriber,$name,NotificationScope $scope,$channel)
    {
        //auto pick up notification config
        $params = [];
        if (isset(Notification::subscriptionsConfig()[$name]))
            $params = Notification::subscriptionsConfig()[$name];
        
        $sub = $this->saveSubscription($subscriber, $name, $scope, $channel, $params, Process::NOTIFICATION_SUBSCRIBED);
        logTrace(__METHOD__." $sub->notification type $sub->channel ok.");
        //todo add activity recording for account scope
    }
    /**
     * Unsubscribe from a notification
     * @see NotificationManager::saveSubscription()
     */
    public function unsubscribe($subscriber,$name,NotificationScope $scope,$channel)
    {
        $sub = $this->saveSubscription($subscriber, $name, $scope, $channel, [], Process::NOTIFICATION_UNSUBSCRIBED);
        logTrace(__METHOD__." $sub->notification type $sub->channel ok.");
        //todo add activity recording
    }
    /**
     * Generic method to save notification subscription (either subscribe or unsubscribe)
     * @param type $subscriber The subscriber
     * @param type $name The notification name
     * @param NotificationScope $scope The notification scope
     * @param type $channel The notification channel
     * @param type $params The subscription params
     * @param string $status The notification status (either to subscribe or unsubscribe)
     * @return NotificationSubscription
     */
    protected function saveSubscription($subscriber,$name,NotificationScope $scope,$channel,array $params,$status)
    {
        $sub = $this->findSubscription($subscriber, $name, $channel, $scope);

        if ($sub==null){
            $sub = new NotificationSubscription();
            $sub->notification = $name;
            $sub->scope = $scope->toString();
            $sub->channel = $channel;
            if ($scope->isGlobal)
                $sub->account_subscriber = $subscriber;
            else  {
                $sub->subscriber = $subscriber;
                $json = json_decode($subscriber);
                if (isset($json->account_id)){
                    $sub->account_subscriber = $json->account_id;
                }
            }
        }
        if (!empty($params))
            $sub->params = json_encode($params);
        $sub->status = $status;
        $sub->save();
        return $sub;
    }
    /**
     * Find subscription
     * @param type $subscriber
     * @param type $name
     * @param type $channel
     * @param NotificationScope $scope
     * @return type
     */
    public function findSubscription($subscriber,$name,$channel,NotificationScope $scope)
    {
        return NotificationSubscription::model()->forNotification($name)->scopeBy($scope,$subscriber)->notifyBy($channel)->find();
    }
    
}