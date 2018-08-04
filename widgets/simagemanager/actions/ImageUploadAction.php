<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.xupload.actions.XUploadAction");
/**
 * Description of ImageUploadAction
 *
 * @author kwlok
 */
class ImageUploadAction extends XUploadAction 
{
    /*
     * If support multiple images; It will affect the way how uploaded images will be stored into session.
     * Default to true; Each uploaded image will be kept adding to session
     * If false (single image), session only stores one image and overwrite previous one
     */
    public $multipleImages = true;
    /**
     * Default upload limit
     * @var integer 
     */
    public $uploadLimit;
    /**
     * SingleImageForm (or subclass of it) to be used.  Defaults to SingleImageForm
     * @see XUploadAction::init()
     * @var string
     */
    public $formClass = 'common.widgets.simagemanager.models.SingleImageForm';
    /**
     * Name of the model attribute used to store session state variable.
     * Defaults to 'stateVariable'
     * @since 0.5
     */
    public $stateVariableAttribute = 'stateVariable';

    public function init( ) 
    {
        parent::init();
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
                $images = SActiveSession::get($this->stateVariable);

                if (isset($images[$_GET["file"]]) && ($this->fileExists($images[$_GET["file"]]) || $this->externalImageExists($images[$_GET["file"]]))) {
                    
                    if (empty($images[$_GET["file"]]['id'])){//for newly create and not yet saved into db
                        logTrace(__METHOD__.' file deleted from directory',$images[$_GET["file"]]);
                        $this->deleteFile($images[$_GET["file"]]);//physcial delete 
                    }
                    logTrace(__METHOD__.' file removed from session='.$this->stateVariable,$images[$_GET["file"]]);
                    unset($images[$_GET["file"]]); // remove it from our session and save that info
                    SActiveSession::set($this->stateVariable, $images);
                    logTrace(__METHOD__.' remaining session files '.$this->stateVariable, SActiveSession::get($this->stateVariable));
                    $success = true;
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
        $this->_loadParent($model);
        if (isset($this->uploadLimit))
            $model->uploadLimit = $this->uploadLimit;
        $model->{$this->fileAttribute} = CUploadedFile::getInstance($model, $this->fileAttribute);
        if ($model->{$this->fileAttribute} !== null) {
            
            $this->mapAttributes($model);
					
            if ($model->validate()) {

                $path = $this->getPath();

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                    chmod($path, 0777);
                }

                $model->{$this->fileAttribute}->saveAs($path . $model->{$this->fileNameAttribute});
                chmod($path . $model->{$this->fileNameAttribute}, 0777);

                $returnValue = $this->beforeReturn();
				
                if ($returnValue === true) {
                    echo $this->returnFilejson($this->returnAttributes($model));                    
                } else {
                    Yii::log(__METHOD__.' '.$returnValue, CLogger::LEVEL_ERROR);
                    echo $this->returnFilejson(array(array("error" => $returnValue,)));
                }
            } 
            else {
                Yii::log(__METHOD__.' '. CVarDumper::dumpAsString($model->getErrors()), CLogger::LEVEL_ERROR);
                echo $this->returnFilejson(array(array("error" => Yii::app()->getController()->getFlashAsString('error',$model->getErrorMessage(),null))));
            }
        } 
        else 
            throw new CHttpException(500, "Could not upload file");
    }
    
    private function _loadParent($model) 
    {
        logTrace('$_GET',$_GET);
        if (isset($_GET['ptype']) && isset($_GET['pid']))
            $model->parent = $_GET['ptype']::model()->findbyPk($_GET['pid']);
    }

    protected function mapAttributes($model) 
    {
        $model->{$this->mimeTypeAttribute} = $model->{$this->fileAttribute}->getType();
        $model->{$this->sizeAttribute} = $model->{$this->fileAttribute}->getSize();
        $model->{$this->displayNameAttribute} = $model->{$this->fileAttribute}->getName();
        $model->{$this->fileNameAttribute} = $model->{$this->displayNameAttribute};
        $model->{$this->stateVariableAttribute} = $this->stateVariable;
    }
    
    protected function returnFilejson($result)
    {
        $filejson = new stdClass();
        $filejson->files = $result;
        return json_encode($filejson);        
    }
    
    protected function returnAttributes($model)
    {
        $attr = new CList();
        $repo = SActiveSession::get($this->stateVariable);
        unset($repo[$model->{$this->fileNameAttribute}]['path']);//not to expose this info
        unset($repo[$model->{$this->fileNameAttribute}]['id']);//not to expose ths info  
        logTrace(__METHOD__.' repo',$repo[$model->{$this->fileNameAttribute}]);
        $attr->add($repo[$model->{$this->fileNameAttribute}]);
        return $attr->toArray();
    }
    /**
     * Now we need to save our file info to the user's session
     * @return boolean
     */
    protected function beforeReturn() 
    {
        if (get_class($this->formModel)=='SingleImageForm') {//single image model, always overwrite previous one
            logTrace(__METHOD__.' previous session files cleared', SActiveSession::get($this->stateVariable));
            SActiveSession::clear($this->stateVariable);
        }
        
        $userFiles = $this->formModel->updateRepository(
                        SActiveSession::get($this->stateVariable),
                        $this->getPath(),
                        $this->getFileUrl($this->formModel->{$this->fileNameAttribute}),
                        $this->formModel               
                    );
            
        SActiveSession::set($this->stateVariable, $userFiles);
        logTrace(__METHOD__.' uploaded files saved into session = '.$this->stateVariable,  SActiveSession::get($this->stateVariable));
        return true;
    }    
    /**
     * Check if external image exists
     * @param $file
     * @return bool
     */
    protected function externalImageExists($file) 
    {
        return isset( $file['name'] ) && $file['name']==Image::EXTERNAL_IMAGE;
    }    
}