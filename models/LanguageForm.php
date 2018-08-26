<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * LanguageForm serves as base form that supports multi langauges
 *
 * @author kwlok
 */
abstract class LanguageForm extends SFormModel 
{
    private $_c;//The controller that renders this form
    private $_l;//locales
    private $_m;//model instance
    private $_s;//shop model instance
    private $_new;//whether this instance is new or not
    private $_a;//The current (active) locale the form is holding
    private $_e;//the attributes to be excluded 
    /*
     * Attributes to persists its value while doing validation; 
     * As validation is done attribute by attribute, 
     * Certain validation rules requires other attribute value to be presented requires this feature
     */
    protected $persistentAttributes = [];    
    /*
     * The model that this form is representing
     */
    public $model;
    /*
     * The form id; It is also the underlying model id
     */
    public $id;
    /*
     * The account that owns the form data; It is also the underlying model account id
     */
    public $account_id;
    /*
     * The shop that owns the form data; It is also the underlying model shop id
     */
    public $shop_id;//@todo this should make it generic and not just shop alone will owns form data
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($id=null,$scenario='')
    {
        parent::__construct($scenario);
        $this->id = $id;
    }
    /**
     * Initializes this form.
     */
    public function init()
    {
        parent::init();
        if (!isset($this->model))
            throw new CException(Sii::t('sii',__CLASS__.' must have model specified.'));
        $this->checkIsNewRecord();
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
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],              
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],                      
        ];
    }     
    /**
     * Array of form attributes that require multi language support
     * Data structure: 
     * array(
     *   'attribute_1'=>array(
     *       'required'=>true,
     *       'inputType'=>'textField',
     *       'inputHtmlOptions'=>array(),
     *   ),
     *   'attribute_2'=>array(
     *       'required'=>false,
     *       'label'=>false,
     *       'inputType'=>'textArea',
     *       'inputHtmlOptions'=>array(),
     *       'note'=>'put notes here',
     *       'ckeditor'=>array(
     *            'imageupload'=>true,
     *            'js'=>'ckeditor.js',
     *       ),
     *    ),
     * );
     * 
     * @return array Definitions of form attributes that requires multi languages
     */    
    abstract public function localeAttributes();
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id', 'required'],
            ['account_id, shop_id', 'numerical', 'integerOnly'=>true],
        ];
    }
    /**
     * Set locales
     * @param type $locales
     */
    public function setLocales($locales)
    {
        $this->_l = $locales;
    }
    /**
     * Set locales
     * @param type $locales
     */
    public function getLocales()
    {
        return $this->_l;
    }    
    /**
     * Supported locales
     * If not specified, it will be auto loaded from Shop::getLanguages()
     * And, resort locales to have shop locale always be the first one
     */
    public function locales()
    {
        if (!isset($this->_l)){
            if (isset($this->shop_id))
                $locales = $this->shop->getLanguages();
            else//cater for Shop itself
                $locales = $this->modelInstance->getLanguages();
            //set default locale as first one in array
            $this->sortLocales($locales);
        }
        return $this->_l;
    }
    /**
     * Sort locales to have shop locale always be the first one
     */
    public function sortLocales($locales) 
    {
        $this->_l = [$this->getLanguageDefaultLocale()=>$locales[$this->getLanguageDefaultLocale()]];
        unset($locales[$this->getLanguageDefaultLocale()]);//remove default locale from locales set
        $this->_l = array_merge($this->_l, $locales);//merge with remaining locales set
        return $this->_l;
    }
    /**
     * Validate attributes looping through value of each locale/language
     * It also make sure at least one locale (default) must exists
     *                   
     * @see rules()
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        foreach ($this->getLocaleAttributes() as $attribute => $value) {
            if (is_array($value)){
                foreach ($value as $locale => $localValue) {
                    $this->setCurrentLocale($locale);
                    if (!empty($localValue))
                        $this->validateLocaleAttribute($attribute, $localValue, $locale);
                    else {
                        //this rule make sure that at least the shop locale (default) must exists
                        if ($locale==$this->getLanguageDefaultLocale())
                            $this->validateLocaleAttribute($attribute, $localValue, $locale);
                    }
                }
            }
            else {
                $this->validateLocaleAttribute($attribute, $value);
            }
        }
        return !$this->hasErrors();
    }
    /**
     * Validate a specific attribute
     * This uses a separate form instance to do validation on specific attribute, 
     * and if has error, add to current form errors
     * 
     * Validation are performed based on validation rules defined in rules()
     * 
     * @see rules()
     * @param type $attribute
     * @param type $value
     * @param type $locale
     */
    protected function validateLocaleAttribute($attribute,$value,$locale=null)
    {
        $form = $this->cloneForm();
        $form->setScenario($this->getScenario());
        $form->setCurrentLocale($this->getCurrentLocale());
        foreach ($this->persistentAttributes as $field) {
            $form->$field = $this->$field;//need to persist this field to do validation
        }
        $form->$attribute = $value;
        //logTrace(__METHOD__." $locale.$attribute value='$value'");
        if (!$form->validate([$attribute])){
            logError(__METHOD__." $locale $attribute errors",$form->getErrors($attribute),false);
            $this->addError(isset($locale)?$this->formatErrorName($locale, $attribute):$attribute, (isset($locale)?SLocale::getLanguages($locale).' ':'').$form->getError($attribute));
        }
    }
    /**
     * Serialize multi-lang attributes 
     * @param array $form form attributes (or model->getAttributes())
     * @param boolean $serialize Json encode into string
     * @param array $exclude attributes to be excluded from assignment
     */
    public function assignLocaleAttributes($form,$serialize=false,$exclude=[])
    {
        foreach ($form as $attribute => $value) {
            //logTrace(__METHOD__.' '.$attribute);
            if (in_array($attribute, $exclude)==false){
                if ($serialize && in_array($attribute,array_keys($this->localeAttributes())))
                    $this->$attribute = trim(json_encode($form[$attribute]),'"');//trim away double quotes for empty values ended with "" (not all cases?)
                else
                    $this->$attribute = $form[$attribute];
            }
        }        
    }   
    /**
     * Transform locale attributes from json encoded into vector form (array)
     */
    public function vectorizeLocaleAttributes()
    {
        foreach (array_keys($this->localeAttributes()) as $attribute) {
            logTrace(__METHOD__.' '.$attribute,$this->$attribute);
            $this->$attribute = json_decode($this->$attribute,true);
        }
    }    
    /**
     * Transform locale attributes from vector form (array) into json encoded
     */
    public function devectorizeLocaleAttributes()
    {
        foreach (array_keys($this->localeAttributes()) as $attribute) {
            logTrace(__METHOD__.' '.$attribute,$this->$attribute);
            $this->$attribute = json_encode($this->$attribute);
        }
    }    
    /**
     * Return multi-lang attributes that inherited form specifically owns
     * @return type
     */
    public function getLocaleAttributes()
    {
        $attributes = $this->getAttributes();
        unset($attributes['model']);
        return $attributes;
    }
    /**
     * Return multi-lang attributes keys that child form specifically owns
     * @return type
     */
    public function getLocaleAttributeKeys()
    {
        return array_keys($this->getLocaleAttributes());
    }
    /**
     * Check if exists a particular multi-lang attribute
     * @return boolean
     */
    public function existsLocaleAttribute($attribute)
    {
        return array_key_exists($attribute, $this->getLocaleAttributes());
    }
    /**
     * Render all form attributes
     * @param CController $controller
     * @param string $readonly Indicate if form is readonly
     * @param string $attributes Indicate which locale attributes to render; Empty means render all locale attributes
     * @param string $return optional
     */
    public function renderForm($controller,$readonly=false,$attributes=[],$return=false)
    {
        $this->_c = $controller;
        if ($return){
            return $this->_c->widget('common.widgets.spagetab.SPageTab',[
                'id'=>$this->formatFormTabId(),
                //'name'=>'Form Title',
                'tabs'=>$this->getLocalePages($readonly,$attributes)
            ],true);     
        }
        else {
            $this->_c->widget('common.widgets.spagetab.SPageTab',[
                //'name'=>'Form Title',
                'tabs'=>$this->getLocalePages($readonly,$attributes)
            ]);     
        }
    }
    /**
     * Return all locale pages
     * @param type $readonly
     * @param string $attributes Indicate which locale attributes to render; Empty means render all locale attributes
     * @return array
     */
    protected function getLocalePages($readonly=false,$attributes=[])
    {
        $content = [];
        foreach ($this->locales() as $locale => $localeTitle) {
            $content[] = [
                'key'=>get_class($this).'-'.$locale.'-'.$this->formatFormTabId(),
                'title'=>$localeTitle,
                'content'=>$this->getLocalePageContent($locale,$readonly,$attributes),
            ];
        }     
        return $content;
    } 
    /**
     * Return unique locale page id
     * (Leverage on CWidget()->id)
     * @return type
     */
    protected function getLocalePageId()
    {
        $widget = new CWidget();
        return $widget->getId();
    }    
    /**
     * Return a particular locale page content
     * @param type $locale
     * @param type $readonly
     * @param string $attributes Indicate which locale attributes to render; 
     *        Empty means render all locale attributes
     *        @see childForm::localeAttributes() for $attributes defintion. Example:
     * <pre>
     *  return [
     *      'name'=>[
     *          'required'=>true,
     *          'purify'=>true,
     *          'inputType'=>'textField',
     *          'inputHtmlOptions'=>['size'=>100,'maxlength'=>static::$nameLength],
     *      ],
     *      'content'=>[
     *          'required'=>true,
     *          'purify'=>[
     *              'Attr.EnableID'=>true,
     *          ],
     *          'inputType'=>'textArea',
     *          'inputHtmlOptions'=>['size'=>60,'rows'=>5],
     *          'ckeditor'=>[
     *              'imageupload'=>true,
     *              'js'=>'tutorialckeditor.js',
     *              'csrfTokenSelector'=>'div.form', // default to '.data-form form'
     *          ],
     *      ],
     *  ];
     * </pre>
     * @return type
     */
    protected function getLocalePageContent($locale,$readonly=false,$attributes=[])
    {
        if (empty($attributes))
            $attributes = $this->localeAttributes();
        else {
            $temp = new CMap();
            foreach($this->localeAttributes() as $key => $config) {
                if (in_array($key, $attributes)){
                    $temp->add($key, $config);
                }
            }
            $attributes = $temp->toArray();
            //logTrace(__METHOD__.' for targeted attributes', $attributes);
        }
        
        $content = $readonly?CHtml::openTag('div',['class'=>'detail-view']):'';
        foreach (array_keys($attributes) as $attr) {
            $value = $this->getLanguageValue($attr,$locale,$readonly);
            
            if (isset($attributes[$attr]['note'])){
                $note = '';
                if (is_array($attributes[$attr]['note'])){
                    foreach($attributes[$attr]['note'] as $text)
                        $note .= $text.'<br>';
                }
                else {
                    $note .= $attributes[$attr]['note'];
                }
            }

            if (isset($attributes[$attr]['purify']) && $attributes[$attr]['purify']){
                logTrace(__METHOD__.' purify '.$attr.' '.$locale);                    
                $value = Helper::purify($value,is_array($attributes[$attr]['purify'])?$attributes[$attr]['purify']:[]);
            }
            if ($readonly){//READONLY
                $content .= CHtml::openTag('div',['class'=>'data-element']);
                if ((isset($attributes[$attr]['label']) && $attributes[$attr]['label']) || !isset($attributes[$attr]['label']))
                    $content .= CHtml::tag('span',['class'=>'key'],$this->getAttributeLabel($attr));
                if (isset($note))
                    $content .= CHtml::tag('p',['class'=>$attr.' note '],$note);
                $content .= CHtml::tag('span',['class'=>'value'],$value);
            }
            else {
                $content .= CHtml::openTag('div',['class'=>'row '.$this->formatAttributeClass($locale, $attr),'data-locale'=>$locale]);
                if ((isset($attributes[$attr]['label']) && $attributes[$attr]['label']) || !isset($attributes[$attr]['label'])) {
                    $label = $this->getAttributeLabel($attr);
                    if ($this->getToolTip($attr)!=null)
                        $label .= $this->_c->stooltipWidget($this->getToolTip($attr),[],true);

                    $content .= CHtml::label($label, false, ['required' => $attributes[$attr]['required']]);
                }
                if (isset($note))
                    $content .= CHtml::tag('p',['class'=>$attr.' note '],$note);

                switch ($attributes[$attr]['inputType']) {
                    case 'textField':
                        $content .= CHtml::textField($this->formatAttributeName($locale,$attr),$value,$attributes[$attr]['inputHtmlOptions']);
                        break;
                    case 'textArea':
                        $content .= CHtml::textArea($this->formatAttributeName($locale,$attr),$value,$attributes[$attr]['inputHtmlOptions']);
                        if (isset($attributes[$attr]['ckeditor']) && isset($this->_c)){
                            if (isset($attributes[$attr]['ckeditor']['imageupload']) && $attributes[$attr]['ckeditor']['imageupload']){
                                $csrfTokenSelector = isset($attributes[$attr]['ckeditor']['csrfTokenSelector'])?$attributes[$attr]['ckeditor']['csrfTokenSelector']:'.data-form form';
                                $filebrowserImageUploadUrl = 'filebrowserImageUploadUrl : \'/'.$this->_c->module->id.'/'.$this->_c->id.'/ckeditorimageupload?APP_CSRF_TOKEN=\'+$(\''.$csrfTokenSelector.'\').find(\'input[type="hidden"]\').val(),';
                            }
                            if (isset($attributes[$attr]['ckeditor']['js']))
                                $customConfig = 'customConfig:\''.$this->_c->module->getAssetsURL($this->_c->module->pathAlias.'.js').'/'.$this->_c->module->getAssetFilename($attributes[$attr]['ckeditor']['js']).'\'';
                            $script = (isset($filebrowserImageUploadUrl)?$filebrowserImageUploadUrl:'').(isset($customConfig)?$customConfig:'');
                            if (strlen($script)>0)
                                cs()->registerScript('ckeditor'.$locale,'CKEDITOR.replace(\''.get_class($this).'_'.$attr.'_'.$locale.'\',{'.$script.'});');
                        }
                        break;
                    default:
                        break;
                }
                //show validation errors if any
                if ($this->hasErrors($this->formatErrorName($locale, $attr))){
                    $content .= CHtml::tag('div',['class'=>'errorMessage'],$this->getError($this->formatErrorName($locale, $attr)));
                    $errorScript = '$(\'.'.$this->formatAttributeClass($locale, $attr).' label\').addClass(\'error\');';
                    switch ($attributes[$attr]['inputType']) {
                        case 'textField':
                            $errorScript .= '$(\'.'.$this->formatAttributeClass($locale, $attr).' input\').addClass(\'error\');';
                            break;
                        case 'textArea':
                            $errorScript .= '$(\'.'.$this->formatAttributeClass($locale, $attr).' textarea\').addClass(\'error\');';
                            break;
                        default:
                            break;
                    }
                    cs()->registerScript(get_class($this).'error'.$attr.$locale,$errorScript);
                }
            }
            $content .= CHtml::closeTag('div');
        }
        $content .= $readonly?CHtml::closeTag('div'):'';
        return $content;
    }
    protected function formatAttributeName($locale,$attribute)
    {
        return get_class($this).'['.$attribute.']['.$locale.']';
    }
    protected function formatAttributeClass($locale,$attribute)
    {
        return get_class($this).'-'.$attribute.'-'.$locale;
    }
    protected function formatErrorName($locale,$attribute)
    {
        return $attribute.$locale;
    }
    protected function formatFormTabId()
    {
        return $this->getLocalePageId().'_'.$this->id;
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        if (isset($this->model)){
            $model = $this->model;
            return $model::model()->attributeLabels();
        }
        else 
            return parent::attributeLabels();        
    }      
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        if (isset($this->model)){
            $model = $this->model;
            return $model::model()->attributeToolTips();
        }
        else 
            return parent::attributeToolTips();
    }  
    /**
     * Form display name, follows $model name
     * @return string
     */
    public function displayName() 
    {
        if (isset($this->model)){
            $model = $this->model;
            return $model::model()->displayName();
        }
        else 
            return Sii::t('sii','unset');
    }
    /**
     * Return model instance
     * If $this->id has value, it try load underlying model from db
     * @return CModel
     */
    public function getModelInstance() 
    {
        if (isset($this->model)){
            $model = $this->model;
            if (!isset($this->_m)){
                if (isset($this->id)){
                    $this->_m = $model::model()->findByPk($this->id);
                    if ($this->_m===null)//if model not found
                        $this->_m = new $model();
                }
                else
                    $this->_m = new $model();
            }
            return $this->_m;
        }
        else 
            return null;
    }
    /**
     * Return shop model
     * If $this->shop_id has value, it try load underlying model from db
     * @return CModel
     */
    public function getShop() 
    {
        if (isset($this->shop_id)){
            if (!isset($this->_s))
                $this->_s = Shop::model()->findByPk($this->shop_id);
            return $this->_s;
        }
        else 
            return null;
    }     
    /**
     * Load locale attributes from model instance 
     * @param array $exclude attributes to be excluded from loading
     */
    public function loadLocaleAttributes($exclude=[])
    {
        foreach ($this->getLocaleAttributeKeys() as $attribute) {
            if (in_array($attribute, $exclude)==false)
                $this->$attribute = $this->modelInstance->$attribute;
        }
        $this->checkIsNewRecord();
    }        
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return $this->modelInstance->getViewUrl();
    }    
    /**
     * Returns if the current record is new.
     * @see CActiveRecord mimic its method
     */
    public function getIsNewRecord()
    {
        return $this->_new;
    }
    /**
     * Sets if the record is new.
     * @param boolean $value whether the record is new
     * @see getIsNewRecord
     */
    public function setIsNewRecord($value)
    {
        $this->_new=$value;
    }  
    /**
     * Check if record is new or old
     */
    public function checkIsNewRecord()
    {
        if (isset($this->id))
            $this->setIsNewRecord(false);
        else
            $this->setIsNewRecord(true);
    }
    
    public function setAttributeExclusion($exclude)
    {
        if (!isset($this->_e))
            $this->_e = [];//assign empty array
        $this->_e = array_merge($this->_e,$exclude);
    }
    
    public function getAttributeExclusion($additionalExclude=[])
    {
        return array_merge($this->_e,$additionalExclude);
    }
    
    public function setCurrentLocale($locale)
    {
        $this->_a = $locale;
    }
    public function getCurrentLocale()
    {
        return $this->_a;
    }
    /**
     * Clone a form for validation use
     * @return \form
     */
    protected function cloneForm()
    {
        $form = get_class($this);
        return new $form;
    }
}