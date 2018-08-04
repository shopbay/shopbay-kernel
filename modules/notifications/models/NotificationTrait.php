<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.notifications.models.NotificationBatchJob");
/**
 * Description of NotificationTrait
 * Below gives the definition of each configuration item of notification.
 * The detailed configuration of notification medium type, recipient type and template used are defined at table `s_notification`
 * There are Two way of notifiation triggers: event-based or non-event based.
 * (1) Event-based: When column 'event' is not null. The notification is linked to {@link Transitional} object and its triggered when the model status hits the 'event' (equal to event); 
 *                  $model is available as view data in template file for retrieving model data
 * (2) Non-Event: When column 'event' is null. The notification is static and subscription based. It is triggered by job scheduler based on execution time 
 *                  NotificationSubscription::getViewData() is available in template file for reference
 * 
 * @see templates {@link common.modules.notifications.templates}
 * 
 * @author kwlok
 */
trait NotificationTrait 
{
    /**
     * Notification medium type (send notification by xxx)
     */
    public static $typeMessage       = 0;//portal inbox message
    public static $typeEmail         = 1;
    public static $typeSms           = 2;
    public static $typeMessenger     = 3;//facebook messenger
    /**
     * Modes of recipient types (to determine how to get recipient id/email):
     */
    /**
     * Recipient derived by model attribute; Attribute is set in column $recipient; 
     * E.g For model Question, $recipient=account_id, so deriving recipient id = $question->account_id
     */
    public static $recipientUser        = 0;
    /**
     * Recipient derived by model object associated with it(normally defined in method relations()); 
     * E.g For model Product, $recipient=shop.account_id, so deriving recipient id = $product->shop->account_id
     */
    public static $recipientObjectowner = 1;
    /**
     * Recipient derived by the role user assigned to 
     * E.g Adminstrator, so user with Administrator role will receive email
     */
    public static $recipientRole        = 2;
    /**
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
     */
    public static $recipientClassmethod = 3;
    /*
     * List of supported notifications (refer to `s_notification` for recipient types)
     */
    /**
     * Notification triggered when a question is asked
     */
    public static $questionAsk = 'question_ask';
    /**
     * Notification triggered when a question is answered
     */
    public static $questionAnswer = 'question_answer';
    /*
     * Notification triggered when a shop application request is sent
     */
    public static $shopRequest = 'shop_request';
    /*
     * Notification triggered when a shop application request is approved
     */
    public static $shopApprove = 'shop_approve';
    /*
     * Notification triggered when a shop application request is rejected
     */
    public static $shopReject = 'shop_reject';
    /*
     * A welcome message notification triggered when account successfuly signup for first time
     */
    public static $accountWelcome = 'account_welcome';
    /*
     * A account activation notification triggered when account first time signup (to proceed how to activate account)
     */
    public static $accountActivate = 'account_activate';
    /*
     * Notification triggered when user reset password (forgotten password)
     */
    public static $accountResetpassword = 'account_resetpassword';
    /*
     * Notification triggered when user reset email (change email)
     */
    public static $accountResetemail = 'account_resetemail';
    /*
     * Account activation notification triggered when account first time signup using social media account (to proceed how to activate account)
     */
    public static $accountPresignup = 'account_presignup';
    /*
     * Account activation notification triggered when account is created by admin 
     */
    public static $accountNew = 'account_new';
    /*
     * Account activation notification triggered when account is closed 
     */
    public static $accountClosed = 'account_closed';
    /*
     * Notification triggered when an order is placed but unpaid (notify buyer to make payment)
     */
    public static $orderPay = 'order_pay';
    /*
     * Notification triggered when an order is placed but unpaid (notify seller there is an order pending payment)
     */
    public static $orderUnpaid = 'order_unpaid';
    /*
     * Notification triggered when an order is paid but pending payment verification (notify buyer)
     */
    public static $orderPending = 'order_pending';
    /*
     * Notification triggered when an order is paid but pending payment verification (notify seller)
     */
    public static $orderVerify = 'order_verify';
    /*
     * Notification triggered when an order is paid and confirmed
     */
    public static $orderConfirm = 'order_confirm';
    /*
     * Notification triggered when an order payment verification is failed
     */
    public static $orderReject = 'order_reject';
    /*
     * Notification triggered when an order is cancelled (if seller cancel, notify buyer; if buyer cancel, notify seller)
     */
    public static $orderCancel = 'order_cancel';
    /*
     * Notification triggered when an shipping order is being processed by seller
     */
    public static $shippingorderProcess = 'shippingorder_process';
    /*
     * Notification triggered when an shipping order is refunded
     */
    public static $shippingorderRefund = 'shippingorder_refund';
    /*
     * Notification triggered when an item is shipped 
     */
    public static $itemShip = 'item_ship';
    /*
     * Notification triggered when an item is received 
     */
    public static $itemReceive = 'item_receive';
    /*
     * Notification triggered when an item is reviewed 
     */
    public static $itemReview = 'item_review';
    /*
     * Notification triggered when an item is refunded 
     */
    public static $itemRefund = 'item_refund';
    /*
     * Notification triggered when an item is requested for return 
     */
    public static $itemReturn = 'item_return';
    /*
     * Notification triggered when an item return request is approved
     */
    public static $itemReturnApprove = 'item_return_approve';
    /*
     * Notification triggered when an item return request is rejected
     */
    public static $itemReturnReject = 'item_return_reject';
    /*
     * Notification triggered when an item is canceled
     */
    public static $itemCancel = 'item_cancel';
    /*
     * Notification triggered when a message (inbox) is replied
     */
    public static $messageReply = 'message_reply';
    /*
     * Notification triggered when a tutorial is submitted
     */
    public static $tutorialSubmit = 'tutorial_submit';
    /*
     * Notification triggered when inventory hits low stock level
     */
    public static $inventoryLow = 'inventory_low';
    /*
     * Notification triggered when a ticket is submitted
     */
    public static $ticketSubmit = 'ticket_submit';
    /*
     * Notification triggered when a ticket is replied
     */
    public static $ticketReply = 'ticket_reply';
    /*
     * Notification triggered when a subscription is confirmed
     */
    public static $subscriptionConfirm = 'subscription_confirm';
    /*
     * Notification triggered when a subscription is canceled
     */
    public static $subscriptionCancel = 'subscription_cancel';
    /*
     * Notification triggered when a subscription is past due
     */
    public static $subscriptionPastdue = 'subscription_pastdue';
    /*
     * Notification triggered when a subscription is suspended
     */
    public static $subscriptionSuspend = 'subscription_suspended';
    /*
     * Notification triggered when on subscription basis - when user subscribes to this notification
     */
    public static $productUpdates = 'product_updates';
    /**
     * Notification display names
     * @return array
     */
    public static function siiName()
    {
        return [
            //for notification name
            static::$productUpdates => Sii::t('sii','Daily Product Updates'), 
            static::$orderConfirm => Sii::t('sii','Order Receipt'),
            static::$itemShip => Sii::t('sii','Shipping Notice'),
            //for notification channels
            static::$typeMessage => Sii::t('sii','Inbox Message'), 
            static::$typeEmail => Sii::t('sii','Email'), 
            static::$typeMessenger => Sii::t('sii','Messenger'), 
        ];
    }      /**
    /**
     * Notification display description
     * @return array
     */
    public static function siiDescription()
    {
        return [
            static::$productUpdates => Sii::t('sii','Be the first to know our new arrivals in the last 24 hours'), 
            static::$orderConfirm => Sii::t('sii','Get instant order confirmation on Messenger'), 
            static::$itemShip => Sii::t('sii','Get notified when your item has been shipped'),
        ];
    }      
    /**
     * Notfication subscriptions config
     * 
     * NOTE (1):
     * The configuration will be inserted into NotficationSubscription $params when get subscribed by user
     * @see NotificationManager::subscribe()
     * 
     * NOTE (2):
     * IMPORTANT! For notification to be able to pickup by scheduled job, it must contain param "batch" equals to true
     * @see NotificationBatchJob
     * @see SubsriberCommand
     * 
     * @return array
     */
    public static function subscriptionsConfig()
    {
        return [
            static::$productUpdates => [
                'alias'=>'s1',//a shorter name for OptIn payload
                'batch'=>true,//IMPORTANT! True so that scheduled job can pick up for batch run
                'hours'=>24,
                'frequency'=>NotificationBatchJob::$frequencyDaily,
                'start_time'=>'0815',//time (not earlier than) to send out updates, 24 hours format (hhmm), 1800
            ], 
            static::$orderConfirm => [
                'alias'=>'s2',//a shorter name for OptIn payload
                'accountRequired'=>true,
            ],
            static::$itemShip => [
                'alias'=>'s3',//a shorter name for OptIn payload
                'accountRequired'=>true,
            ],
        ];
    }      /**
     * Return email templates
     * @param type $key
     * @return type
     */
    public static function getEmailTemplates($key=null)
    {
        if (!isset($key)){
            return [
                'email.account.welcome.merchant'=>Sii::t('sii','Welcome Message - Merchant'),
                'email.account.welcome.customer'=>Sii::t('sii','Welcome Message - Customer'),
                'email.account.resetemail'=>Sii::t('sii','Account Email Reset'),
                'email.account.resetpassword'=>Sii::t('sii','Password Reset'),
                'email.account.activate'=>Sii::t('sii','Account Activation'),
                'email.shop.request'=>Sii::t('sii','Shop Application Request'),
                'email.shop.approve'=>Sii::t('sii','Shop Application Approval Notice'),
                'email.shop.reject'=>Sii::t('sii','Unsuccessful Shop Application Notice'),
                'email.order.pay'=>Sii::t('sii','Order Pending Payment'),
                'email.order.pending'=>Sii::t('sii','Order Pending Payment Verification'),
                'email.order.pending_merchant'=>Sii::t('sii','Order Pending Payment Verification by Merchant'),
                'email.order.confirm'=>Sii::t('sii','Order Confirmation'),
                'email.order.cancel'=>Sii::t('sii','Order Cancellation'),
                'email.order.unpaid'=>Sii::t('sii','Order Unpaid'),
                'email.order.reject'=>Sii::t('sii','Unsuccessful Order Payment Verification'),
                'email.shippingorder.refund'=>Sii::t('sii','Shipping Order Refund Notice'),
                'email.shippingorder.process'=>Sii::t('sii','Shipping Order Pending Process'),
                'email.item.ship'=>Sii::t('sii','Item Shipped'),
                'email.item.receive'=>Sii::t('sii','Item Received'),
                'email.item.cancel'=>Sii::t('sii','Item Canceled'),
                'email.item.refund'=>Sii::t('sii','Item Refunded'),
                'email.item.review'=>Sii::t('sii','Item Reviewed'),
                'email.item.returnrequest'=>Sii::t('sii','Item Return Request'),
                'email.item.returnapprove'=>Sii::t('sii','Item Return Approval'),
                'email.item.returnreject'=>Sii::t('sii','Item Return Rejection'),
                'email.question.ask'=>Sii::t('sii','Question Asked'),
                'email.question.answer'=>Sii::t('sii','Question Answered'),
                'email.tutorial.submit'=>Sii::t('sii','Tutorial Submitted'),
                'email.ticket.submit'=>Sii::t('sii','Ticket Submitted'),
                'email.ticket.reply'=>Sii::t('sii','Ticket Replied'),
                'email.inventory.lowstock'=>Sii::t('sii','Low Inventory Notice'),
            ];
        }
        else {
            $templates =  self::getEmailTemplates();
            return $templates[$key];
        }
    }
    /**
     * Return message templates
     * @param type $key
     * @return type
     */
    public static function getMessageTemplates($key=null)
    {
        if (!isset($key)){
            return [
                'message.account.welcome.merchant'=>Sii::t('sii','Welcome Message - Merchant'),
                'message.account.welcome.customer'=>Sii::t('sii','Welcome Message - Customer'),
                'message.shop.approve'=>Sii::t('sii','Shop Application Approval Notice'),
                'message.shop.reject'=>Sii::t('sii','Unsuccessful Shop Application Notice'),
                'message.shop.request'=>Sii::t('sii','Shop Application Request'),
                'message.order.pay'=>Sii::t('sii','Order Pending Payment'),
                'message.order.pending'=>Sii::t('sii','Order Pending Payment Verification'),
                'message.order.pending_merchant'=>Sii::t('sii','Order Pending Payment Verification by Merchant'),
                'message.order.confirm'=>Sii::t('sii','Order Confirmation'),
                'message.order.cancel'=>Sii::t('sii','Order Cancellation'),
                'message.order.reject'=>Sii::t('sii','Unsuccessful Order Payment Verification'),
                'message.shippingorder.refund'=>Sii::t('sii','Shipping Order Refund Notice'),
                'message.shippingorder.process'=>Sii::t('sii','Shipping Order In Process'),
                'message.item.ship'=>Sii::t('sii','Item Shipped'),
                'message.item.receive'=>Sii::t('sii','Item Received'),
                'message.item.cancel'=>Sii::t('sii','Item Canceled'),
                'message.item.refund'=>Sii::t('sii','Item Refunded'),
                'message.item.review'=>Sii::t('sii','Item Reviewed'),
                'message.item.returnrequest'=>Sii::t('sii','Item Return Request'),
                'message.item.returnapprove'=>Sii::t('sii','Item Return Approval'),
                'message.item.returnreject'=>Sii::t('sii','Item Return Rejection'),
                'message.question.ask'=>Sii::t('sii','Question Asked'),
                'message.question.answer'=>Sii::t('sii','Question Answered'),
                'message.tutorial.submit'=>Sii::t('sii','Tutorial Submitted'),
                'message.ticket.submit'=>Sii::t('sii','Ticket Submitted'),
                'message.ticket.reply'=>Sii::t('sii','Ticket Replied'),
                'message.inventory.lowstock'=>Sii::t('sii','Low Inventory Notice'),
            ];
        }
        else {
            $templates =  self::getMessageTemplates();
            return $templates[$key];
        }
    }
    
