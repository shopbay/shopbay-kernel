<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.supload.models.SUploadForm");
/**
 * Description of AttachmentForm
 * 
 * [1] Support additional upload attribute 'group, obj_type, obj_id' specific to model Attachment
 * 
 * @author lok
 */
class AttachmentForm extends SUploadForm
{
    public $obj_type;//object type that attachments belongs to
    public $obj_id;//object id
    public $group;//attachments grouping per object
    public $description;//description of attachment; If set to "disabled", the field will not be shown in form
    /**
     * Configurable parameters
     */
    public $uploadRoute;
    public $disableDescription=false;//If set to "true", the field will not be shown in form
    /**
     * Various views required parameters
     */
    public $uploadView   = 'common.modules.media.views.attachment.upload';
    public $formView     = 'common.modules.media.views.attachment.form';
    public $formClass    = 'upload-form';
    public $fileAttribute= 'file';
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
        $this->maxSizeAllowed = Config::getSystemSetting('media_max_size');
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('obj_id, size', 'numerical', 'integerOnly'=>true),
            array('obj_type', 'length', 'max'=>20),
            array('group', 'length', 'max'=>50),
        ));
    }
    
    public function getUploadButtonText($multiple)
    {
        return Sii::t('sii','1#<i class="fa fa-upload"></i> Upload Attachment|0#<i class="fa fa-upload"></i> Choose Attachment', $multiple);
    }
    
    public function getGalleryTableStyle()
    {
        return 'style="width:380px"';
    }
    
    public function getHiddenFields()
    {
        $hiddenFields = CHtml::activeHiddenField($this, 'group');
        $hiddenFields .= CHtml::activeHiddenField($this, 'obj_type');
        $hiddenFields .= CHtml::activeHiddenField($this, 'obj_id');
        return $hiddenFields;
    }
    
}