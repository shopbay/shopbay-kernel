<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ImageGetAction
 *
 * @author kwlok
 */
class ImageGetAction extends CAction 
{
    /**
     * Name of the model to load image. Defaults to 'Image'
     * @var string
     */
    public $model = 'Image';
    /**
     * Get product image
     * @param integer $id the ID of the image model
     */
    public function run() 
    {
        if(Yii::app()->request->isAjaxRequest) {
            if(isset($_GET['id']) && isset($_GET['size'])) {
                $_modelClass = $this->model;
                $model = $_modelClass::model()->findByPk($_GET['id']);
                if ($model==null)
                     echo Sii::t('sii','Image not found');
                else {
                    if (isset($_GET['size']))
                      echo $model->render($_GET['size'],'Image',array('original'=>$model->getUrl()));
                    else
                      echo $model->render(Image::VERSION_MEDIUM,'Image',array('class'=>$model->getUrl()));
                }
                Yii::app()->end();
            }
        }
        else
            throwError404(Sii::t('sii','The requested page does not exist'));
    }    
}