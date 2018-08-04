<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of FilesController
 *
 * @author kwlok
 */
class FilesController extends SController 
{
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return [
            ['allow',  
                'actions'=>['index'],
                'users'=>['*'],
            ],
            //default deny all users anything not specified       
            ['deny',  
                'users'=>['*'],
            ],
        ];
    }    
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'index'=>[
                'class'=>'common.modules.media.actions.AssetsAction',
                'modelStatus'=>Process::MEDIA_ONLINE,
            ],        
        ];
    }
    
}
