<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("pages.models.PageSEOTrait");
/**
 * Description of TutorialForm
 *
 * @author kwlok
 */
class TutorialForm extends LanguageForm 
{
    use PageSEOTrait, LanguageModelTrait;
    public static $nameLength = 250;
   /*
     * Inherited attributes
     */
    public $model = 'Tutorial';
    /*
     * Local attributes
     */
    public $name;
    public $content;
    public $difficulty = Tutorial::BEGINNER;
    public $slug;
    public $tags;
    public $seoTitle;
    public $seoKeywords;
    public $seoDesc;
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
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
            'name'=>[
                'required'=>true,
                'purify'=>true,
                'inputType'=>'textField',
                'inputHtmlOptions'=>['size'=>100,'maxlength'=>static::$nameLength],
            ],
            'content'=>[
                'required'=>true,
                'purify'=>[
                    'Attr.EnableID'=>true,
                ],
                'inputType'=>'textArea',
                'inputHtmlOptions'=>['size'=>60,'rows'=>5],
                'ckeditor'=>[
                    'imageupload'=>true,
                    'js'=>'tutorialckeditor.js',
                ],
            ],
        ];
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
        return array_merge(parent::rules(),$this->seoRules(),[
            ['name', 'required'],
            ['name', 'length', 'max'=>static::$nameLength],
            ['difficulty', 'length', 'max'=>1],
            ['tags', 'length', 'max'=>500],
            
            ['slug', 'length', 'max'=>256],
            ['slug', 'ruleSlugUnique','on'=>$this->getCreateScenario()],
        ]);
    }
    /**
     * Verify brand url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        $this->modelInstance->slug = $this->slug;
        $this->modelInstance->setScenario($this->getScenario());
        $this->modelInstance->validate(['slug']);
        if ($this->modelInstance->hasErrors())
            $this->addErrors($this->modelInstance->getErrors());
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array_merge(parent::attributeToolTips(),$this->seoAttributeToolTips(),[]);
    }    
    /**
     * Return multi-lang attributes that inherited form specifically owns
     * @return type
     */
    public function getLocaleAttributes()
    {
        $attributes = parent::getLocaleAttributes();
        unset($attributes['shop_id']);//tutorial has no this field
        unset($attributes['seoTitle']);
        unset($attributes['seoKeywords']);
        unset($attributes['seoDesc']);
        return $attributes;
    }
    /**
     * Check if tutorial can be submitted
     */
    public function submitable()
    {
        return $this->modelInstance->submitable();
    }
    /**
     * Check if tutorial can be published
     */
    public function publishable()
    {
        return $this->modelInstance->publishable();
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

}
