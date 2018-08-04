<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BrandSettingsTrait
 * NOTE: Model associated with this trait must be a child class of JsonSettingsForm
 * As this trait assumes some attributes 'ownerClass', 'ownerAttribute' etc of JsonSettingsForm are presented
 *
 * @author kwlok
 */
trait BrandSettingsTrait 
{
    public $myDomain;//this is the seller's own domain
    public $favicon;  
    /**
     * Declares the validation rules.
     */
    public function brandRules()
    {
        return [
            ['favicon', 'numerical', 'integerOnly'=>true],
            ['myOwnDomain', 'length', 'min'=>8, 'max'=>500],
            //validate valid domain
            ['myDomain', 'match', 'pattern'=>'/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', 'message'=>Sii::t('sii','You have entered an invalid domain name.')],
            //scan through existing shop seller owned domain used
            ['myDomain', 'ruleMyDomain'],
        ];
    }
    /**
     * Validation rules for taken my domain
     */
    public function ruleMyDomain($attribute,$params)
    {
        if (!empty($this->myDomain)){
            $settingModelClass = $this->ownerSettingClass;
            $settingModel = $settingModelClass::model()->myDomain($this->myDomain)->find();
            if ($settingModel!=null && $settingModel->{$this->ownerAttribute} != $this->{$this->ownerAttribute})
                $this->addError($attribute, Sii::t('sii','{model} domain "{domain}" is already taken. Please try others.',['{domain}'=>$this->myDomain,'{owner}'=>$this->owner->displayName()]));
        }
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        $labels = [];
        if (isset($this->owner)){
            $labels = [
                'customDomain' => Sii::t('sii','{model} Custom Domain',['{model}'=>$this->owner->displayName()]),
                'myDomain' => Sii::t('sii','My {model} Domain',['{model}'=>$this->owner->displayName()]),
                'favicon' => Sii::t('sii','{model} Favicon',['{model}'=>$this->owner->displayName()]),
            ];
        }
        return array_merge(parent::attributeLabels(),$labels);
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array_merge(parent::attributeToolTips(),[
            'customDomain'=>Sii::t('sii','Personalize your {model} url by specifying your desire custom domain here. Please take note that you cannot edit the domain name after creation.',['{model}'=>$this->owner->displayName()]),
            'myDomain'=>Sii::t('sii','Input your own domain here. Please ensure a valid domain name, e.g. www.yourdomain.com'),
            'favicon'=>Sii::t('sii','Have your {model} favicon appears in the browser’s address bar and bookmarks. Image size: 200x200 pixel (Jpg/Png)',['{model}'=>$this->owner->displayName()]),
        ]);
    }  
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),[
            'customDomain'=>CHtml::tag('div',['class'=>'data-element'],$this->owner->url),
            'favicon'=>CHtml::tag('div',['class'=>'data-element'],CHtml::image($this->owner->getFaviconUrl(),'Favicon',array('width'=>200))),
        ]);
    }  
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_brand';       
    }         
    /**
     * @return string
     */
    public function subFormViewFile()
    {
        return 'shops.views.settings._form_favicon';
    }      
    
    public function renderSubForm($controller,$subFormId='settings_form_2')
    {
        $controller->renderPartial($this->subFormViewFile(),['model'=>$this]);
    }
}
