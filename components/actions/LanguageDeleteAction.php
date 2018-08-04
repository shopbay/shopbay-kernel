<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.DeleteAction");
/**
 * Description of LanguageDeleteAction
 *
 * @author kwlok
 */
class LanguageDeleteAction extends DeleteAction 
{
    /**
     * Generate success flash
     * 
     * @param type $model
     * @return type
     */
    protected function setSuccessFlash($model)
    {
        if (!isset($this->flashTitle))
            $this->flashTitle = Sii::t('sii','{model} Delete');
        if (!isset($this->flashMessage))
            $this->flashMessage = Sii::t('sii','{name} is deleted successfully');
        user()->setFlash(isset($this->flashId)?$this->flashId:get_class($model),array(
            'message'=>str_replace('{name}',isset($this->nameAttribute)?$model->displayLanguageValue($this->nameAttribute,user()->getLocale()):$model->displayName(),$this->flashMessage),
            'type'=>'success',
            'title'=>str_replace('{model}',$model->displayName(),$this->flashTitle),
        ));
    }   
}
