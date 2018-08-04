<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.workflow.models.Transitionable');
/**
 * Description of Downloadable
 *
 * @author kwlok
 */
abstract class Downloadable extends Transitionable 
{
    protected $downloadRoute = 'file/download';
    
    abstract public function findFile($filename);//model finder method
    abstract public function getFilename();
    abstract public function getFileOwner();
    
    public function getDownloadUrl()
    {
        return url($this->downloadRoute.'/'.$this->filename);
    }
    
    public function getFileBasepath()
    { 
        return Yii::app()->serviceManager->mediaStorage->getMediaPath($this->fileOwner);
    }
    
    public function getFilepath()
    {
        return $this->fileBasepath.DIRECTORY_SEPARATOR.$this->filename;
    }
    
}
