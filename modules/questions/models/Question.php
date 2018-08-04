<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * This is the model class for table "s_question".
 *
 * The followings are the available columns in table 's_question':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property integer $type
 * @property string $title 
 * @property string $question
 * @property string $question_by
 * @property integer $question_time
 * @property string $answer
 * @property integer $answer_by
 * @property integer $answer_time
 * @property string $tags
 * @property string $slug
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Account $questionBy
 *
 * @author kwlok
 */
class Question extends Transitionable
{
    const DEMO_QUESTION = -1;
    const TYPE_PRIVATE = 0;
    const TYPE_PUBLIC  = 1;
    /**
     * Returns the static model of the specified AR class.
     * @return Question the static model class
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
        return Sii::t('sii','Question|Questions',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_question';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
                'createAttribute'=>'question_time',
                'updateEnable'=>false,
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'question_by',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'question_by',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
                'merchantAttribute'=>'obj_id',                
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'account',
                'localeAttribute'=>'profileLocale',
            ],  
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::QUESTION_ONLINE,
                'inactiveStatus'=>Process::QUESTION_OFFLINE,
            ],  
            'transitionWorkflow' => [
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],              
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.QuestionWorkflowBehavior',
            ],              
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'question',
            ],
            'content' => [
                'class'=>'common.components.behaviors.ContentBehavior',
            ],
            'commentable' => [
                'class'=>'common.modules.comments.behaviors.CommentableBehavior',
            ],      
            'likablebehavior' => [
                'class'=>'common.modules.likes.behaviors.LikableBehavior',
                'modelFilter'=>'question',
            ],    
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'columns' => ['title'],
            ],
            'taggable' => [
                'class'=>'common.modules.tags.behaviors.TaggableBehavior',
                'tagUrlMethod'=>'getUrl',
            ],
            'searchable' => [
                'class'=>'common.modules.search.behaviors.SearchableBehavior',
                'searchModel'=>'SearchQuestion',
            ],                    
        ];
    }   
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['obj_type, question', 'required'],
            ['obj_id, type, answer_by, answer_time', 'numerical', 'integerOnly'=>true],
            ['question_by', 'length', 'max'=>12],
            ['question','rulePurify','on'=>'ask'],
            ['answer','rulePurify','on'=>'answer'],
            ['obj_type', 'length', 'max'=>20],
            ['id, obj_id, status, answer', 'safe'],
            ['title, slug', 'length', 'max'=>256],
            ['tags', 'length', 'max'=>500],
            ['slug', 'unique'],
            
            ['obj_type, obj_id, question, answer', 'required','on'=>'updateAnswer'],
            ['answer','rulePurify','on'=>'updateAnswer'],
            
            //activate scenario
            ['type', 'ruleActivation','on'=>'activate'],
            
            ['id, obj_type, obj_id, type, title, question, question_by, question_time, answer, answer_by, answer_time, tags, slug, status', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Activation Check
     * (1) Verify that only public type question can be activated
     */
    public function ruleActivation($attribute,$params)
    {
        if (!$this->isPublic()) {
            $this->addError('type',Sii::t('sii','Question is not declared as public type'));
        }
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
    public function insertQuestion()
    {
        $this->insertEncodedContent('question');
    }
    public function updateQuestion()
    {
        $this->updateEncodedContent('question');
    }
    public function updateAnswer()
    {
        $this->updateEncodedContent('answer');
    }    
    /**
     * A wrapper method to return pending answered records of this model
     * @return \Question
     */
    public function publicQuestion() 
    {
        $this->merchant()->getDbCriteria()->mergeWith([
            'condition'=>'type = '.self::TYPE_PUBLIC,
        ]);
        return $this;
    }    
    /**
     * A wrapper method to return published records of this model
     * @return \Question
     */
    public function published() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.Process::QUESTION_ONLINE.'\' AND type = '.self::TYPE_PUBLIC.' AND obj_type=\''.$this->tableName().'\'',
        ]);
        
        return $this;
    }   
    /**
     * A wrapper method to return pending answered records of this model
     * @return \Question
     */
    public function pendingAnswered() 
    {
        $this->merchant()->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.Process::ASKED.'\'',
        ]);
        return $this;
    }    
    /**
     * A wrapper method to return asked records of this model
     * @return \Question
     */
    public function asked() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.Process::ASKED.'\' OR obj_type=\''.$this->tableName().'\'',
        ]);
        return $this;
    }
    /**
     * A wrapper method to return answered records of this model
     * @return \Question
     */
    public function answered() {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status IN (\''.Process::QUESTION_ONLINE.'\',\''.Process::QUESTION_OFFLINE.'\') AND obj_type!=\''.$this->tableName().'\'',
        ]);
        return $this;
    }
    public function retrieve($id) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'id = '.$id,
        ]);
        return $this;
    }        
    public function answerUpdatable()
    {
        return $this->answer_by==user()->getId();
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'obj_type' => Sii::t('sii','Obj Type'),
            'obj_id' => Sii::t('sii','Obj'),
            'type' => Sii::t('sii','Type'),
            'title' => Sii::t('sii','Title'),
            'question' => Sii::t('sii','Question'),
            'question_by' => Sii::t('sii','Question By'),
            'question_time' => Sii::t('sii','Question Time'),
            'answer' => Sii::t('sii','Answer'),
            'answer_by' => Sii::t('sii','Answer By'),
            'answer_time' => Sii::t('sii','Answer Time'),
            'tags' => Sii::t('sii','Tags'),
            'slug' => Sii::t('sii','SEO URL'),
            'status' => Sii::t('sii','Status'),
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
        $criteria->compare('obj_type', $this->obj_type, true);
        $criteria->compare('obj_id', $this->obj_id);
        $criteria->compare('type', $this->type, true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('question',$this->question,true);
        $criteria->compare('question_by',$this->question_by);
        $criteria->compare('question_time',$this->question_time);
        $criteria->compare('answer',$this->answer,true);
        $criteria->compare('answer_by',$this->answer_by);
        $criteria->compare('answer_time',$this->answer_time);
        $criteria->compare('tags',$this->tags,true);
        //$criteria->compare('slug',$this->slug,true);
        $criteria->compare('status',$this->status);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function isPublic()
    {
        return $this->type==self::TYPE_PUBLIC;
    }    
    
    public function isPrivate()
    {
        return $this->type==self::TYPE_PRIVATE;
    }       
    
    public function hasAnswer()
    {
        return $this->answer!=null;
    }       
    
    public function getQuestioner()
    {
        return AccountProfile::model()->findByAttributes(['account_id'=>$this->question_by]);
    }       

    public function getAnswerer()
    {
        return AccountProfile::model()->findByAttributes(['account_id'=>$this->answer_by]);
    }       

    public function answerable()
    {
        return $this->status==Process::ASKED && $this->shop->account_id==user()->getId();
    }   
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        $route = 'question/view/'.$this->id;
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,true);
        else {
            if (Account::isSubType($this->question_by) && $this->shop->domain!=null){
                //show shop sub domain url
                return $this->shop->url.'/'.$route;
            }
            else
                return url($route);//$route cannot start with "/" else host info not following current scheme
        }
    }
    
    public function getAskUrl()
    {
        return url('question/ask');
    }

    public function getAnswerUrl($domain=null)
    {
        $route = 'questions/management/answer/id/'.$this->id;
        return isset($domain) ? app()->urlManager->createDomainUrl($domain,$route) : url($route);
    }
    
    public function getMerchantViewUrl()
    {
        return url('questions/management/view/'.$this->id);        
    }    
    public function getUrl()
    {
        return url('community/questions/'.$this->slug);
    }       
    public function getReturnUrl()
    {
        return $this->url;
    } 
    /**
     * This is required for making a comment and also activity
     * @see CommentManager $target
     * @see ActivityBehavior
     * @return type
     */
    public function getImageUrl()
    {
        return '<i class="fa fa-question small-font"></i>';
    }
    /**
     * This is required for making a comment
     * @see module Comments for comment list view, and likes list view
     * @return type
     */
    public function getImageThumbnail($version=null)
    {
        return '<i class="fa fa-question"></i>';
    }      
    /**
     * Provide this method for LikeManager use
     * @return type
     */
    public function getName()
    {
        return $this->title;
    }       
    
    public function isCommunityQuestion()
    {        
        return $this->obj_type==self::model()->tableName();
    }    
    
    public function hasShop()
    {        
        return $this->obj_type==Shop::model()->tableName();
    }    
    
    public function getShop()
    {        
        return $this->hasShop()?$this->getReference():$this->getReference()->shop;
    }    
    /**
     * This is used by notification schema.sql
     * @return type
     */
    public function getShopId()
    {        
        if ($this->hasShop())
            return $this->getShop()->id;
        if ($this->hasProduct()||$this->hasCampaignBga())
            return $this->getReference()->shop->id;
    }    
    
    public function hasProduct()
    {        
        return $this->obj_type==Product::model()->tableName();
    }    
    
    public function hasCampaignBga()
    {        
        return $this->obj_type==CampaignBga::model()->tableName();
    }    
    
    private $_r;//store question target object 
    public function getReference()
    {
        if (!isset($this->_r[$this->obj_type])){
            if ($this->isCommunityQuestion()){
                $this->_r[$this->obj_type] = $this;            
            }
            else {
                $type = SActiveRecord::resolveTablename($this->obj_type);
                $this->_r[$this->obj_type] = $type::model()->findByPk($this->obj_id);            
            }
        }
        return $this->_r[$this->obj_type];
    }
    
    public function getReferenceName($locale=null)
    {
        $reference = $this->getReference();
        if ($reference instanceof Question)
            return $reference->name;//refer to method self:::getName()
        else
            return $reference->displayLanguageValue('name',$locale);
    }
    
    public function getReferenceImage($version=Image::VERSION_SMEDIUM)
    {        
        if ($this->hasProduct())
            return $this->reference->getImageThumbnail($version);
        else if ($this->hasShop())
            return $this->reference->getImageThumbnail(Image::VERSION_ORIGINAL,['style'=>'width:'.$version.'px;']);
        else
            return $this->getImageThumbnail();
    }    
    /**
     * Only public type can be activated
     * @return type
     */
    public function activable() 
    {
        return $this->getOwner()->status==$this->inactiveStatus && $this->isPublic();
    }
    public function deletable()
    {
        return $this->question_by==user()->getId() && $this->activable();
    }   
    
    public function getTypeLabel($type=null)
    {
        if (!isset($type))
            $type = $this->type;
        
        if ($type == self::TYPE_PRIVATE)
            return ['text'=>Sii::t('sii','Private'),'color'=>'lightblue'];
        else if ($type == self::TYPE_PUBLIC)
            return ['text'=>Sii::t('sii','Public'),'color'=>'lightcoral'];
        else
            return ['text'=>Sii::t('sii','undefined'),'color'=>'black'];
    }
}