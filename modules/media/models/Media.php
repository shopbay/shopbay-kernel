<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_media".
 *
 * The followings are the available columns in table 's_media':
 * @property integer $id
 * @property string $account_id
 * @property string $name
 * @property string $filename
 * @property string $src_url
 * @property string $mime_type
 * @property integer $size
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Media extends Downloadable
{
    use ImageTrait;
    public $initialFilepath;//contains the initial uploaded files stored location
    private $_distinctAssocs;//contains the distinct associations
    /**
     * Init model
     */
    public function init()
    {
        $this->downloadRoute = 'media/download';
    }    
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Image the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Media',[$mode]);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_media';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
            ],
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>[
                    'enable'=>true,
                ],
            ],
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::MEDIA_ONLINE,
                'inactiveStatus'=>Process::MEDIA_OFFLINE,
            ],
            'mediaworkflow' => [
                'class'=>'common.services.workflow.behaviors.MediaWorkflowBehavior',
            ]
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name, filename, src_url, mime_type, size, status', 'required'],
            ['size', 'numerical', 'integerOnly'=>true],
            ['account_id', 'length', 'max'=>12],
            ['name, filename, mime_type', 'length', 'max'=>255],
            ['src_url', 'length', 'max'=>2000],
            ['status', 'length', 'max'=>10],
            //API validation scenario
            ['size', 'ruleTotalSize','on'=>'apiValidation'],
            //for delete scenario
            ['filename', 'ruleDelete', 'on'=>'delete'],
            //deactivate scenario
            ['status', 'ruleDeactivation','on'=>'deactivate'],
            
            ['id, account_id, name, filename, src_url, mime_type, size, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Verify the total media size allowed
     */
    public function ruleTotalSize($attribute,$params)
    {
        if (!$this->isExternalImage){
            $service = Feature::patternize(Feature::$hasStorageLimitTierN);
            $message = Subscription::apiHasService($service,[],true);//return error message if any
            if ($message!=$service)
                $this->addError($attribute, $message);
        }
    }     
    /**
     * Verify if media file can be deleted
     */
    public function ruleDelete($attribute,$params)
    {
        if (count($this->associations)>0)
            $this->addError($attribute, Sii::t('sii','Media cannot be deleted now. It is associated with other objects.'));
    }     
    /**
     * Verify if media file can be deactivated
     */
    public function ruleDeactivation($attribute,$params)
    {
        foreach ($this->associations as $assoc) {
            if ($assoc->object->online()){
                $this->addError($attribute, Sii::t('sii','Media cannot be deactivated now. It is currently used by one or more online objects.'));
                break;
            }
        }
    }   
    public function mimeType($compareString) 
    {
        $criteria=new CDbCriteria;
        $criteria->compare('mime_type',$compareString.'/',true);
        $this->getDbCriteria()->mergeWith($criteria);
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    }      
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'associations' => [self::HAS_MANY, 'MediaAssociation', 'media_id'],
        ];
    }
    /**
     * Get distinct associations
     * @return array
     */
    public function getDistinctAssociations()
    {
        if (!isset($this->_distinctAssocs)){
            $command = Yii::app()->db->createCommand()
                            ->select('obj_type, obj_id')
                            ->from(MediaAssociation::model()->tableName())
                            ->where('media_id='.$this->id)
                            ->group('obj_type, obj_id');
            logTrace(__METHOD__.' query command = '.$command->text);
            //query db data
            $this->_distinctAssocs = Yii::app()->db->createCommand($command->text)->queryAll();
            logTrace(__METHOD__.' distinct associations',$this->_distinctAssocs);
        }
        return $this->_distinctAssocs;
    }    
    /**
     * Get distinct associations
     * @return array
     */
    public function getDistinctAssociationObjects()
    {
        $objects = [];
        foreach ($this->distinctAssociations as $data) {
            $type = SActiveRecord::resolveTablename($data['obj_type']);
            $objects[] = $type::model()->findByPk($data['obj_id']);
        }
        return $objects;
    }       
    /**
     * Check if this media is attached to more than one owner
     * @return array
     */
    public function getHasManyAssociations()
    {
        return count($this->distinctAssociations)>1;
    }       
    /**
     * Check if this media is attached to only one owner
     * @return array
     */
    public function getHasSingleAssociation()
    {
        return count($this->distinctAssociations)==1;
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' =>  Sii::t('sii','Account'),
            'name' =>  Sii::t('sii','File Name'),
            'filename' =>  Sii::t('sii','Filename'),
            'src_url' =>  Sii::t('sii','File URL'),
            'mime_type' =>  Sii::t('sii','Mime Type'),
            'size' =>  Sii::t('sii','File size'),
            'status' =>  Sii::t('sii','Status'),
            'create_time' =>  Sii::t('sii','Upload Time'),
            'update_time' =>  Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Create media record
     * @param array $config
     * array (
     *   'account_id'=>'',
     *   'initialFilepath'=>'',//required for move file
     *   'name'=>'',
     *   'filename'=>'',
     *   'mime_type'=>'',
     *   'size'=>'',
     *   'status'=>'',
     *   'move_file'=>'<control field>',
     *   'external_media'=>'<control field>',
     *   'owner'=>'<control field>',
     * )
     * @throws Exception
     */
    public function createRecord($config)
    {
        if (!array_key_exists('status', $config))
            $config['status'] = Process::MEDIA_OFFLINE;
        if (!array_key_exists('move_file', $config))
            $config['move_file'] = true;
        if (!array_key_exists('external_media', $config))
            $config['external_media'] = false;
        //Set scenario if any
        if (isset($config['scenario']))
            $this->setScenario($config['scenario']);
                
        if( $this->save()) {
            logTrace(__METHOD__.' ok',$this->attributes);
            if (!$this->isExternalImage){//assign the actual src_url since now media id is ready
                $this->src_url = $this->assetUrl;
                $this->update();
            }
            if( !$config['external_media'] && $config['move_file'])
                $this->moveFile();
        }
        else {
            logError(__METHOD__." Could not save Media",$this->getErrors());
            $this->addError('name', Sii::t('sii','Could not save Media'));
        }
    }
    /**
     * Move file to user folder (after save, so that filepath can be identified)
     * @see Downloadable
     */
    protected function moveFile()
    {
        if (is_file($this->initialFilepath)){//make sure it is a regular file, and not a directory!
            if (rename( $this->initialFilepath, $this->filepath ) ) {
                chmod( $this->filepath, 0777 );
                logTrace(__METHOD__." file move from $this->initialFilepath to $this->filepath");
            }
        }
    }
    /**
     * Attach media to a model owner
     * @throws Exception
     */
    public function attachToOwner($model,$group=null)
    {
        $mediaAssoc = new MediaAssociation();
        $mediaAssoc->media_id = $this->id;
        $mediaAssoc->obj_id = $model->id;
        $mediaAssoc->obj_type = $model->tableName();
        $mediaAssoc->media_group = isset($group)?$group:get_class($model);
        $mediaAssoc->description = null;//todo fill if required
        if($mediaAssoc->save()){
            logTrace(__METHOD__." attach media to model ".get_class($model).' ok',$mediaAssoc->attributes);
            return $mediaAssoc;
        }
        else {
            logError(__METHOD__." Could not attach media to model ".get_class($model),$mediaAssoc->errors);
            throw new CException( " Could not attach media to model ".get_class($model));
        }
    }    
    /**
     * Delete media file and its db record
     */
    public function deleteFile()
    {
        if (file_exists($this->filepath)){
            $trashDir = $this->fileBasepath.DIRECTORY_SEPARATOR.'trash';
            if (!file_exists($trashDir)){
                mkdir($trashDir);
                logInfo(__METHOD__.' mkdir for trash ',$trashDir);
            }
            $trashFile = $trashDir.DIRECTORY_SEPARATOR.$this->filename;
            rename($this->filepath, $trashFile);//move to trash folder
            //@unlink($this->filepath);//physical delete
            $this->filename = 'trash/'.$this->filename;
            $this->update();
            logInfo(__METHOD__.' moved to trash ok',$trashFile);
        }
        $this->delete();
    }
    /**
     * A wrapper method to return records by $filename of this model
     * @param type $filename
     * @return \Attachment
     */
    public function findFile($filename)
    {
        $this->getDbCriteria()->mergeWith(['condition'=>'filename = \''.$filename.'\'']);
        return $this;
    }

    public function getFileOwner()
    {
        return $this->account_id;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getViewUrl()
    {
        return url('/media/view/'.$this->id);
    }
    /**
     * The media direct access url
     * @param string $domain If domain not set, use merchant domain (slower performance as need to go through access control check)
     * @param boolean $preview if in preview mode
     * @param boolean $secure if use secure connection
     * @see AssetsAction
     * @return type
     */
    public function getAssetUrl($domain=null,$preview=false,$secure=false)
    {
        $route = '/media/assets/';
        if ($preview)
            $route .= 'preview/';
        
        $route .= $this->id.'.jpg';
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,$secure);
        else {
            if ($secure)
                return request()->getHostInfo('https').$route;
            else
                return request()->getHostInfo().$route;
        }
    }
    /**
     * Preview url ensures that media is always return and not subject to the media status
     * @see AssetsAction
     * @param string $domain If domain not set, use cdn domain (must faster as no access control check)
     * @return type
     */
    public function getPreviewUrl($domain=null,$secure=false)
    {
        if (!isset($domain))
            $domain = app()->urlManager->cdnDomain;
        return $this->isExternalImage?$this->src_url:$this->getAssetUrl($domain, true, $secure);
    }
    
    public function getUrl()
    {
        return $this->isExternalImage?$this->src_url:$this->assetUrl;
    }

    public function getPreview($width=420,$height=null)
    {
        if ($this->isExternalImage)//separately check since its mime_type might not be available
            return CHtml::image($this->src_url, $this->name, ['title'=>$this->name,'width'=>$width,'height'=>$height]);
        elseif ($this->isImage)
            return CHtml::image($this->previewUrl, $this->name, ['title'=>$this->name,'width'=>$width,'height'=>$height]);
        elseif ($this->isAudio) {
            return '<audio controls width="'.$width.'">'.
                      CHtml::tag('source',['src'=>$this->previewUrl,'type'=>$this->mime_type]).
                      Sii::t('sii','Your browser does not support the audio tag.').
                   '</audio>';
        }
        elseif ($this->isVideo) {
            return '<video controls heigh="'.$height.'" width="'.$width.'">'.
                      CHtml::tag('source',['src'=>$this->previewUrl,'type'=>$this->mime_type]).
                      Sii::t('sii','Your browser does not support the video tag.').
                   '</video>';
        }
        else
            return Helper::purify(Helper::rightTrim(@file_get_contents($this->filepath),200));
    }
    
    public function getPreviewIcon()
    {
        if ($this->isExternalImage)//separately check since its mime_type might not be available
            return CHtml::image($this->src_url, $this->name, ['title'=>$this->name,'width'=>'auto','height'=>100]);
        elseif ($this->isImage)
            return CHtml::image($this->previewUrl, $this->name, ['title'=>$this->name,'width'=>'auto','height'=>100]);
        elseif ($this->isVideo || $this->isAudio )
            return $this->getPreview(150,100);
        else
            return $this->icon;
    }
    
    public function getIcon()
    {
        if ($this->isImage)
            return '<i class="fa fa-file-image-o fa-fw"></i>';
        elseif ($this->isAudio)
            return '<i class="fa fa-file-audio-o fa-fw"></i>';
        elseif ($this->isVideo)
            return '<i class="fa fa-file-video-o fa-fw"></i>';
        elseif ($this->isText)
            return '<i class="fa fa-file-text-o fa-fw"></i>';
        elseif ($this->isApplication)
            return '<i class="fa fa-file-code-o fa-fw"></i>';
        else 
            return '<i class="fa fa-file-o fa-fw"></i>';
    }
    
    public function getIsAudio()
    {
        return strpos($this->mime_type, 'audio/')===0;
    }
    
    public function getIsVideo()
    {
        return strpos($this->mime_type, 'video/')===0;
    }
    
    public function getIsImage()
    {
        return strpos($this->mime_type, 'image')===0;
    }    
    
    public function getIsText()
    {
        return strpos($this->mime_type, 'text')===0;
    }    

    public function getIsApplication()
    {
        return strpos($this->mime_type, 'application')===0;
    }    
    
    public function getDownloadLink()
    {
        $html = CHtml::link($this->name,$this->downloadUrl,['style'=>'padding: 5px 5px;','title'=>$this->name,'download'=>$this->name]);
        return CHtml::tag('span',[],$html);
    }
    /**
     * Return the total media size consumed by user
     * @param type $user
     * @param type $format If to format size or simply the numeric value
     * @return mixed String (formatted size) or size in numeric value (bytes)
     */
    public static function getTotalSize($user,$format=false)
    {
        $command = Yii::app()->db->createCommand()
                    ->select('sum(size) total')
                    ->from(Media::model()->tableName())
                    ->where('account_id = \''.$user.'\'');
        $result = Yii::app()->db->createCommand($command->text)->queryRow();
        //todo How to include also other database table records?
        return $format ? Helper::formatBytes($result['total']) : ($result['total']!=null?$result['total']:0);
    }    
    
}