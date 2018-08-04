<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MediaStorage
 *
 * @author kwlok
 */
class MediaStorage extends CApplicationComponent
{
    /**
     * Init
     */
    public function init()
    {
        parent::init();
    }  
    /**
     * The media base path. The default mount point is stored at table s_config
     * 
     * System should support multiple storage locations and assign one (fixed one) to each user 
     * Each storage location can be a mount point. This easily extend the file storage
     * @see shopbay.json for multiple storage mount point
     * @return type
     */
    public function getBasepath($user)
    {
        $path = readConfig('storage', 'media');
        $mountPoint = ConfigAccount::getSetting($user,ConfigAccount::MEDIA_MOUNT_POINT);
        return $path[$mountPoint];
    }
    /**
     * Return media base path
     * @param type $user the media owner id
     * @return string
     */
    public function getMediaPath($user)
    {
        $path = $this->getBasepath($user).DIRECTORY_SEPARATOR.md5($user);
        if (!file_exists($path)){
            logInfo(__METHOD__.' mkdir '.$path);
            mkdir($path);
        }
        return $path;
    }    
}
