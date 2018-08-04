<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SessionMedia
 *
 * @author kwlok
 */
class SessionMedia 
{
    const UPLOAD_ACTION  = 'imageupload';
    /**
     * Update Image/Media repo in its data structure
     * This is the data structure stored in session as well
     * 
     * @param type $repo
     * @param mixed $mediaObj
     * @return type
     */
    public static function updateRepository($repo,$thumbnailUrl,$mediaObj,$primary=false,$imagePath=null,$deleteRoute=SessionMedia::UPLOAD_ACTION)
    {
        //load CSRF token
        $cookies = Yii::app()->request->getCookies();
        $cookieToken = $cookies->contains(Yii::app()->request->csrfTokenName)?$cookies->itemAt(Yii::app()->request->csrfTokenName)->value:'';
        //base structure
        $data = [
            'primary' => $primary,                    
            'thumbnail_url' => $thumbnailUrl,
            'size' => $mediaObj->size,
            'mime' => $mediaObj->mime_type,
            'name' => $mediaObj->name,
            'filename' => $mediaObj->filename,
            'delete_type'=>'DELETE',
            'delete_url' => Yii::app()->getController()->createUrl($deleteRoute).'?_method=delete&file='.$mediaObj->filename.'&'.Yii::app()->request->csrfTokenName.'='.$cookieToken,
//            'delete_url' => Yii::app()->getController()->createUrl(self::UPLOAD_ACTION, array(
//                "_method" => "delete",
//                "file" => $imageObj->filename,
//                Yii::app()->request->csrfTokenName=>$cookieToken,                    
//            )),
        ];
        
        if ($mediaObj instanceof Image){
            $repo[$mediaObj->filename] =  array_merge($data,[ 
                'id' => $mediaObj->id,//only available for persistent image in DB
                'path' => $mediaObj->name==Image::EXTERNAL_IMAGE?$mediaObj->src_url:$imagePath.$mediaObj->src_url,
                'url' => $mediaObj->src_url,
            ]);
        }
        elseif ($mediaObj instanceof Media){
            $repo[$mediaObj->filename] =  array_merge($data,[
                'id' => $mediaObj->id,//only available for persistent image in DB
                'path' => $mediaObj->name==Image::EXTERNAL_IMAGE?$mediaObj->src_url:$mediaObj->filepath,
                'url' => $mediaObj->getPreviewUrl(null, request()->isSecureConnection),//use cdn path
            ]);
        }
        else {
            $repo[$mediaObj->filename] = array_merge($data,[ 
                "path" => $mediaObj->name==Image::EXTERNAL_IMAGE?$mediaObj->src_url:$imagePath.$mediaObj->filename,
                "url" => $thumbnailUrl,
            ]);
        }    
        return $repo;
    }  
    
}
