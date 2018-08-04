<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of JsonSettingsForm
 *
 * @author kwlok
 */
abstract class JsonSettingsForm extends SFormModel 
{
    CONST RULE_EQUALS = 'equals';
    CONST RULE_CONTAINS = 'contains';
    
    private $_o;//owner instance
    /*
     * The owner model class
     */
    abstract public function getOwnerClass();
    /*
     * The owner setting model attribute
     */
    abstract public function getOwnerSettingClass();
    /*
     * The owner model attribute
     */
    abstract public function getOwnerAttribute();
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            [$this->ownerAttribute, 'required'],
            [$this->ownerAttribute, 'numerical', 'integerOnly'=>true],
        ];
    }    
    /**
     * Return owner model
     * If $this->ownerAttribute has value, it try load underlying model from db
     * @return CModel
     */
    public function getOwner() 
    {
        if (isset($this->{$this->ownerAttribute})){
            if (!isset($this->_o)){
                $model = $this->getOwnerClass();
                $this->_o = $model::model()->findByPk($this->{$this->ownerAttribute});
            }
            return $this->_o;
        }
        else 
            return null;
    }         
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [];
    }
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return [];
    }  
    /**
     * @return array customized attribute display value (name=>label)
     */
    public function getDisplayValue($attribute)
    {
        $displayValues = $this->attributeDisplayValues();
        return isset($displayValues[$attribute])?$displayValues[$attribute]:Sii::t('sii','System defaults');
    }      
    /**
     * @return array customized attribute values that do not display
     */
    public function attributeDisplayNone()
    {
        return [
            $this->ownerAttribute,
        ];
    }  
    /**
     * @return boolean
     */
    public function isAllowDisplay($attribute)
    {
        return !in_array($attribute, $this->attributeDisplayNone());
    }      
    /**
     * Display all setting values of this form
     * @return type
     */
    public function displaySettings()
    {
        $text = new CMap();
        foreach ($this->attributes as $attribute => $rawValue){
            if ($this->isAllowDisplay($attribute))
                $text->add($this->getAttributeLabel($attribute),$this->getDisplayValue($attribute));
        }
        return ['key-value-element'=>$text->toArray()];
    }
    /**
     * @return The owner model display name
     */
    public function displayName()
    {
        $settingModel = $this->ownerSettingClass;
        return $settingModel::model()->displayName();        
    }   
    /**
     * This render the setting form
     * @param type $controller The controller used to render the form
     * @param $echo If to echo or return as string
     */
    public function renderForm($controller,$echo=true)
    {
        if ($echo)
            $controller->renderPartial($this->formViewFile(),['model'=>$this]);
        else
            return $controller->renderPartial($this->formViewFile(),['model'=>$this],true);
    }
    /**
     * Not using 'getter' to prevent attribute auto saved into json
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        throw new CException('Please define form view file');       
    }
    /**
     * If this form as a sub form (Second form)
     * @return type
     */
    public function hasSubForm()
    {
        return $this->subFormViewFile()!=false;
    }
    /**
     * @return The sub form view file to be rendered
     */
    public function subFormViewFile()
    {
        return false;       
    }      
    /**
     * Default no sub form to render
     * @param type $controller
     * @param string $subFormId
     */
    public function renderSubForm($controller,$subFormId='settings_form_2')
    {
        echo '';//render nothing
    }
    /**
     * This render the main active form  
     * @param type $controller The controller used to render the form
     */
    public function renderActiveForm($controller)
    {
        $form = $controller->beginWidget('CActiveForm', ['id'=>'settings_form']);

        echo $form->errorSummary($this); 

        $this->renderForm($controller);

        echo '<div class="row" style="padding-top:20px;clear:left">';

        $controller->widget('zii.widgets.jui.CJuiButton',[
            'name'=>'actionButtonForm1',
            'buttonType'=>'button',
            'caption'=>Sii::t('sii','Save'),
            'value'=>'actionbtn',
            'onclick'=>'js:function(){'.$this->defaultSubmitFormScript().'}',
        ]);

        echo '</div>'; 

        $controller->endWidget();         
    }     
    
    protected function defaultSubmitFormScript() 
    {
        return 'submitform("settings_form");';
    }
}
