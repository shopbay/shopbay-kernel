<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.xupload.models.XUploadForm");
/**
 * Description of SUploadForm
 *
 * @author kwlok
 */
class SUploadForm extends XUploadForm 
{
    /**
     * @var mixed a list of file name extensions that are allowed to be uploaded.
     * This can be either an array or a string consisting of file extension names
     * separated by space or comma (e.g. "gif, jpg").
     * Extension names are case-insensitive. Defaults to null, meaning all file name
     * extensions are allowed.
     * @see CFileValidator::types
     */
    public $typesAllowed = null;
    /**
     * @var integer the maximum number of bytes required for the uploaded file.
     * Defaults to null, meaning no limit.
     * Note, the size limit is also affected by 'upload_max_filesize' INI setting
     * and the 'MAX_FILE_SIZE' hidden field value. (Default is 2M).
     * If to support higher, need to update php.ini
     * @see CFileValidator::maxSize
     * @see CFileValidator::tooLarge
     */
    public $maxSizeAllowed = 2000000;//max size 2M = 2000000 bytes
    /**
     * @var mixed a list of MIME-types of the file that are allowed to be uploaded.
     *  This can be either an array or a string consisting of MIME-types separated
     * by space or comma (e.g. "image/gif, image/jpeg"). MIME-types are
     * case-insensitive. Defaults to null, meaning all MIME-types are allowed.
     * @see CFileValidator::mimeTypes
     */
    public $mimeTypesAllowed = null;
    /**
     * The error messsage to display when wrong mime type is presented.
     * @var type 
     */
    public $wrongMimeTypeMessage = null;

    public $uploadWidget = 'common.extensions.supload.SUpload';
    public $uploadView   = 'common.extensions.supload.views.upload';
    public $downloadView = 'common.extensions.supload.views.download';
    public $formView     = 'common.extensions.supload.views.form';
    public $description;//description of upload file; If set to "disabled", the field will not be shown in form
    public $scenarioSkipSecureFileNames = 'skipSecureFileNames';//specific scenario to skip secure filenames changing
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
    } 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        if (!isset($this->wrongMimeTypeMessage))
            $this->wrongMimeTypeMessage = Sii::t('sii','The file "{filename}" cannot be uploaded. Its MIME-type "{mime_type}" is not supported.',['{filename}'=>$this->name,'{mime_type}'=>$this->mime_type]);
        
        return array(
            ['file', 'file','types'=>$this->typesAllowed,'mimeTypes'=>$this->mimeTypesAllowed,'wrongMimeType'=>$this->wrongMimeTypeMessage,'maxSize'=>$this->maxSizeAllowed,'minSize'=>1],//minimum 1 byte
            ['description', 'length', 'max'=>500],
        );
    } 
    /**
     * A stub to allow overrides of thumbnails returned
     */
    public function getThumbnailAssetUrl($publicPath,$assetUrl) 
    {            
        if (substr($this->mime_type, 0, 5)=='image')
             return $publicPath.$this->filename;
        else
            return Helper::getMimeTypeIconUrl($this->mime_type,$assetUrl);
    }
    /**
     * OVERRIDDEN
     * Change our filename to match our own naming convention
     * Need to override this as need extra scenario check to check if to use secureNames
     * @return bool
     */
    public function beforeValidate() 
    {
        //(optional) Generate a random name for our file to work on preventing
        // malicious users from determining / deleting other users' files
        if($this->secureFileNames) {
            if ($this->getScenario()!=$this->scenarioSkipSecureFileNames){
                $this->filename = sha1( Yii::app( )->user->id.microtime().$this->name.rand(1, 1000));
                $this->filename .= ".".$this->file->getExtensionName( );
            }
        }
        //below are extract directly from CModel::beforeValidate()
        //here skip XUploadForm::beforeValidate() as it does not support additional scenario checks
        $event=new CModelEvent($this);
        $this->onBeforeValidate($event);
        return $event->isValid;
    }
    
}