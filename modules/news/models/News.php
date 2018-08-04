<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * This is the model class for table "s_news".
 *
 * The followings are the available columns in table 's_news':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $headline
 * @property string $content
 * @property string $image
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class News extends Transitionable
{
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
        return Sii::t('sii','News|News',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_news';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],              
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::NEWS_ONLINE,
                'inactiveStatus'=>Process::NEWS_OFFLINE,
            ],                
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],  
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'headline',
                'buttonIcon'=>true,
                //'iconUrlSource'=>'shop',
            ],
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],
            'contentbehavior' => [
                'class'=>'common.components.behaviors.ContentBehavior',
            ],
            'newsbehavior' => [
                'class'=>'common.modules.news.behaviors.NewsBehavior',
            ],
            'sitemap' => [
                'class'=>'common.components.behaviors.SitemapBehavior',
                'scopes'=>['active'],
                'sort'=>'update_time DESC',
            ],
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'label'=>Sii::t('sii','Image'),
                'stateVariable'=>SActiveSession::NEWS_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_NEWS,
            ],
        ];
    }
    /**
     * Validation rules for model attributes
     * 
     * Note: model attribute (table column) wil have own validation rules following underlying table definition
     * Some attribute that are to have support of multiple locales have to actual attribute level rules specified at LanguageForm level
     * 
     * @see NewsForm
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, headline, content, status', 'required'],
            ['account_id, shop_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded headline in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            ['headline', 'length', 'max'=>2000],
            //This column stored json encoded content in different languages, 
            //It buffers about 20 languages, assuming each take 1000 chars.
            ['content', 'length', 'max'=>20000],
            ['status', 'length', 'max'=>20],
            ['headline','rulePurify'],
            ['content','rulePurify'],
            ['image', 'safe'],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],                    

            ['id, account_id, shop_id, headline, content, image, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if news has any associations
     * (2) News is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',['{object}'=>$this->displayLanguageValue('name',user()->getLocale())]));
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
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'headline' => Sii::t('sii','Headline'),
            'content' => Sii::t('sii','Content'),
            'image' => Sii::t('sii','Image'),
            'status' => Sii::t('sii','Status'),
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
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('headline',$this->headline,true);
        $criteria->compare('content',$this->content,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this->mine(), [
            'criteria'=>$criteria,
        ]);
    }

    public function getName()
    {
        return $this->displayLanguageValue('headline');
    }
    
    public function like() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->select = 'obj_id';
        $like = Like::model()->mine()->shop()->findAll($criteria);
        //logTrace('Like::model()',$criteria);
        $shops = new CList();
        foreach ($like as $item)
            $shops->add($item->obj_id); 
        $this->active()->getDbCriteria()->mergeWith([
            'condition'=>QueryHelper::constructInCondition('shop_id',$shops),
        ]);
        //logTrace('News->like()',$this->getDbCriteria());
        return $this;
    }    
    
    public function getSubscriber() 
    {
        $subscriber = null;
        $like = Like::model()->mine()->shop($this->shop_id)->find();
        if ($like!=null)
            $subscriber = $like->account_id;
        return $subscriber;
    }           
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('news/view/'.$this->id);
    }
    /**
     * This is public accessible url
     * @return type
     */
    public function getUrl($secure=false)
    {
        return $this->getBaseUrl($secure).'/article/'.$this->id;
    }
    /**
     * Return markdown content
     * @param $locale
     * @return string
     */
    public function getMarkdownContent($locale,$length=null)
    {
        $md = new CMarkdown();
        $content = $md->transform(Helper::purify($this->displayLanguageValue('content',$locale)));
        if (isset($length))
            $content = Helper::rightTrim($content, $length);
        return $content;
    }
    
    public function getItemColumnData($locale=null) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/images/',Image::DEFAULT_IMAGE_NEWS);
        $list->add($this->displayLanguageValue('headline',$locale),[
            'image'=>$imageData,
        ]);
        return $list;
    }   
}