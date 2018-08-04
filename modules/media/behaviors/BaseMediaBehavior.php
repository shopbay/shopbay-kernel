<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BaseMediaBehavior
 *
 * @author kwlok
 */
class BaseMediaBehavior extends CActiveRecordBehavior 
{
    /**
     * @var boolean When true, it will bring media to online following owner status; Default to "false"
     */
    public $transitionMedia = false;
    /**
     * @var string The name of the attribute for root directory. Defaults to '/undefined'
     */
    public $stateVariable = 'undefined';
    /**
     * @var string The name of the default image to load if object image not found. Defaults to 'DEFAULT_IMAGE'
     */
    public $imageDefault = Image::DEFAULT_IMAGE;
    /**
     * @var string The name of the label to display for upload icon. Defaults to 'Media'
     */
    public $label = 'Media';
    /**
     * The media grouping (for further categorization on top of model)
     * @see Media::attachToOwner()
     * @var string 
     */
    public $mediaGroup;
    /**
     * @var boolean If to check storage limit
     */
    public $checkStorageLimit = true;//Default to validate media storage size via Api

    public function loadMediaModel($id)
    {
        return Media::model()->findByPk($id);
    }    
    
    public function getMediaAssocation($media)
    {
        foreach ($media->associations as $assoc) {
            if ($assoc->media_id==$media->id && 
                $assoc->obj_type==$this->getOwner()->tableName() && 
                $assoc->obj_id==$this->getOwner()->id)
                return $assoc;
        }
        return null;//not found
    }    
    
    public function loadMediaAssociationModel($id)
    {
        return MediaAssociation::model()->findByPk($id);
    }    
    
    public function hasImage()
    {
        return $this->getOwner()->image!=null;
    }
        
