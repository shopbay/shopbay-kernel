<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.notifications.models.Notification");
Yii::import("common.modules.notifications.models.MessageQueue");
Yii::import("common.services.notification.Mailer");
Yii::import("common.services.notification.Messenger");
/**
 * Description of Dispatcher
 *
 * @author kwlok
 */
class Dispatcher extends CComponent 
{
    /**
     * Send message 
     * @param type $event
     */
    public static function send($event) 
    {
        $msg = new Message();
        $msg->sender = Account::SYSTEM;//system admin
        $msg->send_time = time();
        $msg->recipient = $event->recipient;
        $msg->subject = $event->subject;
        $msg->content = $event->content;
	$msg->save();
        logInfo(__METHOD__.' Message '.$msg->id.' sent.');
    }
    /**
     * Broadcast message to a group
     * @param type $event
     */
    public static function broadcast($event) 
    {
        logInfo(__METHOD__.' Broadcasting message to '.$event->recipient);

        $recset = AuthAssignment::getUsers($event->recipient);

        foreach ($recset as $rec)
            self::send(new MessageEvent($rec->userid, $event->subject, $event->content));
    }
    /**
     * Group send email to a group
     * @param type $event
     */
    public static function groupEmail($event) 
    {
        logInfo(__METHOD__.' Sending group email to members of role '.$event->role);

        $auths = AuthAssignment::getUsers($event->role);

        foreach ($auths as $auth){
            $account = Account::model()->findByPk($auth->userid);
            if ($account!=null){
                self::email(new EmailEvent($account->email,
                                           isset($account->profile->name)?$account->profile->name:$account->name,
                                           $event->subject,
                                           $event->content));
            }
            else
                logError(__METHOD__.' account '.$auth->userid.' not found.');
        }
    }
    /**
     * Sending email notification
     * Two modes here:
     * (1) ASYNCHRONOUS - put email message into a queue and let other queue runner to do the sending
     * This mode requires additional component to process queued messages stored at s_message_queue (MessageQueue)
     * Currently NotificationCommand (at console app) will be the queue messages processor
     * 
     * (2) SYNCHRONOUS - straight away send email out in one single process (thread)
     * 
     * @param type $event
     */
    public static function email($event) 
    {
        if ($event->mode==EmailEvent::SYNCHRONOUS) {
            //for SYNCHRONOUS mode, also save to queue as email trail, but status is set to OK (already sent)
            $saveToQueue = true;
            return self::sendEmail($event, $saveToQueue);
        }
        else { //ASYNCHRONOUS mode
            return self::saveToQueue($event,Notification::$typeEmail);
        }
    }
    /**
     * The actual method to send out email
     * @return type
     */
    public static function sendEmail($event,$saveToQueue=false)
    {
        //Yii::log('Email message to '.$event->addressTo, CLogger::LEVEL_TRACE);
        $mailer = new Mailer();
        $mailer->send($event->addressTo, $event->addressName, $event->subject, $event->content, $event->attachment, $event->senderName);

        if ($saveToQueue){
            self::saveToQueue($event,Notification::$typeEmail,Process::OK);
        }

        if ($mailer->hasError()){
            Yii::log($mailer->getError(), CLogger::LEVEL_ERROR);
            return Process::ERROR;
        }
        else {
            Yii::log(__METHOD__.' Email sent.', CLogger::LEVEL_TRACE);
            //return back result
            return Process::OK;
        }        
    }
    /**
     * Put message into queue (not imediately send it)
     * @param type $event
     * @param type $type
     * @param type $status
     * @return type
     */
    public static function saveToQueue($event,$type,$status=Process::HOLD)
    {
        $mq = new MessageQueue();
        $mq->type = $type;
        $mq->message = json_encode($event);
        $mq->status = $status;
        if ($mq->save()){
            logTrace(__METHOD__." message put to queue $mq->id type $mq->type for status $status");
            return Process::OK;
        }
        else {
            logWarning(__METHOD__.' failed to put message to queue.',array_merge(['error'=>$mq->getErrors()],$mq->getAttributes()));
            return Process::ERROR;
        }
    }
    /**
     * Sending messenger notification
     * Two modes here:
     * (1) ASYNCHRONOUS - put  message into a queue and let other queue runner to do the sending
     * This mode requires additional component to process queued messages stored at s_message_queue (MessageQueue)
     * Currently NotificationCommand (at console app) will be the queue messages processor
     * 
     * (2) SYNCHRONOUS - straight away send message out in one single process (thread)
     * 
     * @param type $event
     */
    public static function messenger($event) 
    {
        if ($event->mode==MessengerEvent::SYNCHRONOUS) {
            
            $messenger = new Messenger();

            if ($event instanceof MessengerEvent){
                $sender = $event->scopeObject;
                $params = $event->params;
                $subject = $event->subject;
            }
            else {//cater for message queue (input $event is a stdClass)
                $sender = MessengerEvent::parseScope($event->sender);//cannot use scope here as json string don't have scope attribute; Messenger::send expects sender attribute
                $params = (array)$event->params;
                $subject = (array)$event->subject;
            }                

            $status = $messenger->send($sender,$event->recipient, $subject, $event->content,$params);

            //prepare status 
            if ($status==false){
                Yii::log(__METHOD__.' Error sending to Messenger.', CLogger::LEVEL_ERROR);
                return Process::ERROR;
            }
            else {
                Yii::log(__METHOD__.' Message sent to Messenger.', CLogger::LEVEL_TRACE);
                //for SYNCHRONOUS mode, also save to queue as email trail, but status is set to OK (already sent)
                self::saveToQueue($event,Notification::$typeMessenger,Process::OK);
                //return back result
                return Process::OK;
            }            
        }
        else { //ASYNCHRONOUS mode
            return self::saveToQueue($event,Notification::$typeMessenger);
        }
    }

        
}