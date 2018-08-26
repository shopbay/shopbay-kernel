<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("pages.models.PageSEOTrait");
/**
 * Description of TutorialSeriesForm
 *
 * @author kwlok
 */
class TutorialSeriesForm extends LanguageForm 
{
    use PageSEOTrait, LanguageModelTrait;
    public static $nameLength = 250;
   /*
     * Inherited attributes
     */
    public $model = 'TutorialSeries';
    /*
     * Local attributes
     */
    public $name;
    public $desc;
    public $tutorials;
    public $tags;
    public $slug;
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
            'desc'=>[
                'required'=>false,
                'purify'=>[
                    'Attr.EnableID'=>true,
                ],
                'inputType'=>'textArea',
                'inputHtmlOptions'=>['size'=>60,'rows'=>5],
                'ckeditor'=>[
                    'imageupload'=>true,
                    'js'=>'tutorialckeditor.js',
                    'csrfTokenSelector'=>'div.form'
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
            ['name, tutorials', 'required'],
            ['name', 'length', 'max'=>static::$nameLength],
            ['tutorials', 'length', 'max'=>500],
            ['slug', 'length', 'max'=>256],
            ['tags', 'length', 'max'=>500],
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
    
    public function serializeTutorialsValue()
    {
        if (isset($this->modelInstance->tutorials))
           $this->tutorials = json_decode($this->modelInstance->tutorials,true);//convert back to array
        
        if (!is_array($this->tutorials))
            $this->tutorials = [];//expect tutorials to be array 
        
        $this->tutorials = implode(',',$this->tutorials);//convert back to string
        return $this->tutorials;
    }
    
    public function getSelectedTutorialsArray($locale=null)
    {
        $tutorials = new CMap();
        foreach ($this->modelInstance->searchTutorials($locale)->rawData as $tutorial) {
            $tutorials->add($tutorial['id'],$tutorial['name']);
            
        }
        return $tutorials->toArray();
    }
    
    public function getOtherTutorialsArray($locale=null)
    {
        $others = new CMap();
        foreach (Tutorial::model()->published()->findAll() as $tutorial) {
            $found = false;
            foreach ($this->getSelectedTutorialsArray($locale) as $id => $title) {
                if ($tutorial->id==$id)
                    $found = true;
            }
            if (!$found){
                $others->add($tutorial->id,$tutorial->localeName($locale));
            }
        }
        //logTrace(__METHOD__,$others);
        return $others->toArray();
    }     
}
