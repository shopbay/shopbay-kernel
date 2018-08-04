<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("pages.behaviors.PageBehavior");
Yii::import("pages.models.PageSEOTrait");
Yii::import('common.modules.pages.models.PageOwnerTrait');
/**
 * Description of PageForm
 *
 * @author kwlok
 */
class PageForm extends LanguageForm 
{
    use PageSEOTrait, PageOwnerTrait;
    
    public static $titleLength = 20;
    public static $descLength = 100;
    protected $persistentAttributes = ['owner_id','owner_type'];    
    /*
     * Inherited attributes
     */
    public $model = 'Page';
    /*
     * Local attributes
     */
    public $owner_id;
    public $owner_type;
    public $title;
    public $desc;
    public $content;
    public $slug;
    public $seoTitle;
    public $seoKeywords;
    public $seoDesc;
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),$this->ownerBehaviors(),[
            'pagebehavior' => [
                'class'=>'PageBehavior',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
                'localeAttribute'=>null,
            ],                      
        ]);
    }      
    /**
     * @return array Definitions of form attributes that requires multi languages
     */       
    public function localeAttributes()
    {
        return [
            'title'=>[
                'required'=>true,
                'inputType'=>'textField',
                'inputHtmlOptions'=>['size'=>80,'maxlength'=>static::$titleLength],//for seo purpose, this can set up to 65 chars.
            ],
            'desc'=>[
                'required'=>false,
                'purify'=>true,
                'inputType'=>'textArea',
                'inputHtmlOptions'=>['cols'=>100,'rows'=>3,'maxlength'=>static::$descLength],
            ],
            //not required; page content is auto sync between PageLayout and here.
//            'content'=>[
//                'required'=>true,
//                'purify'=>true,
//                'inputType'=>'textArea',
//                'inputHtmlOptions'=>['size'=>60,'rows'=>5],
//                'ckeditor'=>[
//                    'imageupload'=>true,
//                    'csrfTokenSelector'=>'.form',
//                    'js'=>'pageckeditor.js',
//                ],
//            ],
        ];
    }    
    /**
     * Supported locales
     * If not specified, it will be auto loaded from OwnergetLanguages()
     * And, resort locales to have shop locale always be the first one
     */
    public function locales()
    {
        //copy form attributes (mainly owner_id, and owner_type) to model attributes
        //so that locales (languages) can be loaded from model instance
        $this->modelInstance->attributes = $this->getAttributes();
        return parent::locales();
    }    
    /**
     * Validation rules for locale attributes
     * 
     * Note: that all different locale values of one attributes are to be stored in db table column
     * Hence, model attribute (table column) wil have separate validation rules following underlying table definition
     * 
     * @return array validation rules for locale attributes.
     */
    public function rules()
    {
        $rules= array_merge(parent::rules(),$this->ownerRules(),$this->seoRules(),[
            ['title', 'required'],
            ['title', 'length', 'max'=>static::$titleLength],
            ['desc', 'length', 'max'=>static::$descLength],
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
            ['slug', 'ruleSlugWhitelist', 'on'=>$this->getCreateScenario()],
        ]);
        return $rules;
    }
    /**
     * Verify brand url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        $this->modelInstance->slug = $this->slug;
        $this->modelInstance->owner_id = $this->owner_id;
        $this->modelInstance->owner_type = $this->owner_type;
        $this->modelInstance->setScenario($this->getScenario());
        $this->modelInstance->ruleSlugUnique($attribute,$params);
        if ($this->modelInstance->hasErrors())
            $this->addErrors($this->modelInstance->getErrors());
    }   
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array_merge(parent::attributeToolTips(),$this->seoAttributeToolTips(),[
            'title'=>Sii::t('sii','Give a name to your page.'),
            'desc'=>Sii::t('sii','Describe what this page is about.'),
            'slug'=>Sii::t('sii','This is the page\'s SEO url. If you leave this field blank, it will be auto-generated based on page title.'),
        ]);
    }
    /**
     * Return status text
     * @param type $color
     * @return type
     */
    public function getStatusText($color=true)
    {
        return $this->modelInstance->getStatusText($color);
    }
    /** 
     * The page base url
     * @return string
     */
    public function getPageBaseUrl()
    {
        if ($this->isNewRecord){
            $page = new Page();
            $page->owner_id = $this->owner_id;
            $page->owner_type = $this->owner_type;
            return $page->getBaseUrl(true);
        }
        else {
            return $this->modelInstance->getBaseUrl(true);
        }
    }        
    /** 
     * The page layout edit url
     * @return string
     */
    public function getLayoutUrl()
    {
        return $this->modelInstance->getLayoutUrl();
    }       
    /**
     * Return multi-lang attributes that inherited form specifically owns
     * @return type
     */
    public function getLocaleAttributes()
    {
        $attributes = parent::getLocaleAttributes();
        unset($attributes['seoTitle']);
        unset($attributes['seoKeywords']);
        unset($attributes['seoDesc']);
        unset($attributes['shop_id']);//page has no this field
        return $attributes;
    }
    /**
     * Validate attributes looping through value of each locale/language
     * It also make sure at least one locale (default) must exists
     * It further validates seo attributes                   
     * @see rules()
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        parent::validateLocaleAttributes();//do first round of validation
        foreach ($this->seoParams as $field => $value) {
            $this->validateLocaleAttribute($field,$value);//Validate individual param
        }
        return !$this->hasErrors();
    }    
}
