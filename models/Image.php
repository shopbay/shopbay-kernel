<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_image".
 *
 * The followings are the available columns in table 's_image':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $name
 * @property string $filename
 * @property string $src_url
 * @property string $mime_type
 * @property integer $size
 * @property integer $create_by
 * @property integer $create_time
 *
 * @author kwlok
 */
class Image extends CActiveRecord
{
    use ImageTrait;
    /*
     * Below are various image versions
     */
    const VERSION_ORIGINAL      = 'original';
    const VERSION_XLARGE        = '640';
    const VERSION_LARGE         = '360';
    const VERSION_SLARGE        = '300';
    const VERSION_XXLMEDIUM     = '250';
    const VERSION_XLMEDIUM      = '200';
    const VERSION_LMEDIUM       = '160';
    const VERSION_MEDIUM        = '120';
    const VERSION_SMEDIUM       = '100';
    const VERSION_SMALL         = '80';
    const VERSION_XSMALL        = '60';
    const VERSION_XXSMALL       = '30';
    const VERSION_XXXSMALL      = '20';
    /*
     * Below ID is matched to preload default images at s_image column "id"
     * @see common.sql
     */
    const DEFAULT_IMAGE                = 0;
    const DEFAULT_IMAGE_PRODUCT        = 1;
    const DEFAULT_IMAGE_CATEOGRY       = 2;
    const DEFAULT_IMAGE_SHOP           = 3;
    const DEFAULT_IMAGE_BRAND          = 4;
    const DEFAULT_IMAGE_ACCOUNT        = 5;
    const DEFAULT_IMAGE_CAMPAIGN_BGA   = 6;
    const DEFAULT_IMAGE_CAMPAIGN_SALE  = 7;
    const DEFAULT_IMAGE_NEWS           = 8;
    /*
     * Image that are reply on external image repositories such as cloud: dropbox, instagram etc
     */
    const EXTERNAL_IMAGE = 'image.external';
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
        return Sii::t('sii','Image|Images',[$mode]);
    }     
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_image';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['obj_type, obj_id, name, filename, src_url, mime_type, size', 'required'],
            ['obj_id, size, create_by, create_time', 'numerical', 'integerOnly'=>true],
            ['obj_type', 'length', 'max'=>20],
            ['name, filename, mime_type', 'length', 'max'=>255],
            ['src_url', 'length', 'max'=>2000],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, obj_type, obj_id, name, filename, src_url, mime_type, size, create_by, create_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'create_by'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'obj_type' => 'Obj Type',
            'obj_id' => 'Obj',
            'name' => 'Name',
            'filename' => 'Filename',
            'src_url' => 'Src Url',
            'mime_type' => 'Mime Type',
            'size' => 'Size',
            'create_by' => 'Create By',
            'create_time' => 'Create Time',
        ];
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('filename',$this->filename,true);
        $criteria->compare('src_url',$this->src_url,true);
        $criteria->compare('mime_type',$this->mime_type,true);
        $criteria->compare('size',$this->size);
        $criteria->compare('create_by',$this->create_by);
        $criteria->compare('create_time',$this->create_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if(parent::beforeSave()){
            if($this->isNewRecord)
            {
                if (!isset($this->create_by))
                    $this->create_by = user()->getId();
                $this->create_time = time();
            }
            return true;
        }
        else
            return false;
    }      

    public function getUrl($forceSecure=false)
    {
        if ($this->isExternalImage)
            return $this->src_url;
        else
            return Yii::app()->image->getBaseUrl($forceSecure).$this->src_url;
    }

}