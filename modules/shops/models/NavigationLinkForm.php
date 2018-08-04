<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of NavigationLinkForm
 *
 * @author kwlok
 */
class NavigationLinkForm extends LanguageForm
{
    protected $sample = false;
    /*
     * Inherited attributes
     */
    public $model = 'Shop';
    /*
     * Local attributes
     */
    public $title;
    public $link;
    /**
     * Initializes this form.
     */
    public function init()
    {
        parent::init();
        $this->shop_id = $this->id;
    }  
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
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
                'label'=>false,
                'inputType'=>'textField',
                'inputHtmlOptions'=>['maxlength'=>20,'placeholder'=>Sii::t('sii','Enter menu heading here')],
            ],
        ];
    }  
    /**
     * Validation rules for locale attributes
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['title', 'length', 'max'=>20],
            ['link', 'length', 'max'=>500],
            ['link', 'url'],
        ];
    }
    
    public function setAsSample()
    {
        $this->sample = true;
    }
    
    protected function formatFormTabId()
    {
        if ($this->sample)
            return 'yw';//let javascript to auto assign id
        else
            return $this->getLocalePageId().'_'.$this->id;
    }    
}
