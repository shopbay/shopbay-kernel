<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.media.behaviors.BaseMediaBehavior");
/**
 * Description of MultipleMediaBehavior
 *
 * @author kwlok
 */
class MultipleMediaBehavior extends BaseMediaBehavior 
{
    /**
     * Turn on if to have primary image flagging
     * @var boolean 
     */
    public $primaryFlag = true;
    /**
     * Turn on if to detach media when session has no media (user has clear it)
     * @var boolean 
     */
    public $detachMediaWhenSessionIsEmpty = false;
    /**
    * @var boolean When true, after save image saving by session variable will be skipped; Default to "false"
    */
    public $skipAfterSave = false;
    /**
     * afterSave
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function afterSave($event)
    {
        if (!$this->skipAfterSave){
            if (SActiveSession::exists($this->stateVariable)) {
            
                $sessionMedia = SActiveSession::get($this->stateVariable);
                $keptMediaAssoc = new CList();
                foreach($sessionMedia as $file){
                    logTrace(__METHOD__.' session file data',$file);
                    if (isset($file['name']) && isset($file['filename']) && $file['name']==Image::EXTERNAL_IMAGE){
                        logTrace(__METHOD__.' adding external media file '.$file['filename']);
                        $this->_addMedia($keptMediaAssoc, $file, true);
                    }
                    elseif (is_file( $file["path"])) {
                        $this->_addMedia($keptMediaAssoc, $file);
                    } else {
                        logWarning(__METHOD__.' '.$file["path"]." is not a file");
                    }
                }
                //clean away unwanted media in db
                $this->_clearUnwantedMedia($keptMediaAssoc);  
    
                if ($this->transitionMedia)
                    $this->_transitionMedia($keptMediaAssoc);  

                //clear session media
                SActiveSession::clear($this->stateVariable);
            }
            else {
                logTrace(__METHOD__.' session file does not exists',$this->stateVariable);
                if ($this->detachMediaWhenSessionIsEmpty){
                    logTrace(__METHOD__.' detaching session media.. '.$this->stateVariable);
                    $this->detachMediaAssociation();//detach media file when nothing is in session
                }
            }
        }
        else
            logTrace(__METHOD__.' skipped.',$this->stateVariable);
    }

    private function _addMedia($keptMediaAssoc, $file, $external=false)
    {
        //Note: not catching any exception, let it fails and throw errors at caller side..
        //mainly for media storage check!
        if (!empty($file['id'])){
            $media = $this->loadMediaModel($file['id']);
            if ($media!=null){
                $mediaAssoc = $this->getMediaAssocation($media);
                if ($mediaAssoc!=null){//already inside DB, so keep
                    logTrace(__METHOD__.' already inside DB, so keep '.$file['id']);
                    $keptMediaAssoc->add($this->getMediaAssocation($media));
                }
                else //create association for newly added media from existing media gallery
                    $keptMediaAssoc->add($this->createMediaAssociationRecord($media));
            }
            else //image id not found; UNLIKELY TO HAPPEN UNLESS GOT SYSTEM BUG?
                $keptMediaAssoc->add($this->createMedia($file,$external));
        }
        else {
            if ($external){
                //create external image record
                $keptMediaAssoc->add($this->createMedia($file,true));
            }
            else //new added image (local image)
                $keptMediaAssoc->add($this->createMedia($file));        
        }
    }

    private function _clearUnwantedMedia($keptMediaAssoc)
    {
        $mediaAssocIds = [];
        foreach ($keptMediaAssoc as $assoc) {
            $mediaAssocIds[] = $assoc->id;
        }
        logTrace(__METHOD__.' keep list:',$mediaAssocIds);
        $criteria = new CDbCriteria();
        $criteria->addNotInCondition('id',$mediaAssocIds);
        $this->detachMediaAssociation($criteria);
    }
    
    private function _transitionMedia($keptMediaAssoc)
    {
        foreach ($keptMediaAssoc as $assoc) {
            $this->transitionMediaRecord($assoc);
        }
    }
    
    public function loadSessionMedia() 
    {
        $sessionMedia = SActiveSession::get($this->stateVariable);
        //load from DB
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$this->getOwner()->tableName()));
        $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
        if (isset($this->mediaGroup))
            $criteria->addColumnCondition(array('media_group'=>$this->mediaGroup));
        foreach (MediaAssociation::model()->findAll($criteria) as $mediaAssoc){
            $sessionMedia = SessionMedia::updateRepository(
                    $sessionMedia,
                    $mediaAssoc->getUrl(),
                    $mediaAssoc->media,               
                    $this->_findPrimaryMedia($mediaAssoc)
                );
        }
        SActiveSession::set($this->stateVariable,$sessionMedia);
        return SActiveSession::get($this->stateVariable);
    }    
    /**
     * Find the primary media
     * @param MediaAssociation $mediaAssoc
     * @return boolean
     */
    private function _findPrimaryMedia($mediaAssoc)
    {
        if ($this->primaryFlag && $this->getOwner()->image==$mediaAssoc->id){
            //for owner has attribue "image", e.g. product
            return true;
        }
        else
            return false;
    }
    
    public function disableMediaAfterSave()
    {
        $this->skipAfterSave = true;
    }
}