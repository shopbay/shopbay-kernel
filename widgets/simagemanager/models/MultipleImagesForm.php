<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.simagemanager.models.SingleImageForm");
/**
 * Description of MultipleImagesForm
 *
 * @author kwlok
 */
class MultipleImagesForm extends SingleImageForm 
{
    public $filesContainer = '.files';
    public $uploadLimit;
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
        $this->uploadLimit = Config::getBusinessSetting('limit_product_image');
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('size', 'limitcheck','params'=>array()),
            array('stateVariable', 'limitcheck','on'=>'limit'),
        ));
    }   
    /**
     * Validate image upload limit
     */
    public function limitcheck($attribute,$params)
    {
        logTrace(__METHOD__.' SActiveSession::count('.$this->stateVariable.') = '.SActiveSession::count($this->stateVariable));
        if (SActiveSession::count($this->stateVariable)>=$this->uploadLimit)
            $this->addError($attribute,Sii::t('sii','Maximum {max} images only are allowed.',array('{max}'=>$this->uploadLimit)));
    }
    /**
     * @return \CJavaScriptExpression bind onClick event to delete button
     */
    public function getDeleteButtonScript()
    {
        return 'enabledeletebutton_multi(".files .btn.btn-danger");';
    }
}