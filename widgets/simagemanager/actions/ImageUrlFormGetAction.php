<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.simagemanager.SImageManager");
/**
 * Description of ImageUrlFormGetAction
 *
 * @author kwlok
 */
class ImageUrlFormGetAction extends CAction 
{
    /**
     * Image form model class name
     * @var string
     */
    public $formModel           = SImageManager::MULTIPLE_IMAGES;  
    public $formView            = 'common.widgets.simagemanager.views.urlform';
    public $formContainerId     = 'imageurlform_modal';
    public $addImageByUrlAction = 'imageurladd';
    /**
     * Get product image url form
     */
    public function run() 
    {
        if (Yii::app()->request->isAjaxRequest) {
            $modal = $this->controller->widget('common.widgets.smodal.SModal',array(
                        'container'=>$this->formContainerId,
                        'content'=>$this->controller->renderPartial($this->formView,array(
                            'route'=>url($this->controller->module->id .'/'.$this->controller->id.'/'.$this->addImageByUrlAction),
                            'container'=>$this->formContainerId,
                            'formModel'=>$this->formModel,
                        ),true),
                        'closeButton'=>false,
                    ),true);
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'modal'=>$modal,
            ));            
            Yii::app()->end();
        }
        else
            throwError404(Sii::t('sii','The requested page does not exist'));
    }    
}