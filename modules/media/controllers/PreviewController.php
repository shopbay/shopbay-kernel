<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.media.controllers.FilesController');
/**
 * Description of PreviewController
 * No media status or ownership validation.
 * Anybody with the media preview url will be able to access (with a valid media file id)
 * e.g. http://<domain>/media/assets/preview/[media_file_id].jpg
 * 
 * @see Meida::getAssetUrl() for 'preview'
 * 
 * @author kwlok
 */
class PreviewController extends FilesController 
{
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'index'=>[
                'class'=>'common.modules.media.actions.AssetsAction',
            ],        
        ];
    }
}
