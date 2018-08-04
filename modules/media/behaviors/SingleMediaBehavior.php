<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.media.behaviors.BaseMediaBehavior");
/**
 * Description of SingleMediaBehavior
 *
 * @author kwlok
 */
class SingleMediaBehavior extends BaseMediaBehavior 
{
    /**
     * The custom method to set image owner; Default to false;
     * When false, image owner is set at attribute 'image'.
     * @see self::_setMediaOwner()
     * @var boolean 
     */
    public $setMediaOwnerMethod = false;
    /**
     * afterSave
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function afterSave($event)
    {
        if (SActiveSession::exists($this->stateVariable) ) {
            
            $sessionMedia = SActiveSession::get($this->stateVariable);
            logTrace(__METHOD__.' $sessionMedia size='.count($sessionMedia));

            foreach($sessionMedia as $file){

                logTrace(__METHOD__.' session file data',$file);
                //Note: not catching any exception, let it fails and throw errors at caller side..
                //mainly for media storage check!
                if (isset($file['name']) && isset($file['filename']) && $file['name']==Image::EXTERNAL_IMAGE){
                     $this->_createMediaAssociation($file,true);
                }
                elseif (is_file($file["path"])) {
                    $this->_createMediaAssociation($file);
                } 
                else {
                    logWarning(__METHOD__.' '.$file["path"]." is not a file");
                }
                
                break;//expect one file only
            }
            //clear session media
            SActiveSession::clear($this->stateVariable);
        }
        else
            logTrace(__METHOD__.' session file does not exists',$this->stateVariable);
        
    }
        
    private function _createMediaAssociation($file,$external=false)
    {
        $this->detachMediaAssociation();
        if (!empty($file['id'])){
            $media = $this->loadMediaModel($file['id']);
            if ($media!=null){
                //create association for newly added media from existing media gallery
                $newMediaAssoc = $this->createMediaAssociationRecord($media);
            }
        }
        else {
            $newMediaAssoc = $this->createMedia($file,$external);
        }
        $this->_setMediaOwner($newMediaAssoc->id);
        if ($this->transitionMedia)
            $this->transitionMediaRecord($newMediaAssoc);        
    }
    
    private function _setMediaOwner($id)
    {
        logTrace(__METHOD__.' media assoc='.$id.', owner.id='.$this->getOwner()->id);
        if ($this->setMediaOwnerMethod==false){
            //this method is used by bypass 'owner.afterSave()' event
            $updatedRow = $this->getOwner()->updateByPk($this->getOwner()->id,array('image'=>$id));
            logTrace(__METHOD__.' ok updated row '.$updatedRow, $this->getOwner()->attributes);
        }
        else {
            $this->getOwner()->{$this->setMediaOwnerMethod}($id);
        }
    }

}