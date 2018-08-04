<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_process".
 *
 * The followings are the available columns in table 's_process':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $color
 * @property string $text
 *
 * @author kwlok
 */
class Process extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Process the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Process|Processes',array($mode));
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_process';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('name, description, text', 'required'),
            array('name, text, color', 'length', 'max'=>20),
            array('description', 'length', 'max'=>100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, description, color, text', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'name' => Sii::t('sii','Name'),
            'description' => Sii::t('sii','Description'),
            'color' => Sii::t('sii','Color'),
            'text' => Sii::t('sii','Text'),
        ];
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('color',$this->color,true);
        $criteria->compare('text',$this->text,true);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    /*
     * @var constant for Processes of Shopping Cart
     */
    const CART                   = 'CART;';
    const CHECKOUT               = 'CHK;';
    const CHECKOUT_ADDRESS       = 'CHK;ADDR;';
    const CHECKOUT_PAYMENT       = 'CHK;PY;';
    const CHECKOUT_CONFIRM       = 'CHK;CFM;';
    /**
     * According to s_workflow definition, this is the first process of order purchase 
     * Applies to s_order and s_item
     * @var constant for Processes of Item, Order
     */
    const COMPLETED              = 'COM;';
    const UNPAID                 = 'U;';//payment unpaid
    const DEFERRED               = 'D;';//deferred payment
    const PAID                   = 'PY;';
    const ORDERED                = 'O;';
    const PICKED_ACCEPT          = 'PI;AC;';
    const DEFERRED_PICKED_ACCEPT = 'D;PI;AC;';
    const PICKED_REJECT          = 'PI;RJ;';
    const PACKED_ACCEPT          = 'PA;AC;';
    const DEFERRED_PACKED_ACCEPT = 'D;PA;AC;';
    const PACKED_REJECT          = 'PA;RJ;';
    const SHIPPED                = 'S;';
    const COLLECTED              = 'C;';
    const RECEIVED               = 'RC;';     
    const REVIEWED               = 'RV;';
    const RETURNED_PENDING       = 'RT;PD;';
    const RETURNED_ACCEPT        = 'RT;AC;';
    const RETURNED_REJECT        = 'RT;RJ;';
    const CANCELLED              = 'CCL;';//this cancel requires refund
    const DEFERRED_CANCELLED     = 'D;CCL;';//this cancel does not require refund
    const REFUND                 = 'RF;';
    const ORDER_FULFILLED        = 'O;FFL;';
    const ORDER_PARTIAL_FULFILLED= 'O;FFL;P;';
    const ORDER_REJECTED         = 'O;RJ;';
    /**
     * @var constant for Processes of News
     */
    const NEWS_OFFLINE          = 'NW;OFF;';        
    const NEWS_ONLINE           = 'NW;ON;';
    /**
     * @var constant for Processes of Question
     */
    const ASK                   = 'ASK;';
    const ASKED                 = 'Q;';
    const QUESTION_OFFLINE      = 'Q;OFF;';    
    const QUESTION_ONLINE       = 'Q;ON;';
    /**
     * @var constant for Processes of Account
     */
    const ACTIVE                = 'A;ON;';//activated and login at least once
    const INACTIVE              = 'A;OFF;';//inactivated 
    const PRESIGNUP             = 'A;PRESIGN;';//pending signup
    const SIGNUP                = 'A;SIGN;';//pending activating
    const SUSPEND               = 'A;SUSP;';//suspended
    const PASSWORD_RESET        = 'A;PWD;';
    const EMAIL_RESET           = 'A;EML;';
    const ACCOUNT_NEW           = 'A;NEW;';//newly created account, pending activating and must change password
    const ACCOUNT_NEW_PASSWORD  = 'A;NEW;PWD;';//newly created account, pending change password
    const ACCOUNT_CLOSED        = 'A;CLOSED;';//account closed
    /**
     * @var constant for Processes of Shop
     */
    const SHOP_REQUEST          = 'SHOP;REQ;';
    const SHOP_PENDING          = 'SHOP;PD;';
    const SHOP_APPROVED         = 'SHOP;AC;';
    const SHOP_REJECTED         = 'SHOP;RJ;';
    const SHOP_PROTOTYPE        = 'SHOP;PTT;';
    const SHOP_ONLINE           = 'SHOP;ON;';
    const SHOP_OFFLINE          = 'SHOP;OFF;';
    const SHOP_SUSPENDED        = 'SHOP;SUSP;';
    /**
     * @var constant for Processes of Product
     */
    const PRODUCT_ONLINE        = 'PRD;ON;';
    const PRODUCT_OFFLINE       = 'PRD;OFF;';
    const PRODUCT_INVENTORY_LOW = 'PRD;LOW;';
    /**
     * @var constant for Processes of Campaign
     */
    const CAMPAIGN_ONLINE       = 'CPG;ON;';
    const CAMPAIGN_OFFLINE      = 'CPG;OFF;';
    /**
     * @var constant for Processes of Shipping
     */
    const SHIPPING_ONLINE       = 'SHP;ON;';
    const SHIPPING_OFFLINE      = 'SHP;OFF;';
    /**
     * @var constant for Processes of Payment Method (Shop)
     */
    const PAYMENT_METHOD_ONLINE = 'PAY;ON;';
    const PAYMENT_METHOD_OFFLINE= 'PAY;OFF;';
    /**
     * @var constant for Processes of Tax (Shop)
     */
    const TAX_ONLINE            = 'TAX;ON;';
    const TAX_OFFLINE           = 'TAX;OFF;';
    /**
     * @var constant for Processes of Media
     */
    const MEDIA_ONLINE          = 'M;ON;';
    const MEDIA_OFFLINE         = 'M;OFF;';
    /**
     * @var constant for Processes of Tutorial
     */
    const TUTORIAL_DRAFT        = 'T;D;';
    const TUTORIAL_SUBMITTED    = 'T;S;';    
    const TUTORIAL_PUBLISHED    = 'T;P;';
    /**
     * @var constant for Processes of Tutorial Series
     */
    const TUTORIAL_SERIES_DRAFT     = 'TS;D;';
    const TUTORIAL_SERIES_SUBMITTED = 'TS;S;';    
    const TUTORIAL_SERIES_PUBLISHED = 'TS;P;';
    /**
     * @var constant for Processes of Ticket
     */
    const TICKET_DRAFT          = 'TKT;D;';
    const TICKET_SUBMITTED      = 'TKT;S;';    
    const TICKET_CLOSED         = 'TKT;C;';
    const TICKET_REPLIED        = 'TKT;R;';
    /**
     * @var constant for Processes of Plan
     */
    const PLAN_DRAFT            = 'P;D;';
    const PLAN_SUBMITTED        = 'P;S;';    
    const PLAN_APPROVED         = 'P;A;';
    /**
     * @var constant for Processes of Package
     */
    const PACKAGE_DRAFT         = 'PKG;D;';
    const PACKAGE_SUBMITTED     = 'PKG;S;';    
    const PACKAGE_APPROVED      = 'PKG;A;';
    /**
     * @var constant for Processes of Subscription
     */
    const SUBSCRIPTION_PENDING  = 'SUB;PD;';
    const SUBSCRIPTION_ACTIVE   = 'SUB;A;';
    const SUBSCRIPTION_EXPIRED  = 'SUB;EXP;';
    const SUBSCRIPTION_PASTDUE  = 'SUB;PDUE;';
    const SUBSCRIPTION_CANCELLED= 'SUB;CCL;';
    const SUBSCRIPTION_SUSPENDED= 'SUB;SUSP;';    
    const SUBSCRIPTION_PENDING_CANCEL  = 'SUB;PCCL;';
    /**
     * @var constant for Processes of Chatbot
     */
    const CHATBOT_ONLINE          = 'BOT;ON;';
    const CHATBOT_OFFLINE         = 'BOT;OFF;';
    /**
     * @var constant for Processes of Notification Subscription
     */
    const NOTIFICATION_SUBSCRIBED     = 'N;SUB;';
    const NOTIFICATION_UNSUBSCRIBED   = 'N;USUB;';
    /**
     * @var constant for Processes of Page
     */
    const PAGE_ONLINE          = 'PG;ON;';
    const PAGE_OFFLINE         = 'PG;OFF;';
    /**
     * @var constant for Processes of Theme
     */
    const THEME_ONLINE          = 'TH;ON;';
    const THEME_OFFLINE         = 'TH;OFF;';    
    /**
     * @var constant for common use
     */
    const YES                   = 'Y';
    const NO                    = 'N';
    const START                 = 'START';
    const END                   = 'END';
    const HOLD                  = 'HOLD';
    const OK                    = 'OK';
    const ERROR                 = 'ERROR';
    const INTERRUPT             = 'IRPT;';
    const PROCESSIMG            = 'PRC;';
    const CACHE                 = 'P;CACHE;';
    const DELETED               = 'DEL;';//soft delete status

    public static function getId($process)
    {
        $record=Yii::app()->cache->get(self::CACHE.$process);
        if($record===false)
            $record = Process::_cache($process);
        return $record->id;
    }
    public static function getDescription($process)
    {
        $record=Yii::app()->cache->get(self::CACHE.$process);
        if($record===false)
            $record = Process::_cache($process);
        return $record->description;
    }
    public static function getColor($process)
    {
        $record=Yii::app()->cache->get(self::CACHE.$process);
        if($record===false)
            $record = Process::_cache($process);
        return $record->color;
    }
    /**
     * Get text returns text found from DB
     * @param type $process
     * @return type
     */
    public static function getText($process)
    {
        $record=Yii::app()->cache->get(self::CACHE.$process);
        if($record===false)
            $record = Process::_cache($process);
        return $record->text;
    }
    /**
     * Display text with color supports text localization
     * @param type $process
     * @return type
     */
    public static function getDisplayTextWithColor($process)
    {
        $record=Yii::app()->cache->get(self::CACHE.$process);
        if($record===false)
            $record = Process::_cache($process);
        if ($record==null)
            return array('text'=>$process,'color'=>'black');
        else
            return array('text'=>Process::getDisplayText($record->text),'color'=>$record->color);
    }
    
    public static function getHtmlDisplayText($process)
    {
        return Helper::htmlColorText(Process::getDisplayTextWithColor($process));
    }
    public static function getNameByDesc($desc)
    {
        $criteria=new CDbCriteria;
        $criteria->select='name';
        $criteria->condition='description=\''.$desc.'\'';
        return Process::model()->find($criteria)->name;
    }        
    
    /**
     * Display text with color supports text localization
     * @param type $text
     * @return type
     */
    public static function getTextInColor($text)
    {
        $record=Yii::app()->cache->get(self::CACHE.$text);
        if($record===false)
            $record = Process::_cacheColorMap($text);
        return array('text'=>$text,'color'=>$record->color);
    }
    
    private static function _cacheColorMap($text)
    {
        $criteria=new CDbCriteria;
        $criteria->distinct = true;
        $criteria->condition='text=\''.$text.'\'';
        $record = Process::model()->find($criteria);
        Yii::app()->cache->set(self::CACHE.$text , $record);
        return $record; 
    }
    
    private static function _cache($process)
    {
        $criteria=new CDbCriteria;
        $criteria->condition='name=\''.$process.'\'';
        $record = Process::model()->find($criteria);
        Yii::app()->cache->set(self::CACHE.$process , $record);
        return $record; 
    }
    /**
     * Retrieve process display text and color as defined in table s_process
     * 
     * @return type
     */
    public static function getList()
    {
        return Yii::app()->db->createCommand()
                ->selectDistinct('text,color')
                ->from(Process::model()->tableName())
                ->queryAll();
    }   
    /**
     * Return localized display process text
     * @param type $text
     * @param type $locale
     */
    public static function getDisplayText($text,$locale=null)
    {
        switch ($text) {
            case 'Online':
                return Sii::tl('sii','Online',$locale);
            case 'Offline':
                return Sii::tl('sii','Offline',$locale);
            case 'Submitted':
                return Sii::tl('sii','Submitted',$locale);
            case 'Unpaid':
                return Sii::tl('sii','Unpaid',$locale);
            case 'Deferred':
                return Sii::tl('sii','Cash On Delivery',$locale);
            case 'Paid':
                return Sii::tl('sii','Paid',$locale);
            case 'Ordered':
                return Sii::tl('sii','Ordered',$locale);
            case 'Cancelled':
                return Sii::tl('sii','Cancelled',$locale);
            case 'Picked':
                return Sii::tl('sii','Picked',$locale);
            case 'Out of Stock':
                return Sii::tl('sii','Out of Stock',$locale);
            case 'Packed':
                return Sii::tl('sii','Packed',$locale);
            case 'QC Failed':
                return Sii::tl('sii','QC Failed',$locale);
            case 'Shipped':
                return Sii::tl('sii','Shipped',$locale);
            case 'Collected':
                return Sii::tl('sii','Collected',$locale);
            case 'Received':
                return Sii::tl('sii','Received',$locale);
            case 'Reviewed':
                return Sii::tl('sii','Reviewed',$locale);
            case 'Returned':
                return Sii::tl('sii','Returned',$locale);
            case 'Pending':
                return Sii::tl('sii','Pending',$locale);
            case 'Pending Return':
                return Sii::tl('sii','Pending Return',$locale);
            case 'Rejected':
                return Sii::tl('sii','Rejected',$locale);
            case 'Fulfilled':
                return Sii::tl('sii','Fulfilled',$locale);
            case 'Partial Fulfilled':
                return Sii::tl('sii','Partial Fulfilled',$locale);
            case 'Refunded':
                return Sii::tl('sii','Refunded',$locale);
            case 'Approved':
                return Sii::tl('sii','Approved',$locale);
            case 'Accepted':
                return Sii::tl('sii','Accepted',$locale);
            case 'Asked':
                return Sii::tl('sii','Asked',$locale);
            case 'Closed':
                return Sii::tl('sii','Closed',$locale);
            case 'Suspended':
                return Sii::tl('sii','Suspended',$locale);
            case 'Active':
                return Sii::tl('sii','Active',$locale);
            case 'Expired':
                return Sii::tl('sii','Expired',$locale);
            case 'Complete':
                return Sii::tl('sii','Sent',$locale);
            case 'Join Request':
                return Sii::tl('sii','Join Request',$locale);
            case 'Invitation':
                return Sii::tl('sii','Invitation',$locale);
            case 'unset':
                return Sii::tl('sii','unset',$locale);
            default:
                return $text;
        }
    }
    /**
     * Return localized display process action text
     * @param type $action
     */
    public static function getActionText($action)
    {
        switch ($action) {
            case WorkflowManager::ACTION_ACTIVATE:
                return Sii::t('sii','Activate');
            case WorkflowManager::ACTION_DEACTIVATE:
                return Sii::t('sii','Deactivate');
            case WorkflowManager::ACTION_PAY:
                return Sii::t('sii','Pay');
            case WorkflowManager::ACTION_VERIFY:
                return Sii::t('sii','Verify');
            case WorkflowManager::ACTION_REPAY:
                return Sii::t('sii','Repay');
            case WorkflowManager::ACTION_PURCHASE:
                return Sii::t('sii','Purchase');
            case WorkflowManager::ACTION_DELIVER:
                return Sii::t('sii','Deliver');
            case WorkflowManager::ACTION_PROCESS:
                return Sii::t('sii','Process');
            case WorkflowManager::ACTION_REFUND:
                return Sii::t('sii','Refund');
            case WorkflowManager::ACTION_PICK:
                return Sii::t('sii','Pick');
            case WorkflowManager::ACTION_PACK:
                return Sii::t('sii','Pack');
            case WorkflowManager::ACTION_SHIP:
                return Sii::t('sii','Ship');
            case WorkflowManager::ACTION_RECEIVE:
                return Sii::t('sii','Receive');
            case WorkflowManager::ACTION_REVIEW:
                return Sii::t('sii','Review');
            case WorkflowManager::ACTION_RETURN:
            case WorkflowManager::ACTION_RETURNITEM:
                return Sii::t('sii','Return');
            case WorkflowManager::ACTION_ROLLBACK:
                return Sii::t('sii','Rollback');
            case WorkflowManager::ACTION_ANSWER:
                return Sii::t('sii','Answer');
            case WorkflowManager::ACTION_SUBMIT:
                return Sii::t('sii','Submit');
            case WorkflowManager::ACTION_ACCEPT:
                return Sii::t('sii','Accept');
            default:
                return $action;
        }
    }
    /**
     * Return localized display process action decision text
     * @param type $decision
     */
    public static function getDecisionText($decision)
    {
        switch ($decision) {
            case WorkflowManager::DECISION_FULFILL:
                return Sii::t('sii','Fulfill');
            case WorkflowManager::DECISION_HASSTOCK:
                return Sii::t('sii','Has Stock');
            case WorkflowManager::DECISION_NOSTOCK:
                return Sii::t('sii','Out of Stock');
            case WorkflowManager::DECISION_ACCEPT:
                return Sii::t('sii','Accept');
            case WorkflowManager::DECISION_REJECT:
                return Sii::t('sii','Reject');
            case WorkflowManager::DECISION_SHIP:
                return Sii::t('sii','Ship');
            case WorkflowManager::DECISION_COLLECT:
                return Sii::t('sii','Collect');
            case WorkflowManager::DECISION_PAY:
                return Sii::t('sii','Pay');
            case WorkflowManager::DECISION_REPAY:
                return Sii::t('sii','Repay');
            case WorkflowManager::DECISION_CANCEL:
                return Sii::t('sii','Cancel');
            case WorkflowManager::DECISION_RECEIVE:
                return Sii::t('sii','Receive');
            case WorkflowManager::DECISION_REVIEW:
                return Sii::t('sii','Review');
            case WorkflowManager::DECISION_RETURN:
                return Sii::t('sii','Return');
            default:
                return $decision;
        }
    }    
}