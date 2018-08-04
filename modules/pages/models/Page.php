<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.workflow.models.Transitionable');
Yii::import('common.modules.pages.models.PageTrait');
Yii::import('common.modules.pages.models.PageOwnerTrait');
Yii::import('common.modules.themes.models.ThemeParams');
Yii::import('common.modules.shops.components.BasePage');
/**
 * This is the model class for table "s_page".
 *
 * The followings are the available columns in table 's_page':
 * @property integer $id
 * @property integer $account_id
 * @property integer $owner_id 
 * @property string $owner_type  
 * @property string $title
 * @property string $desc
 * @property string $content 
 * ----
 * Note:
 * ----- 
 *  + Page content stores the last saved "active" theme page content as a backup copy and also set as the default content for new theme page to use
 *    Each theme is expected to have same page content but content can be different technically since content and design might 
 *    need to adjust according to theme styles and design
 *  + Non-active theme page saving has no effect / change on this field
 * 
 * @property string $params 
 * @property string $status 
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Page extends Transitionable
{
    use PageTrait, PageOwnerTrait, ThemeParams;
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Page the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_page';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Page|Pages',[$mode]);
    }         
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge($this->ownerBehaviors(),[
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],              
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
            ],
            'transition' => [
              'class'=>'common.components.behaviors.TransitionBehavior',
              'activeStatus'=>Process::PAGE_ONLINE,
              'inactiveStatus'=>Process::PAGE_OFFLINE,
            ],
            'workflow' => [
              'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],              
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],                      
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],
            'pagebehavior' => [
                'class'=>'common.modules.pages.behaviors.PageBehavior',
            ],
            'content' => [
                'class'=>'common.components.behaviors.ContentBehavior',
            ],
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=>[
                    'method'=>'getSlugValue',
                ],
                //enable SkipScenario to bypass auto sluggable behavior when slug value is presented
                'skipScenario'=>'create',//since the implementation only allow create to define slug
            ],
            'sitemap' => [
                'class'=>'common.components.behaviors.SitemapBehavior',
                'pageOwnerAttribute'=>'owner_id',
                'scopes'=>['active'],
                'sort'=>'update_time DESC',
            ],            
       ]);
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge($this->ownerRules(),[
            ['account_id, title', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 65 chars.
            ['title', 'length', 'max'=>1500],
            //It buffers about 20 languages, assuming each take 100 chars.
            ['desc', 'length', 'max'=>2500],
            ['status', 'length', 'max'=>10],            
            ['params', 'length', 'max'=>5000],            
            ['content','rulePurify'],//TODO enable this cannot have html tags inside content?
            
            //slug validation
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
            ['slug', 'ruleSlugWhitelist','on'=>$this->getCreateScenario()],
            
            //on deactivate scenario, id field here as dummy
            ['status', 'ruleDeactivation','params'=>[],'on'=>'deactivate'],
            
            ['id, account_id, title, content, slug, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ]);
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
        if (is_array($this->$attribute)){
            foreach ($this->$attribute as $locale => $localeContent) {
                logTrace(__METHOD__.' validate locale '.$locale,$localeContent);
                $this->validatePurifyContent($attribute, $localeContent);//method inhertied from ContentBehavior    
            }
        }
    }     
    /**
     * Verify page url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        if (!empty($this->slug)){
            logTrace(__METHOD__,$this->slug);
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(['owner_id'=>$this->owner_id,'owner_type'=>$this->owner_type,'slug'=>$this->slug]);
            if (Page::model()->exists($criteria))
                $this->addError('slug',Sii::t('sii','Page URL "{slug}" is already taken.',['{slug}'=>$this->slug]));
        }
    }    
    /**
     * Deactivation Check
     * (1) Verify that page is not in used in any categories
     */
    public function ruleDeactivation($attribute,$params)
    {
        $layout = $this->getParam('layout');
        if ($layout!=null && in_array($layout,[BasePage::HOME,'footer','header'])){
            $this->addError('status',Sii::t('sii','Page "{page}" is mandatory and must always stay online.',['{page}'=>$this->displayLanguageValue('title',user()->getLocale())]));
        }
    }     
    /**
     * Finder method to count page limit (excluding in-built pages)
     * @return type
     */
    public function countPage() 
    {
        $criteria = new CDbCriteria();
        $criteria->compare('params', '"custom":true', true);
        logTrace(__METHOD__.' criteria',$criteria);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }     
    /**
     * A finder method by page id
     * @param string $pageId
     */
    public function locatePage($pageId) 
    {
        return $this->withParam('layout',$pageId);
    }        
    /**
     * @inheritdoc
     * It seems model->owner_type is "auto" set to Owner object (still not know why)
     * example error: "Object of class Shop could not be converted to string..."
     * Here is to set its owner_type back to class name of Owner 
     * @todo Yii BUG??
     */
    protected function beforeSave()
    {
       if (parent::beforeSave()){
            if ($this->isNewRecord){
                $this->refreshOwnerType();
            }
            return true;
       }
       return false;
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
        return array_merge($this->pageSeoAttributeLabels(),$this->ownerAttributeLabels(),[
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'title' => Sii::t('sii','Page Title'),
            'desc' => Sii::t('sii','Page Description'),
            'content' => Sii::t('sii','Page Content'),
            'slug' => Sii::t('sii','Page URL'),
            'params' => Sii::t('sii','Page Params'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ]);
    }   
    /**
     * Add name attribute (for getter method) to support compatiblity with SlugBehavior/etc
     * @return type
     */
    public function getName()
    {
        return $this->title;
    }

    public function deletable()
    {
        $locked = $this->getParam('locked');
        if ($locked!=null && $locked)
            return false;
        else
            return $this->account_id==user()->getId();
    }        
    
    public function updatable()
    {
        $embed = $this->getParam('embed');//pointing to header and footer
        if ($embed!=null && $embed)
            return false;
        else
            return $this->account_id==user()->getId();
    }        
    /**
     * Header and footer is not a full page
     * @return boolean
     */
    public function getIsFullPage()
    {
        $embed = $this->getParam('embed');
        if ($embed!=null && $embed)
            return false;
        else
            return true;
    }        
    
    public function getViewUrl() 
    {
        return url('page/view/'.$this->id);
    }
    
    public function getUpdateUrl() 
    {
        return url('pages/management/update/id/'.$this->id);
    }
    
    public function getDeleteUrl() 
    {
        return url('pages/management/delete/id/'.$this->id);
    }
    /** 
     * The layout mapping id to tabel s_page_layout
     * To retrieve the layout config of this page
     * @see PageLayout::$page
     * @return type
     */
    public function getLayoutMapId()
    {
        if ($this->getParam('layout')!=null)
            return $this->getParam('layout');
        else
            return 'custom_page_'.$this->id;
    }
    /**
     * @see merchant/config/main.php for url mapping: pages/layout/edit/<page_name>
     * @return type
     */
    public function getLayoutUrl()
    {
        return url('pages/layout/edit/'.$this->layoutMapId);
    }      
    /**
     * @see merchant/config/main.php for url mapping: pages/layout/reset/<page_name>
     * @return type
     */
    public function getLayoutResetUrl($theme)
    {
        return url('pages/layout/reset/page/'.$this->layoutMapId.'/theme/'.$theme);
    }      
    /**
     * Page preview url can be directly accessed on brower address bar (no theme preview panel)
     * @return string
     */
    public function getPreviewUrl($theme,$style)
    {
        $baseUrl = $this->getUrl(app()->urlManager->forceSecure||request()->isSecureConnection);//use secure connection if set
        return $baseUrl.'?'.Page::previewUriParams($this->owner, $theme, $style);
    }    
    /**
     * A preview token that last for whithin the day it gets generated
     * @param CModel $owner
     * @return string
     */
    public static function previewToken($owner)
    {
        return sha1($owner->id.'-'.date('Ymd'));
    }    
    /**
     * Prepare preview uri params
     * @return string
     */
    public static function previewUriParams($owner,$theme,$style)
    {
        return http_build_query([
            'theme'=>$theme,
            'style'=>$style,
            'preview'=>Page::previewToken($owner),
        ]);
    }       
}