    public function hasSessionImages() 
    {
        return SActiveSession::exists($this->stateVariable);
    }    
    /**
     * For single image 
     * @return session image
     * @see SessionMedia::updateRepository() for session image data structure
     */
    public function getSessionImageThumbnail() 
    {
        $image = array_values(SActiveSession::get($this->stateVariable));
        return $image[0]['thumbnail_url'];//return first image
    }    
    /**
     * Detach media association 
     * Physical media file is still stored at media storage
     * @param type $extraCriteria
     */
    public function detachMediaAssociation($extraCriteria=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$this->getOwner()->tableName()));
        $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
        if (isset($this->mediaGroup))
            $criteria->addColumnCondition(array('media_group'=>$this->mediaGroup));
    
        if (isset($extraCriteria)){
            $criteria->mergeWith($extraCriteria);
        }
        logTrace(__METHOD__.' criteria',$criteria);
        foreach (MediaAssociation::model()->findAll($criteria) as $unwantedMedia){
           try {
                //delete db record
                logTrace(__METHOD__.' media association record deleted from db',$unwantedMedia->getAttributes());
                $unwantedMedia->delete();
                
            } catch (CException $e) {
                logError(__METHOD__.' media association delete error ',$e->getTrace());
            }
        }
    }      
    
    public function searchMediaAssociation()
    {
        if ($this->getOwner()->id==null)
            return null;
        else {
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(array('obj_type'=>$this->getOwner()->tableName()));
            $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
            if (isset($this->mediaGroup))
                $criteria->addColumnCondition(array('media_group'=>$this->mediaGroup));
            return new CActiveDataProvider('MediaAssociation',array('criteria'=>$criteria));
        }
    }   
    /**
     * @return boolean Check if has multiple images
     */
    public function getHasMultipleImages()
    {
        return $this->getImagesCount() > 1;
    }    
    
    /**
     * Return all the images url in array format
     * @param mixed|integer $limit If not set, return all images; If set, return the first found nth images
     * @return array return image urls
     */
    public function getImages($limit=null,$https=false)
    {
        $images = [];
        if ($this->hasMultipleImages){
            $count=0;
            foreach ($this->searchMediaAssociation()->data as $key => $image){
                $count++;
                if ($limit==null || $limit >= $count)
                    $images[] = $image->getUrl($https);
            }
        }
        else {
            $image = $this->loadMediaAssociationModel($this->getOwner()->image);
            if ($image!=null)
                $images[] = $image->getUrl($https);
        }
        return $images;
    }  
    
    public function getImagesCount()
    {
        return $this->searchMediaAssociation()->getTotalItemCount(true);
    }    
    
    public function getImageOriginalUrl()
    {
        return $this->getImageUrl(Image::VERSION_ORIGINAL);
    }
    
    public function getImageUrl($version=Image::VERSION_XSMALL,$create=false)
    {
        if ($this->isExternalMedia){
            return $this->getExternalMediaUrl($this->getOwner()->image);
        }
        
        if ($this->getOwner()->image==null){
            if ($create){
                $this->getDefaultImageModel()->createThumbnail($version); 
            }
            return $this->getDefaultImageModel()->getUrl(); 
        }
        
        if ($create){
            $thumbnail = Yii::app()->image->loadModel($this->getOwner()->image)->createThumbnail($version); 
        }
        return Yii::app()->image->getUrl($this->getOwner()->image,$version);
    }
    
    public function getIsExternalMedia()
    {
       $mediaAssoc = $this->loadMediaAssociationModel($this->getOwner()->image);
       if ($mediaAssoc!=null)
           return $mediaAssoc->isExternalImage;
       else 
           return false;
    }
    
    public function getExternalMediaUrl($id)
    {
        $imageUrl = null;
        foreach ($this->searchMediaAssociation()->data as $image) {
            if ($image->id==$id){//external image is primary image
                $imageUrl = $image->getUrl();
                break;//return the correct primary image
            }
        }
        return $imageUrl;
    }    
    
    public function getImageThumbnail($version=Image::VERSION_MEDIUM,$htmlOptions=array('id'=>'model-img'),$alt=null)
    {
        if (!isset($alt))
            $alt = Sii::t('sii','Image');
        
        $media = $this->loadMediaAssociationModel($this->getOwner()->image);
        if ($this->getOwner()->image==null || $media==null){
            return $this->getDefaultImageModel()->render($version,$alt,$htmlOptions);
        }
        elseif ($this->isExternalMedia)
            return CHtml::image($this->getExternalMediaUrl($this->getOwner()->image),$alt,array_merge($htmlOptions,array('width'=>$version.'px')));
        else {
            return $media->render($version,$alt,$htmlOptions);
        }
    }     
    /**
     * Create media record
     * @param type $file
     * @param type $external
     * @return type
     */
    protected function createMedia($file,$external=false)
    {
        if (!$external) {
            logTrace(__METHOD__.' creating local media..');
            return Yii::app()->serviceManager->mediaManager->create(user()->getId(),[
                        'initialFilepath'=>$file['path'],
                        'name'=>$file['name'],
                        'filename'=>$file['filename'],
                        'mime_type'=>$file['mime'],
                        'size'=>$file['size'],
                        'owner'=>$this->getOwner(),
                        'media_group'=>$this->mediaGroup,
                        'check_storage_limit'=>$this->checkStorageLimit,
                    ]);
        }
        else {//external image
            logTrace(__METHOD__.' creating external media..');
            $image = new ImageExternal();//calling it to get its init to auto-populated data (name, mime_type, and size)
            return Yii::app()->serviceManager->mediaManager->create(user()->getId(),[
                        'name'=>$image->name,
                        'mime_type'=>$image->mime_type,
                        'size'=>$image->size,
                        'filename'=>$file['filename'],
                        'src_url'=>$file['url'],
                        'external_media'=>true,
                        'owner'=>$this->getOwner(),
                        'media_group'=>$this->mediaGroup,
                    ]);
        }
    }    
    /**
     * Create media association record and attach to owner()
     * @param CActiveRecord $media
     * @return type
     */
    protected function createMediaAssociationRecord($media)
    {
        logTrace(__METHOD__.' media group',$this->mediaGroup);
        return $media->attachToOwner($this->getOwner(),$this->mediaGroup);
    }        
    /**
     * Transition media record according to owner status
     * @param type $mediaAssoc
     */
    protected function transitionMediaRecord($mediaAssoc)
    {
        if ($this->getOwner()->getAccountOwner()->online() && $mediaAssoc->media->offline()){
            logTrace(__METHOD__.' auto activate media as owner is online...',$mediaAssoc->media->attributes);
            Yii::app()->serviceManager->model = get_class($mediaAssoc->media);
            Yii::app()->serviceManager->transition($this->getOwner()->getAccountOwner()->account_id,$mediaAssoc->media,'activate');
        }
    }    
    /**
     * This image data is used in gridview display
     * @param type $imagePath
     * @param type $defaultImage
     * @param type $imageOwner
     * @return type
     */
    public function getImageData($imagePath,$defaultImage,$imageOwner=null)
    {
        if (!isset($imageOwner))
            $imageOwner = $this->getOwner();
        
        $imageData = array(
            'type'=>'MediaAssociation',
            'imagePath'=>$imagePath,
            'default'=>$defaultImage,
            'id'=>$imageOwner->image==null?$defaultImage:$imageOwner->image,
            'version'=>Image::VERSION_ORIGINAL,
            'htmlOptions'=>array('style'=>'height:100px;padding-right:10px;'),
        );
        if ($imageOwner->isExternalMedia){
            $imageData = array_merge($imageData,array(
                'external'=>true,
                'externalImageUrl'=>$imageOwner->getExternalMediaUrl($imageOwner->image),
            ));
        }  
        return $imageData;
    }    

    public function getDefaultImageModel()
    {
        $original = Yii::app()->image->modelClass;
        Yii::app()->image->modelClass = 'Image';
        $defaultImage = Yii::app()->image->loadModel($this->imageDefault);
        Yii::app()->image->modelClass = $original;//restore back
        //logTrace(__METHOD__.' ',$defaultImage->attributes);
        return $defaultImage;
    }        

}