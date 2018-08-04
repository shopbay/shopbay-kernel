<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_activity".
 *
 * The followings are the available columns in table 's_activity':
 * @property integer $id
 * @property integer $account_id
 * @property integer $obj_type
 * @property integer $obj_id
 * @property string $obj_url
 * @property string $icon_url
 * @property string $location
 * @property string $event
 * @property string $description
 * @property integer $create_time
 *
 * @author kwlok
 */
class Activity extends SActiveRecord
{
    const EVENT_ACCEPT           = 'accept';
    const EVENT_ACCOUNT_ACTIVATE = 'account-activate';
    const EVENT_ACCOUNT_RESUME   = 'account-resume';
    const EVENT_ACCOUNT_SUSPEND  = 'account-suspend';
    const EVENT_ACTIVATE         = 'activate';
    const EVENT_ADJUST           = 'adjust';
    const EVENT_ANSWER           = 'answer';
    const EVENT_APPLY            = 'apply';
    const EVENT_APPROVE          = 'approve';
    const EVENT_ASK              = 'ask';
    const EVENT_CANCEL           = 'cancel';
    const EVENT_CHANGE           = 'change';
    const EVENT_CHANGE_PASSWORD  = 'change-password';
    const EVENT_CHANGE_EMAIL     = 'change-email';
    const EVENT_CLOSE            = 'close';
    const EVENT_COLLECT          = 'collect';
    const EVENT_COMPOSE          = 'compose';
    const EVENT_CREATE           = 'create';
    const EVENT_DEACTIVATE       = 'deactivate';
    const EVENT_DELETE           = 'delete';
    const EVENT_EDIT             = 'edit';
    const EVENT_FULFILL          = 'fulfill';
    const EVENT_IMPORT           = 'import';
    const EVENT_INVITE           = 'invite';
    const EVENT_JOIN             = 'join';
    const EVENT_LOGIN            = 'login';
    const EVENT_LOGOUT           = 'logout';
    const EVENT_PACK             = 'pack';
    const EVENT_PAY              = 'pay';
    const EVENT_PICK             = 'pick';
    const EVENT_PROCESS          = 'process';
    const EVENT_PUBLISH          = 'published';
    const EVENT_PURCHASE         = 'purchase';
    const EVENT_RECEIVE          = 'receive';
    const EVENT_REFUND           = 'refund';
    const EVENT_REJECT           = 'reject';
    const EVENT_REOPEN           = 'reopen';
    const EVENT_REPLY            = 'reply';
    const EVENT_RETURN           = 'return';
    const EVENT_REQUEST_DEPOSIT  = 'request-deposit';
    const EVENT_REVIEW           = 'review';
    const EVENT_ROLLBACK         = 'rollback';
    const EVENT_SHOP_RESUME      = 'shop-resume';
    const EVENT_SHOP_SUSPEND     = 'shop-suspend';
    const EVENT_SHIP             = 'ship';
    const EVENT_SUBMIT           = 'submit';
    const EVENT_SUBSCRIBE        = 'subscribe';
    const EVENT_LIKE             = 'like';
    const EVENT_DISLIKE          = 'dislike';
    const EVENT_UNJOIN           = 'unjoin';
    const EVENT_UNSUBSCRIBE      = 'unsubscribe';
    const EVENT_UPDATE           = 'update';
    const EVENT_WRITE            = 'write';
    const EVENT_VERIFY           = 'verify';
    /**
     * Returns the static model of the specified AR class.
     * @return News the static model class
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
        return Sii::t('sii','Activity|Activities',[$mode]);
    }   
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_activity';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
            ],
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
                'updateEnable'=>false,
            ],
            'activity' => [//for purpose to get default location
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
            ],     
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],             
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, obj_type, obj_id, obj_url, icon_url, description, event, location', 'required'],
            ['obj_id', 'numerical', 'integerOnly'=>true],
            ['account_id', 'length', 'max'=>12],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 250 chars.
            ['description', 'length', 'max'=>5000],
            ['obj_url, icon_url', 'length', 'max'=>500],
            ['obj_type, event, location', 'length', 'max'=>20],
            
            ['id, account_id, obj_type, obj_id, obj_url, icon_url, description, event, location, create_time', 'safe', 'on'=>'search'],
        ];
    }    
    /**
     * A scope method to return all activities pertaining to Like model
     * @return \Activity
     */
    public function like() 
    {
        $this->atLocation()->getDbCriteria()->mergeWith([
            'condition'=>'obj_type = \''.Like::model()->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * A scope method to return all activities pertaining to Comment model
     * @return \Activity
     */
    public function comment() 
    {
        $this->atLocation()->getDbCriteria()->mergeWith([
            'condition'=>'obj_type = \''.Comment::model()->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * A scope method to return all activities pertaining to Question model
     * @return \Activity
     */
    public function question() 
    {
        $this->atLocation()->getDbCriteria()->mergeWith([
            'condition'=>'obj_type = \''.Question::model()->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * A scope method to return all activities pertaining to ShippingOrder model
     * @return \Activity
     */
    public function order() 
    {
        $this->atLocation()->getDbCriteria()->mergeWith([
            'condition'=>'obj_type = \''.ShippingOrder::model()->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * A scope method to return all activities pertaining to Item model
     * @return \Activity
     */
    public function item() 
    {
        $this->atLocation()->getDbCriteria()->mergeWith([
            'condition'=>'obj_type = \''.Item::model()->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * Operational (Exclude user authentication events)
     * 
     * @override
     * @see SActiveRecord::all()
     * @param type $events
     * @return type
     */
    public function operational() 
    {
        return $this->atLocation()->excludeEvent([
            self::EVENT_ACCOUNT_ACTIVATE,
            self::EVENT_ACCOUNT_RESUME,
            self::EVENT_ACCOUNT_SUSPEND,
            self::EVENT_SHOP_RESUME,
            self::EVENT_SHOP_SUSPEND,
            self::EVENT_LOGIN,
            self::EVENT_LOGOUT,
        ]);
    }
    /**
     * User activites (Exclude user authentication events)
     * 
     * @override
     * @see SActiveRecord::all()
     * @param type $events
     * @return type
     */
    public function admin() 
    {
        return $this->mine();
    }      
    /**
     * User activites (include user authentication events)
     * 
     * @param type $events
     * @return type
     */
    public function users() 
    {
        return $this->notMine()->includeEvent([
            self::EVENT_ACCOUNT_ACTIVATE,
            self::EVENT_ACCOUNT_RESUME,
            self::EVENT_ACCOUNT_SUSPEND,
            self::EVENT_SHOP_RESUME,
            self::EVENT_SHOP_SUSPEND,
            self::EVENT_LOGIN,
            self::EVENT_LOGOUT,
        ]);
    }    
    /**
     * Set location criteria 
     * @param type $location
     * @return type
     */
    public function atLocation($location=null) 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addColumnCondition(['location'=>isset($location)?$location:$this->getDefaultLocation()]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Exclude events 
     * @param type $events
     * @return type
     */
    public function excludeEvent($events=[]) 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addNotInCondition('event',$events);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Include events 
     * @param type $events
     * @return type
     */
    public function includeEvent($events=[]) 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addInCondition('event',$events);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'obj_type' => Sii::t('sii','Object Type'),
            'obj_id' => Sii::t('sii','Object ID'),
            'obj_url' => Sii::t('sii','Object Url'),
            'icon_url' => Sii::t('sii','Icon Url'),
            'event' => Sii::t('sii','Event'),
            'description' =>Sii::t('sii', 'Description'),
            'create_time' => Sii::t('sii','Create Time'),
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
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('obj_url',$this->obj_url,true);
        $criteria->compare('icon_url',$this->icon_url,true);
        $criteria->compare('event',$this->event,true);
        $criteria->compare('location',$this->location,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('create_time',$this->create_time);

        return new CActiveDataProvider($this->mine(), [
            'criteria'=>$criteria,
        ]);
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('activities/view/'.$this->id);
    } 
    public function showUrl() 
    {
        return $this->event != 'delete';
    }
    public function getSummary()
    {
        switch ($this->event) {
            case self::EVENT_ACCEPT:
                $verb = Sii::t('sii','accepted'); break;
            case self::EVENT_ACCOUNT_ACTIVATE:
                $verb = Sii::t('sii','account activated'); break;
            case self::EVENT_ACCOUNT_RESUME:
                $verb = Sii::t('sii','account resumed'); break;
            case self::EVENT_ACCOUNT_SUSPEND:
                $verb = Sii::t('sii','account suspended'); break;
            case self::EVENT_ACTIVATE:
                $verb = Sii::t('sii','activated'); break;
            case self::EVENT_ADJUST:
                $verb = Sii::t('sii','adjusted'); break;
            case self::EVENT_ANSWER:
                $verb = Sii::t('sii','answered'); break;
            case self::EVENT_APPLY:
                $verb = Sii::t('sii','applied'); break;
            case self::EVENT_APPROVE:
                $verb = Sii::t('sii','approved'); break;
            case self::EVENT_ASK:
                $verb = Sii::t('sii','asked'); break;
            case self::EVENT_CANCEL:
                $verb = Sii::t('sii','cancelled'); break;
            case self::EVENT_CHANGE:
                $verb = Sii::t('sii','changed'); break;
            case self::EVENT_CHANGE_EMAIL:
                $verb = Sii::t('sii','email changed'); break;
            case self::EVENT_CHANGE_PASSWORD:
                $verb = Sii::t('sii','password changed'); break;
            case self::EVENT_CLOSE:
                $verb = Sii::t('sii','closed'); break;
            case self::EVENT_COLLECT:
                $verb = Sii::t('sii','collected'); break;
            case self::EVENT_COMPOSE:
                $verb = Sii::t('sii','composed'); break;
            case self::EVENT_CREATE:
                $verb = Sii::t('sii','created'); break;
            case self::EVENT_DEACTIVATE:
                $verb = Sii::t('sii','deactivated'); break;
            case self::EVENT_DELETE:
                $verb = Sii::t('sii','deleted'); break;
            case self::EVENT_EDIT:
                $verb = Sii::t('sii','edited'); break;
            case self::EVENT_FULFILL:
                $verb = Sii::t('sii','fulfilled'); break;
            case self::EVENT_IMPORT:
                $verb = Sii::t('sii','imported'); break;
            case self::EVENT_JOIN:
                $verb = Sii::t('sii','request to join'); break;
            case self::EVENT_INVITE:
                $verb = Sii::t('sii','invited'); break;
            case self::EVENT_LOGIN:
                $verb = Sii::t('sii','logined'); break;
            case self::EVENT_LOGOUT:
                $verb = Sii::t('sii','logout'); break;
            case self::EVENT_PACK:
                $verb = Sii::t('sii','packed'); break;
            case self::EVENT_PAY:
                $verb = Sii::t('sii','paid'); break;
            case self::EVENT_PICK:
                $verb = Sii::t('sii','picked'); break;
            case self::EVENT_PROCESS:
                $verb = Sii::t('sii','processed'); break;
            case self::EVENT_PUBLISH:
                $verb = Sii::t('sii','published'); break;
            case self::EVENT_PURCHASE:
                $verb = Sii::t('sii','purchased'); break;
            case self::EVENT_RECEIVE:
                $verb = Sii::t('sii','received'); break;
            case self::EVENT_REJECT:
                $verb = Sii::t('sii','rejected'); break;
            case self::EVENT_REOPEN:
                $verb = Sii::t('sii','reopened'); break;
            case self::EVENT_REPLY:
                $verb = Sii::t('sii','replied'); break;
            case self::EVENT_REQUEST_DEPOSIT:
                $verb = Sii::t('sii','deposit requested'); break;
            case self::EVENT_REFUND:
                $verb = Sii::t('sii','refunded'); break;
            case self::EVENT_RETURN:
                $verb = Sii::t('sii','returned'); break;
            case self::EVENT_REVIEW:
                $verb = Sii::t('sii','reviewed'); break;
            case self::EVENT_ROLLBACK:
                $verb = Sii::t('sii','rollbacked'); break;
            case self::EVENT_SHOP_RESUME:
                $verb = Sii::t('sii','shop resumed'); break;
            case self::EVENT_SHOP_SUSPEND:
                $verb = Sii::t('sii','shop suspended'); break;
            case self::EVENT_SHIP:
                $verb = Sii::t('sii','shipped'); break;
            case self::EVENT_SUBMIT:
                $verb = Sii::t('sii','submitted'); break;
            case self::EVENT_SUBSCRIBE:
                $verb = Sii::t('sii','subscribed'); break;
            case self::EVENT_UNJOIN:
                $verb = Sii::t('sii','unjoined'); break;
            case self::EVENT_LIKE:
                $verb = Sii::t('sii','liked'); break;
            case self::EVENT_DISLIKE:
                $verb = Sii::t('sii','disliked'); break;
            case self::EVENT_UNSUBSCRIBE:
                $verb = Sii::t('sii','unsubscribed'); break;
            case self::EVENT_UPDATE:
                $verb = Sii::t('sii','updated'); break;
            case self::EVENT_WRITE:
                $verb = Sii::t('sii','wrote'); break;
            case self::EVENT_VERIFY:
                $verb = Sii::t('sii','verified'); break;
            default:
                $verb = $this->event; break;
        }
        return $this->getSummaryTemplate($verb);
    }
    
    protected function getSummaryTemplate($verb) 
    {
        return Sii::t('sii','{object} {verb} {datetime}',['{object}'=>$this->objectName,'{verb}'=>$verb,'{datetime}'=>$this->datetime]);
    }
    protected function getObjectName()
    {
        switch ($this->obj_type) {
            case Like::model()->tableName():                
                $like = Like::model()->findByPk($this->obj_id);
                if ($like->object!=null)
                    return $like->object->displayName();
                else 
                    return $like->displayName();
            default:
                $type = SActiveRecord::resolveTablename($this->obj_type);
                if ($type==null)
                    return Sii::t('sii','Object');
                else{
                    return $type::model()->displayName();
                }
        }
    } 
    public function getObjectThumbnail()
    {
        if (strpos($this->icon_url, 'http') === 0) {
            return CHtml::image($this->icon_url,'Image',['width'=>Image::VERSION_SMALL]);
        }
        else
            return $this->icon_url;
    }   
    /**
     * Return the activity underlying object
     * @return class
     */
    public function getObject()
    {
        $type = SActiveRecord::resolveTablename($this->obj_type);
        return $type::model()->findByPk($this->obj_id);
    }       
    
    protected function getDatetime() 
    {
        return strtolower(Helper::prettyDate($this->create_time));
    }
    
    public function displayDescription($locale,$trimlen=250)
    {
        //first based on current user locale
        $desc = $this->displayLanguageValue('description',$locale);
        if ($desc==Sii::t('sii','unset')){
            //fallback to activity object locale
            if ($this->object!=null){
                //logTrace(__METHOD__,$this->object->attributes);
                $desc = $this->displayLanguageValue('description',$this->object->getLocale());
            }
        }
        return Helper::rightTrim(Helper::purify($desc),$trimlen);
    }
}
