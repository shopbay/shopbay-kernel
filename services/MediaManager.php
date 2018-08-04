<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of MediaManager
 *
 * @author kwlok
 */
class MediaManager extends ServiceManager
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param array $config
     * array (
     *   'account_id'=>'',
     *   'initialFilepath'=>'',
     *   'name'=>'',
     *   'filename'=>'',
     *   'mime_type'=>'',
     *   'size'=>'',
     *   'status'=>'',
     *   'move_file'=>'<control field>',
     *   'external_media'=>'<control field>',
     *   'owner'=>'<control field>',
     * )
     * @throws CException
     */
    public function create($user,$config)
    {
        if (!array_key_exists('status', $config))
            $config['status'] = Process::MEDIA_OFFLINE;
        if (!array_key_exists('account_id', $config))
            $config['account_id'] = $user;
        if (!array_key_exists('owner', $config))
            $config['owner'] = false;
        
        $media = new Media();
        $mediaAttributes = ['initialFilepath','name','filename','mime_type','size','account_id','status','src_url'];
        foreach ($config as $key => $value) {
            if (in_array($key, $mediaAttributes))
                $media->$key = $value;
        }

        if (!isset($media->src_url))
            $media->src_url = 'to-be-assigned';//this will be assigned inside $media->createRecord()

        $this->validate($user, $media, false);
        
        $validateScenario = 'apiValidation';//Default to validate media storage size via Api
        
        if (isset($config['check_storage_limit']) && !$config['check_storage_limit'])
            $validateScenario = 'skipApiValidation';//skip check if set to false
        
        $media = $this->execute($media, [
            'createRecord'=>array_merge($config,['scenario'=>'skipValidation']),//calling $this->execute already done one round of validation via scenario 'apiValidation'
            'recordActivity'=>[
                'event'=>Activity::EVENT_CREATE,
                'account'=>$user,
            ],
        ],$validateScenario);
        logInfo(__METHOD__.' ok');
        
        if (isset($config['owner']) && $config['owner']!=false){
            $mediaAssoc = $media->attachToOwner($config['owner'],isset($config['media_group'])?$config['media_group']:null);
            logInfo(__METHOD__.' attachToOwner ok');
            return $mediaAssoc;
        }
        else
            return $media;
    }
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, [
            'recordActivity'=>[
                'event'=>Activity::EVENT_DELETE,
                'account'=>$user,
            ],
            'deleteFile'=>self::EMPTY_PARAMS,
        ],'delete');
    }

}
