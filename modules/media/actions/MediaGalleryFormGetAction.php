<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.simagemanager.SImageManager");
/**
 * Description of MediaGalleryFormGetAction
 *
 * @author kwlok
 */
class MediaGalleryFormGetAction extends CAction 
{
    public $formModel = SImageManager::MULTIPLE_IMAGES;  
    public $formContainerId = 'media_gallery_modal';
    public $mediaGallerySelectAction = 'mediagalleryselect';
    /**
     * Get product image url form
     */
    public function run() 
    {
        if (Yii::app()->request->isAjaxRequest) {
            $config = [
                'route'=>url($this->controller->module->id .'/'.$this->controller->id.'/'.$this->mediaGallerySelectAction),
                'container'=>$this->formContainerId,
                'formModel'=>$this->formModel,
            ];
            if (isset($_GET['pager']) && $_GET['pager']){//from pagination 
                $config = array_merge($config,['paginationMode'=>true]);
                $modal = $this->controller->widget('common.modules.media.widgets.smediagallery.SMediaGallery',$config,true);
            }
            else {
                $modal = $this->controller->widget('common.widgets.smodal.SModal',array(
                        'container'=>$this->formContainerId,
                        'content'=>$this->controller->widget('common.modules.media.widgets.smediagallery.SMediaGallery',$config,true),
                        'closeButton'=>false,
                    ),true);
            }
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