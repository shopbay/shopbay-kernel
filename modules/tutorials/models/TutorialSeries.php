<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.services.workflow.models.Administerable");
Yii::import("common.modules.tutorials.models.TutorialTrait");
/**
 * This is the model class for table "s_tutorial_series".
 *
 * The followings are the available columns in table 's_tutorial_series':
 * @property integer $id
 * @property integer $account_id
 * @property string $name 
 * @property string $desc 
 * @property string $tutorials
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
class TutorialSeries extends Administerable
{
    use TutorialTrait;
    const DEMO_TUTORIAL_SERIES = -1;
    protected $draftedStatus = Process::TUTORIAL_SERIES_DRAFT;
    protected $submittedStatus = Process::TUTORIAL_SERIES_SUBMITTED;
    protected $publishedStatus = Process::TUTORIAL_SERIES_PUBLISHED;  
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
        return Sii::t('sii','Tutorial Series');//no singular
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_tutorial_series';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge($this->getCommonBehaviors(),[
            'likablebehavior' => [
                'class'=>'common.modules.likes.behaviors.LikableBehavior',
                'modelFilter'=>'tutorialSeries',
            ],        
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.TutorialSeriesWorkflowBehavior',
            ], 
            'searchable' => [
                'class'=>'common.modules.search.behaviors.SearchableBehavior',
                'searchModel'=>'SearchTutorialSeries',
            ],                    
        ]);
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, tutorials, status', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 250 chars.
            ['name', 'length', 'max'=>5000],
            ['tutorials', 'length', 'max'=>500],
            ['slug', 'length', 'max'=>256],
            ['tags', 'length', 'max'=>500],
            ['status', 'length', 'max'=>20],
            ['slug', 'unique'],
            ['desc', 'safe'],
            ['params', 'length', 'max'=>5000],
            // The following rule is used by search().
            ['id, account_id, name, desc, tutorials, tags, slug, params, status, create_time, update_time', 'safe', 'on'=>'search'],
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
            'desc' => Sii::t('sii','Description'),
            'tutorials' => Sii::t('sii','Tutorials'),
            'tags' => Sii::t('sii','Tags'),
            'slug' => Sii::t('sii','SEO URL'),
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
        $criteria->compare('desc',$this->desc,true);
        $criteria->compare('tutorials',$this->tutorials,true);
        $criteria->compare('tags',$this->tags,true);
        //$criteria->compare('slug',$this->slug,true);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('TutorialSeries',[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('tutorials/series/view/'.$this->id);
    } 
    /**
     * Public access url
     * @return type
     */
    public function getUrl()
    {
        return $this->getAccessUrl('tutorials/series/'.$this->slug);
    }     
    /**
     * Public access url
     * @return type
     */
    public static function getPublicUrl($slug)
    {
        return TutorialSeries::model()->getAccessUrl('tutorials/series/'.$slug);
    }        
    /**
     * This is required for making a comment and also activity
     * @see CommentManager $target
     * @see ActivityBehavior
     * @return type
     */
    public function getImageUrl()
    {
        return '<i class="fa fa-files-o small-font"></i>';
    }
    /**
     * This is required for making a comment
     * @see module Comments for comment list view, and Like list view
     * @return type
     */
    public function getImageThumbnail($version=null)
    {
        return '<i class="fa fa-files-o big-font"></i>';
    }      
    
    public function searchTutorials($locale=null)
    {
        $tutorials = json_decode($this->tutorials,true);
        $models = [];
        if (is_array($tutorials)){
            foreach ($tutorials as $tutorial) {
                $tutorialModel = Tutorial::model()->findByPk($tutorial);
                if ($tutorialModel!=null)
                   $models[] = [
                       'id'=>$tutorialModel->id,
                       'name'=>$tutorialModel->localeName($locale),
                       'statusText'=>$tutorialModel->getStatusText(),
                       'viewUrl'=>$tutorialModel->viewUrl,
                       'url'=>$tutorialModel->url,
                    ];
            }
        }
        return new CArrayDataProvider($models);
    }
    /**
     * Take care of description when empty display null
     * @param type $locale
     * @return type
     */
    public function displayDescripion($locale)
    {
        $desc =  $this->displayLanguageValue('desc',$locale,['Attr.EnableID'=>true,]);//allow attribute "id" for purified content
        return $desc==Sii::tl('sii','unset',$locale)?'':$desc;
    }
}