<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.supload.models.SUploadForm");
/**
 * Description of MediaUploadForm
 *
 * @author kwlok
 */
class MediaUploadForm extends SUploadForm
{
    /**
     * Various views required parameters
     */
    public $formView     = 'common.modules.media.views.upload.form';
    public $downloadView = 'common.modules.media.views.upload.download';
    public $formClass    = 'upload-form';
    public $fileAttribute= 'file';
    public $uploadRoute;
    public $disableDescription = true;//If set to "true", the field will not be shown in form
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
        $this->mimeTypesAllowed = Config::getSystemSetting('media_mime_type');
        $this->maxSizeAllowed = Config::getSystemSetting('media_max_size');
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['size', 'numerical', 'integerOnly'=>true],
        ]);
    }
    
    public function getUploadButtonText($multiple)
    {
        return SButtonColumn::getButtonIcon('import').' '.Sii::t('sii','1#Click here to upload files|0#Click here to upload file', $multiple);
    }
    /**
     * @return \CJavaScriptExpression bind onClick event to delete button
     */
    public function getDeleteButtonScript()
    {
        return 'enabledelete_multimediafiles(".files .btn.btn-danger");';
    }
    
}