    public static function getTemplateName($key)
    {
        $templates =  self::getEmailTemplates();
        if (isset($templates[$key]))
            return $templates[$key];
        $templates =  self::getMessageTemplates();
        if (isset($templates[$key]))
            return $templates[$key];
        return '';
    }    
    /**
     * parse notification subject based on Sii translation if any
     * @param type $model
     * @param type $field
     * @param type $trim
     * @return type
     */
    public static function parseSubject($model,$field,$trim=false) 
    {
        //[1]Search token within {x}
        $rexp = '/{([a-zA-Z\_\-]*)}/i';
        if (!preg_match_all($rexp, $field, $match))
            logInfo(__METHOD__.' No field matched!');

        $localeAttributes = ['name'];
        $locale = self::parseModelLocale($model);
        $pattern = [];
        $replacement = [];
        for ($i=0; $i < count($match[0]); $i++) {
            $pattern[$i] =  '/'.$match[0][$i].'/';
            //if replacement is a locale attribute, use localeBehavior
            if (in_array($match[1][$i],$localeAttributes) && $locale!=false) {
                logTrace(__METHOD__.' locale attribute:'.$match[1][$i].', '.$locale);
                if ($trim==true)
                    $replacement['{'.$match[1][$i].'}'] = '"'.Helper::rightTrim($model->displayLanguageValue($match[1][$i],$locale), 50).'"';
                else
                    $replacement['{'.$match[1][$i].'}'] = $model->displayLanguageValue($match[1][$i],$locale);
            }
            else if ($match[1][$i]=='app'){
                $replacement['{app}'] = param('SITE_NAME');
            }
            elseif ($model instanceof SActiveRecord) {//direct replacement (string to string)
                logTrace(__METHOD__.' replacing field {'.$match[1][$i].'}', $model->{$match[1][$i]});
                if ($trim==true)
                    $replacement['{'.$match[1][$i].'}'] = '"'.Helper::rightTrim ($model->{$match[1][$i]}, 50).'"';
                else
                    $replacement['{'.$match[1][$i].'}'] = $model->{$match[1][$i]};
            }
            //for static or null model, no replacement
        }
        
        if ($locale!=false){
            return Sii::tp('sii',$field,$replacement,$locale);
        }
        else
            return Sii::t('sii',$field,$replacement);
    }  
    /**
     * Parse the corresponding model locale
     * @param SActiveRecord $model
     * @return mixed model locale or false if not found
     */
    protected static function parseModelLocale($model)
    {
        if (is_string($model)){
            return false;//just model classname and not active record, return false,
        }
        elseif (property_exists(get_class($model),'locale')){
            logTrace(__METHOD__.' '.get_class($model).' class has property locale');
            return $model->locale;
        }
        else if ($model instanceof SActiveRecord && $model->hasBehaviors('locale')){
            logTrace(__METHOD__.' '.get_class($model).' class has method getLocale()');
            return $model->getLocale();
        }
        else
            return false;
    }    
    /**
     * Replace notification body content
     * @param type $model
     * @param type $field
     * @param type $trim
     * @return type
     */
    protected static function replaceContent($model,$field,$trim=false) 
    {
        //Search token within {x}
        $rexp = '/{([a-zA-Z\_\-]*)}/i';
        if (!preg_match_all($rexp, $field, $match))
            logInfo('No field matched');

        $pattern = array();
        $replacement = array();
        for ($i=0; $i < count($match[0]); $i++) {
            $pattern[$i] =  '/'.$match[0][$i].'/';
            
            if ($trim==true)
                $replacement[$i] = '"'.Helper::rightTrim ($model->$match[1][$i], 50).'"';
            else
                $replacement[$i] = $model->$match[1][$i];

        }

        //1 - Replace token within {x}
        //2 - Strip of { or }
        $content = preg_replace($pattern, $replacement, $field);
        if ($content==null)
            logWarning ('No field replaced');
        return preg_replace('/({|})/', ' ', $content);
    }    
    /**
     * Encode notification as key string
     * @param type $name Notification name
     * @param type $channel
     * @return type
     */
    public static function encodeKey($name,$channel)
    {
        return $name.Helper::PIPE_SEPARATOR.$channel;
    }
    /**
     * Decode notification key
     * @param type $key
     * @return \stdClass
     */
    public static function decodeKey($key)
    {
        $notification = new stdClass();
        foreach (explode(Helper::PIPE_SEPARATOR, $key) as $key => $value) {
            if ($key==0)
                $notification->name = $value;
            elseif ($key==1)
                $notification->type = $value;
        };
        return $notification;
    }     
}
