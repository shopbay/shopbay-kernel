<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.services.workflow.models.Administerable");
Yii::import("common.modules.tutorials.models.TutorialTrait");
Yii::import("common.modules.tutorials.models.TutorialSeries");
/**
 * This is the model class for table "s_tutorial".
 *
 * The followings are the available columns in table 's_tutorial':
 * @property integer $id
 * @property integer $account_id
 * @property string $name 
 * @property string $content
 * @property string $difficulty
 * @property string $tags
 * @property string $slug
 * @property string $params
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Tutorial extends Administerable
{
    use TutorialTrait;
    const DEMO_TUTORIAL = -1;
    const BEGINNER     = 'B';
    const INTERMEDIATE = 'I';
    const ADVANCED     = 'A';
    protected $draftedStatus = Process::TUTORIAL_DRAFT;
    protected $submittedStatus = Process::TUTORIAL_SUBMITTED;
    protected $publishedStatus = Process::TUTORIAL_PUBLISHED;  
    private $_series;
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
        return Sii::t('sii','Tutorial|Tutorials',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_tutorial';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge($this->getCommonBehaviors(),[
            'likablebehavior' => [
                'class'=>'common.modules.likes.behaviors.LikableBehavior',
                'modelFilter'=>'tutorial',
            ],        
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.TutorialWorkflowBehavior',
            ], 
            'searchable' => [
                'class'=>'common.modules.search.behaviors.SearchableBehavior',
                'searchModel'=>'SearchTutorial',
            ],                    
        ]);
    } 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, content, difficulty, status', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 250 chars.
            ['name', 'length', 'max'=>5000],
            ['slug', 'length', 'max'=>256],
            ['difficulty', 'length', 'max'=>1],
            ['tags', 'length', 'max'=>500],
            ['status', 'length', 'max'=>20],
            ['slug', 'unique'],
            ['params', 'length', 'max'=>5000],

            // The following rule is used by search().
            ['id, account_id, name, content, difficulty, tags, slug, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge($this->pageSeoAttributeLabels(),[
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'name' => Sii::t('sii','Title'),
            'content' => Sii::t('sii','Content'),
            'difficulty' => Sii::t('sii','Difficulty'),
            'tags' => Sii::t('sii','Tags'),
            'slug' => Sii::t('sii','Url'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ]);
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('content',$this->content,true);
        $criteria->compare('difficulty',$this->difficulty,true);
        $criteria->compare('tags',$this->tags,true);
        //$criteria->compare('slug',$this->slug,true);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('Tutorial',[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Find tutorial series
     * @return array of TutorialSeries
     */
    public function searchSeries() 
    {
        if (!isset($this->_series)){
            $criteria=new CDbCriteria;
            //enquote with "<id>" to separate each tutorial id
            $criteria->condition = 'tutorials LIKE \'%"'.$this->id.'"%\'';
            $this->_series = new CActiveDataProvider(TutorialSeries::model()->published(),[
                'criteria'=>$criteria,
                'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            ]);
        }
        return $this->_series;
    }    
    /**
     * @return boolean if has series
     */
    public function hasSeries()
    {
        return $this->searchSeries()->getItemCount() > 0;
    }  
    /**
     * Return series in array
     */
    public function parseSeries($locale)
    {
        $series = [];
        foreach ($this->searchSeries()->data as $tutorial) {
            $series[] = CHtml::link($tutorial->localeName($locale),  TutorialSeries::getPublicUrl($tutorial->slug));
        }
        return $series;
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('tutorial/view/'.$this->id);
    } 
    /**
     * Public access url
     * @return type
     */
    public function getUrl()
    {
        return $this->getAccessUrl('tutorials/'.$this->slug);
    }     
    /**
     * This is required for making a comment and also activity
     * @see CommentManager $target
     * @see ActivityBehavior
     * @return type
     */
    public function getImageUrl()
    {
        return '<i class="fa fa-file-text-o small-font"></i>';
    }
    /**
     * This is required for making a comment
     * @see module Comments for comment list view, and Like list view
     * @return type
     */
    public function getImageThumbnail($version=null)
    {
        return '<i class="fa fa-file-text-o big-font"></i>';
    }          
    /**
     * Take care of tutorial content when empty display null
     * @param type $locale
     * @return type
     */
    public function displayContent($locale)
    {
        return $this->displayLanguageValue('content',$locale,['Attr.EnableID'=>true,]);//allow attribute "id" for purified content
    }
    
    public function getDifficultyText()
    {
        return self::getDifficultyLevels($this->difficulty);
    } 
    
    public function getDifficultyTag()
    {
        $tag = ['text'=>$this->getDifficultyText()];
        switch ($this->difficulty) {
            case self::BEGINNER:
                $tag = array_merge($tag, ['color'=>'lightgreen']);
                break;
            case self::INTERMEDIATE:
                $tag = array_merge($tag, ['color'=>'lightblue']);
                break;
            case self::ADVANCED:
                $tag = array_merge($tag, ['color'=>'orange']);
                break;
            default:
                break;
        }
        return $tag;
    } 
    
    public static function getDifficultyLevels($level=null)
    {
        if ($level!=null){
            $difficulties = self::getDifficultyLevels();
            if (isset($difficulties[$level]))
                return $difficulties[$level];
            else
                return Sii::t('sii','not set');
        }
        else {
            return [
                self::BEGINNER =>  Sii::t('sii','Beginner'),
                self::INTERMEDIATE => Sii::t('sii','Intermediate'),
                self::ADVANCED => Sii::t('sii','Advanced'),
            ];
        }
    }

}