<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of DownloadController
 *
 * @author kwlok
 */
class DownloadController extends AuthenticatedController
{
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),array(
            'attachment'=>array(
                'class'=>'common.components.actions.DownloadAction',
                'model'=>'Attachment',
            ),                    
        ));
    }  
}
