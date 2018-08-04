<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.media.actions.MediaGallerySelectAction");
/**
 * Description of MultiMediaGallerySelectAction
 * 
 * @author kwlok
 */
class MultiMediaGallerySelectAction extends MediaGallerySelectAction 
{  
    /**
     * Select media from media gallery and store into session repo 
     */
    public function run() 
    {
        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_GET['m']) && isset($_GET['next'])) {
                //$_GET['next'] is the next image sequence
                logTrace(__METHOD__.' received $_GET[m]',$_GET['m']);
                $media = Media::model()->findByPk($_GET['m']);
                header('Content-type: application/json');
                
                SActiveSession::set($this->stateVariable, null);//clear session as here we are not using session media
                //all image add/delete are handled at javascript
                //Setting session image which has internally upload limit check can cause usability issue
                logTrace(__METHOD__.' clear session media');
                
                $result = $this->saveSessionMediaMultiMode($media);
                
                if (is_array($result)){//session repo is in array data structure
                    echo CJSON::encode([
                        'status'=>'success',
                        'next_num'=>$_GET['next'],
                        'image_url'=>$result['thumbnail_url'],
                    ]);            
                }
                else {
                    echo CJSON::encode([
                        'status'=>'failure',
                        'message'=>$result,
                    ]);            
                }
                Yii::app()->end();
            }
        }
        else
            throwError403(Sii::t('sii','Unauthorized Access'));
    }   
}
