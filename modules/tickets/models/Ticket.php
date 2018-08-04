<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.services.workflow.models.Transitionable");
Yii::import('common.modules.shops.behaviors.ShopParentBehavior');
/**
 * This is the model class for table "s_ticket".
 *
 * The followings are the available columns in table 's_ticket':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $subject
 * @property string $content
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Ticket extends Transitionable
{
    const DEMO_TICKET = -1;
    const REPLY_SUBJECT_PREFIX = 'RE:';
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
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
        return Sii::t('sii','Ticket|Tickets',array($mode));
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_ticket';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
              'class'=>'common.components.behaviors.TimestampBehavior',
            ),
            'account' => array(
              'class'=>'common.components.behaviors.AccountBehavior',
            ), 
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ),    
            'content' => array(
                'class'=>'common.components.behaviors.ContentBehavior',
            ),
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>array(
                    'enable'=>true,
                ),
            ),
            'workflow' => array(
                'class'=>'common.services.workflow.behaviors.TicketWorkflowBehavior',
            ), 
            'shopparentbehavior' => array(
                'class'=>'ShopParentBehavior',
            ),
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, subject, content, status', 'required'),
            array('account_id, shop_id', 'numerical', 'integerOnly'=>true),
            array('subject', 'length', 'max'=>200),
            array('content', 'length', 'max'=>5000),
            array('content','rulePurify'),
            array('status', 'length', 'max'=>20),
            // The following rule is used by search().
            array('id, account_id, shop_id, subject, content, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * This rule perform purify content
     * This is to prevent malicious code; e.g without this, 
     * content contains script can get executed: <script>alert("test");</script>
     * 
     * @param type $attribute
     * @param type $params
     */
    public function rulePurify($attribute,$params)
    {
        $this->validatePurify($attribute);//method inhertied from ContentBehavior    
    } 
    
    public function insertTicket()
    {
        $this->insertEncodedContent('content');
    }    
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'subject' => Sii::t('sii','Subject'),
            'content' => Sii::t('sii','Content'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    /**
     * A wrapper method to return main thread records of this model
     * @return \SActiveRecord
     */
    public function all() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'subject NOT LIKE \''.self::REPLY_SUBJECT_PREFIX.'%\'',
        ));
        return $this;
    } 
    /**
     * A wrapper method to return repliable records of this model for admin
     * @return \Ticket
     */
    public function admin() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status = \''.Process::TICKET_SUBMITTED.'\'',
        ));
        return $this;
    }    
    /**
     * A wrapper method to return submitted records of this model
     * @return \Ticket
     */
    public function submitted() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status = \''.Process::TICKET_SUBMITTED.'\'',
        ));
        return $this->all();
    }
    /**
     * A wrapper method to return closed records of this model
     * @return \Ticket
     */
    public function closed() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status = \''.Process::TICKET_CLOSED.'\'',
        ));
        return $this->all();
    }

    public function insertReply()
    {
        $this->insertEncodedContent('content');
    }
    
    public function searchReplies()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('subject', self::REPLY_SUBJECT_PREFIX.$this->id);
        logTrace(__METHOD__.' criteria',$criteria);
        return new CActiveDataProvider('Ticket',array('criteria'=>$criteria));
    } 
    /**
     * Provide this method for activity recording use
     * @return type
     */
    public function getNotificationRecipient()
    {
        $ticket = $this->retrieveTicketFromReply();
        $data = array('subject'=>Sii::t('sii','[Ticket ID:{id}] {subject}',array('{id}'=>$ticket->id,'{subject}'=>$ticket->subject)));
        if (AuthAssignment::hasRole($this->account_id, Role::ADMINISTRATOR)){
            //this means administrator is replying ticket, and user to get notified
            return array_merge($data,array('recipient'=>$ticket->account_id,'account'=>$ticket->account));
        }
        else {
            //this means user is replying ticket, and administrator to get notified
            return array_merge($data,array('role'=>Role::ADMINISTRATOR));
        }
    }   
    
    public function retrieveTicketFromReply()
    {
        $ticketId = substr($this->subject,strlen(self::REPLY_SUBJECT_PREFIX));
        logTrace(__METHOD__.' ticket id '.$ticketId);
        $ticket = Ticket::model()->findByPk($ticketId);
        return $ticket;
    }
    /**
     * Return account profile
     * @return type
     */
    public function getAccountProfile()
    {
        return $this->account->profile;
    }
    /**
     * Check if ticket can be closed
     */
    public function closable()
    {
        return $this->status==Process::TICKET_SUBMITTED;
    }
    /**
     * Check if ticket is closed
     */
    public function getIsClosed()
    {
        return $this->status==Process::TICKET_CLOSED;
    }    
    /**
     * Check if ticket is a replied message
     */
    public function getIsReplied()
    {
        return $this->status==Process::TICKET_REPLIED;
    }    
    
    public function updatable()
    {
        return $this->account_id==user()->getId() && $this->closable();
    }    
    public function deletable()
    {
        return $this->account_id==user()->getId() && $this->closable();
    }   
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);
        //$criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('subject',$this->subject,true);
        $criteria->compare('content',$this->content,true);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider(
                                        'Ticket',
                                        array(
                                            'criteria'=>$criteria,
                                            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                        ));

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Provide this method for activity recording use
     * @return type
     */
    public function getName()
    {
        return $this->subject;
    }      
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('ticket/view/'.$this->id);
    } 
    /**
     * This is required for making a comment and also activity
     * @see CommentManager $target
     * @see ActivityBehavior
     * @return type
     */
    public function getImageUrl()
    {
        return '<i class="fa fa-ticket fa-fw"></i>';
    }
    /**
     * This is required for making a comment
     * @see module Comments for comment list view
     * @return type
     */
    public function getImageThumbnail($version=null)
    {
        return '<i class="fa fa-ticket"></i>';
    }      

}