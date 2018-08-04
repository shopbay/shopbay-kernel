<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.xupload.actions.XUploadAction");
/**
 * Customization
 * =============
 * [1] Make upload file more secure through HTTP METHOD POST ONLY, else throw 403 error
 * [2] Make delete file more secure through HTTP METHOD DELETE ONLY, else throw 403 error
 * [3] Support additional upload attribute 'description'
 * [4] Intro mapAttributes method
 * @author lok
 */
class SUploadAction extends XUploadAction 
{
    /**
     * Name of the model attribute used to store the file description (optional).
     * Defaults to 'description', the default value in SUploadForm
     * @var string
     */
    public $descriptionAttribute = 'description';
    /**
     * Whether to delete the temporary file after saving.
     * If true, you will not be able to save the uploaded file again in the current request.
     * @var type 
     */
    public $deleteUploadTempFile = true;
    
    public function init( ) 
    {
        parent::init();
        //logTrace(get_class($this->formModel));
        $form = (object)$_POST[get_class($this->formModel)];
        $this->formModel->{$this->descriptionAttribute} = $form->description;
    }	
    /**
     * The main action that handles the file upload request.
     */
    public function run() 
    {
        if (Yii::app()->request->getIsDeleteRequest()){
            $this->sendHeaders();
            if (!$this->handleDeleting())
                throw new CHttpException(403,'Unauthorized Access');
        }
        else if (Yii::app()->request->getIsPostRequest()){
            $this->sendHeaders();
            $this->handleUploading();
        }
        else 
            throw new CHttpException(403,'Unauthorized Access');
    }
    /**
     * Removes temporary file from its directory and from the session
     *
     * @return bool Whether deleting was meant by request
     */
    protected function handleDeleting()
    {
	if (isset($_GET["_method"]) && $_GET["_method"] == "delete") {
            $success = false;
            if ($_GET["file"][0] !== '.' && SActiveSession::exists($this->stateVariable)) {
                // pull our userFiles array out of state and only allow them to delete
                // files from within that array
                $userFiles = SActiveSession::get($this->stateVariable);

		logTrace('file to delete',$userFiles[$_GET["file"]]);
					
                if ($this->fileExists($userFiles[$_GET["file"]])) {
                    $success = $this->deleteFile($userFiles[$_GET["file"]]);
                    logTrace('file deleted ok');
                    if ($success) {
                        logTrace('file removed from session='.$this->stateVariable,$userFiles[$_GET["file"]]);
                        unset($userFiles[$_GET["file"]]); // remove it from our session and save that info
                        SActiveSession::set($this->stateVariable, $userFiles);
                        logTrace('remaining session files '.$this->stateVariable, SActiveSession::get($this->stateVariable));
                    }
                }
            }
            echo json_encode($success);
            return true;
        }
        return false;
    }
    /**
     * Uploads file to temporary directory
     *
     * @throws CHttpException
     */
    protected function handleUploading()
    {
        $this->init();
        $model = $this->formModel;
        $model->{$this->fileAttribute} = CUploadedFile::getInstance($model, $this->fileAttribute);
        if ($model->{$this->fileAttribute} !== null) {
            
            $this->mapAttributes($model);

            if ($model->validate()) {

                $path = $this->getPath();

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                    chmod($path, 0777);
                }

                $model->{$this->fileAttribute}->saveAs($path . $model->{$this->fileNameAttribute}, $this->deleteUploadTempFile);
                chmod($path . $model->{$this->fileNameAttribute}, 0777);

                $returnValue = $this->beforeReturnByModel($model);
				
                logTrace(__METHOD__.' uploaded files session='.$this->stateVariable,  SActiveSession::get($this->stateVariable));
                
                if ($returnValue === true) {
                    echo $this->returnFilejson($this->returnAttributes($model));          
                } else {
                    if ($returnValue instanceof $model){
                        //second level model validation (at beforeReturn)
                        echo $this->returnFilejson($this->returnErrors($returnValue));
                    }
                    else {
                        //original error handling by XUploadAction
                        Yii::log(__METHOD__.' '.$returnValue, CLogger::LEVEL_ERROR);
                        echo $this->returnFilejson(array(array("error" => $returnValue,)));
                    }
                }
            } 
            else {
                echo $this->returnFilejson($this->returnErrors($model));
            }
        } 
        else {
            logError(__METHOD__.' error',$model->getAttributes());
            throw new CHttpException(500, "Could not upload file");
        }
    }
    
    protected function mapAttributes($model) 
    {
        $model->{$this->mimeTypeAttribute} = $model->{$this->fileAttribute}->getType();
        $model->{$this->sizeAttribute} = $model->{$this->fileAttribute}->getSize();
        $model->{$this->displayNameAttribute} = $model->{$this->fileAttribute}->getName();
        $model->{$this->fileNameAttribute} = $model->{$this->displayNameAttribute};
        $model->{$this->descriptionAttribute} = $model->{$this->descriptionAttribute};
    }
    
    protected function returnAttributes($model) 
    {
        return array(
            array(
                "name" => $model->{$this->displayNameAttribute},
                "type" => $model->{$this->mimeTypeAttribute},
                "size" => $model->{$this->sizeAttribute},
                "url" => $this->getFileUrl($model->{$this->fileNameAttribute}),
                "thumbnail_url" => $model->getThumbnailAssetUrl($this->getPublicPath(),$this->getController()->getAssetsURL('common.assets.images')),
                'delete_url' => Yii::app()->getController()->createUrl($this->getId().'?_method=delete&file='.$model->{$this->fileNameAttribute}),
//                "delete_url" => $this->getController()->createUrl($this->getId(), array(
//                    "_method" => "delete",
//                    "file" => $model->{$this->fileNameAttribute},
//                )),
                "delete_type" => "DELETE",
                "description" => $model->{$this->descriptionAttribute},
        ));
    }
    /**
     * We store info in session to make sure we only delete files we intended to
     * Other code can override this though to do other things with state, thumbnail generation, etc.
     * @since 0.5
     * @author acorncom
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
        );
 
        SActiveSession::set($this->stateVariable, $userFiles);

        return true;
    }    
    
    protected function returnFilejson($result)
    {
        $filejson = new stdClass();
        $filejson->files = $result;
        return json_encode($filejson);        
    }
    
    protected function returnErrors($model)
    {
        $flash = $this->getController()->getFlashAsString(
                    'error',
                    Helper::htmlErrors($model->getErrors()),
                    Sii::t('sii','Upload Error'));
        logError(__METHOD__.' validation error',$model->getErrors());
        return array(array(
            'error' => Sii::t('sii','Upload Error'),
            'errorFlash' => $flash,
        ));        
    }
}
