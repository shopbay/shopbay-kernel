<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_comment".
 *
 * The followings are the available columns in table 's_comment':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $content
 * @property integer $rating
 * @property string $comment_by
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Comment extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Comment the static model class
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
        return Sii::t('sii','Comment|Comments',[$mode]);
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_comment';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'comment_by',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'comment_by',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'account',
                'localeAttribute'=>'profileLocale',
            ],          
            'content' => [
                'class'=>'common.components.behaviors.ContentBehavior',
            ],
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['content', 'required'],
            ['rating', 'numerical', 'integerOnly'=>true],
            ['comment_by', 'length', 'max'=>12],
            ['obj_type', 'length', 'max'=>20],
            ['obj_id', 'safe'],
            ['content','rulePurify'],
            
            ['id, obj_type, obj_id, content, rating, comment_by, create_time, update_time', 'safe', 'on'=>'search'],
        ];
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
    public function insertComment()
    {
        $this->insertEncodedContent('content');
    }
    public function updateComment()
    {
        $this->updateEncodedContent('content');
    }
    
    public function scopes() 
    {
        return [];
    }

    public function getAuthor() 
    {
        $author = new stdClass();
        $author->name = $this->account->nickname;
        $author->avatar = $this->account->getAvatar(Image::VERSION_XSMALL);
        return $author;
    }        

    public function byType($type, $id=null) 
    {
        $condition = 'obj_type=\''.$type.'\'';
        if (isset($id))
            $condition .= ' and obj_id='.$id;
        $this->getDbCriteria()->mergeWith([
            'condition'=>$condition,
        ]);
        return $this;
    }
    
    private $_t;//store comment target object 
    public function getTarget()
    {
        if (!isset($this->_t)){
            $type = SActiveRecord::resolveTablename($this->obj_type);
            return $type::model()->findByPk($this->obj_id);            
        }
        return $this->_t;
    }
    
    public function hasTarget()
    {       
        return $this->getTarget()!=null;
    }
            
    public  function getTargetColumnData($locale) 
    {
        $list = new CMap();
        if ($this->hasTarget()){
            $image = $this->getTarget()->image;
            $list->add($this->getTarget()->displayLanguageValue('name',$locale),[
                'image'=>[
                    'type'=>'Image',
                    'default'=>Image::DEFAULT_IMAGE_PRODUCT,
                    'imagePath'=>'/files/products/',
                    'id'=>$image==null?Image::DEFAULT_IMAGE_PRODUCT:$image,
                    'version'=>Image::VERSION_XSMALL,
                ],
            ]);
        }
        else {
            $list->add(Sii::t('sii','No results found.'),[]);
        }
        return $list;
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
            'content' => Sii::t('sii','Comment'),
            'rating' => Sii::t('sii','Rating'),
            'comment_by' => Sii::t('sii','Comment By'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
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
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('content',$this->content,true);
        $criteria->compare('rating',$this->rating);
        $criteria->compare('comment_by',$this->comment_by);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this->mine(), [
            'criteria'=>$criteria,
        ]);
    }     
    
    public function getCounter()
    {
        return Yii::app()->serviceManager->getAnalyticManager()->getMetricValue($this->obj_type, $this->obj_id, Metric::COUNT_COMMENT);
    }      

    public function updateCounter($value)
    {
        Yii::app()->serviceManager->getAnalyticManager()->setCounterMetric($this->obj_type, $this->obj_id, Metric::COUNT_COMMENT, $value);
    }             
        
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('comment/view/'.$this->id);
    }
   
}