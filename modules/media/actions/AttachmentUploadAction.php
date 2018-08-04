<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.supload.actions.SUploadAction");
/**
 * Customization
 * =============
 * [1] Support additional upload attribute 'group, obj_type, obj_id' specific to model Attachment
 * @author lok
 */
class AttachmentUploadAction extends SUploadAction 
{
    /**
     * Name of the model attribute used to store the Attachment specific info.
     * Refer to Model Attachment
     * @var string
     */
    public $groupAttribute = 'group';
    public $objTypeAttribute = 'obj_type';
    public $objIdAttribute = 'obj_id';
    
    public function init( ) 
    {
        parent::init();
        $attachmentForm = (object)$_POST[get_class($this->formModel)];
        $this->formModel->{$this->groupAttribute} = $attachmentForm->group;
        $this->formModel->{$this->objTypeAttribute} = $attachmentForm->obj_type;
        $this->formModel->{$this->objIdAttribute} = $attachmentForm->obj_id;
    }
    /**
     * The main action that handles the file upload request.
     */
    public function run() 
    {
	parent::run();
    }

    protected function mapAttributes($model) 
    {
        $model->{$this->mimeTypeAttribute} = $model->{$this->fileAttribute}->getType();
        $model->{$this->sizeAttribute} = $model->{$this->fileAttribute}->getSize();
        $model->{$this->displayNameAttribute} = $model->{$this->fileAttribute}->getName();
        $model->{$this->fileNameAttribute} = $model->{$this->displayNameAttribute};
        $model->{$this->descriptionAttribute} = $model->{$this->descriptionAttribute};
        $model->{$this->groupAttribute} = $model->{$this->groupAttribute};
        $model->{$this->objTypeAttribute} = $model->{$this->objTypeAttribute};
        $model->{$this->objIdAttribute} = $model->{$this->objIdAttribute};
    }
    /**
     * We store info in session to make sure we only delete files we intended to
     * Other code can override this though to do other things with state, thumbnail generation, etc.
     * @return boolean|string Returns a boolean unless there is an error, in which case it returns the error message
     */
    protected function beforeReturnByModel($model) 
    {
        $path = $this->getPath();
        // Now we need to save our file info to the user's session
        $userFiles = SActiveSession::get($this->stateVariable);
        
        $userFiles[$model->{$this->fileNameAttribute}] = array(
            "path" => $path.$model->{$this->fileNameAttribute},
            //the same file or a thumb version that you generated
            "thumb" => $path.$model->{$this->fileNameAttribute},
            "filename" => $model->{$this->fileNameAttribute},
            'size' => $model->{$this->sizeAttribute},
            'mime' => $model->{$this->mimeTypeAttribute},
            'name' => $model->{$this->displayNameAttribute},
            'description' => $model->{$this->descriptionAttribute},
            'group' => $model->{$this->groupAttribute},
            'obj_type' => $model->{$this->objTypeAttribute},
            'obj_id' => $model->{$this->objIdAttribute},
        );
        SActiveSession::set($this->stateVariable, $userFiles);

        return true;
    }
}
