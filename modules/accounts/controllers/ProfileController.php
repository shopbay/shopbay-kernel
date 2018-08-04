<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ProfileController
 *
 * @author kwlok
 */
class ProfileController extends AccountBaseController 
{
    public function init()
    {
        parent::init();
        $this->modelType = 'AccountProfile';
        //-----------------
        // @see ImageControllerTrait
        $this->sessionActionsExclude = [//customize, keep one action to exclude
            $this->imageUploadAction, 
        ];
    }        
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),array(
            'index'=>array(
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'finderMethod'=>'all',
            ),               
            $this->imageUploadAction =>array(
                'class'=>'common.widgets.simagemanager.actions.ImageUploadAction',
                'multipleImages'=>false,
                'stateVariable'=> $this->imageStateVariable,
                'secureFileNames'=>true,
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ),
        ));
    }   
}