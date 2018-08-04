<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_media_association".
 *
 * The followings are the available columns in table 's_media_association':
 * @property integer $id
 * @property integer $media_id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $media_group
 * @property string $description
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class MediaAssociation extends CActiveRecord
{
    use ImageTrait;
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
        return Sii::t('sii','Media',array($mode));
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_media_association';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['media_id, obj_id, obj_type', 'required'],
            ['media_id, obj_id', 'numerical', 'integerOnly'=>true],
            ['obj_type', 'length', 'max'=>20],
            ['media_group', 'length', 'max'=>50],
            ['description', 'length', 'max'=>500],
            
            ['id, media_id, obj_id, obj_type, media_group, description, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'media' => [self::BELONGS_TO, 'Media', 'media_id'],
        ];
    }
    /**
     * Return the association object
     * @return class
     */
    public function getObject()
    {
        $type = SActiveRecord::resolveTablename($this->obj_type);
        return $type::model()->findByPk($this->obj_id);
    }  
    /**
     * Association finder 
     * @param $obj_type
     * @param $obj_id
     * @param $media_group
     * @return CComponent
     */
    public function searchAssociation($obj_type,$obj_id,$media_group=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>$obj_type]);
        $criteria->addColumnCondition(['obj_id'=>$obj_id]);
        if (isset($media_group))
            $criteria->addColumnCondition(['media_group'=>$media_group]);
        return new CActiveDataProvider(MediaAssociation::model(),[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>100],//put to higher number to avoid pagination
        ]);
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'media_id' =>  Sii::t('sii','Media'),
            'obj_id' =>  Sii::t('sii','Object ID'),
            'obj_type' =>  Sii::t('sii','Objet Type'),
            'media_group' =>  Sii::t('sii','Media Group'),
            'description' =>  Sii::t('sii','Description'),
            'create_time' =>  Sii::t('sii','Upload Time'),
            'update_time' =>  Sii::t('sii','Update Time'),
        );
    }
    /**
     * Note: this preview use cdn path https://cdn-domain/media/assets/preview/.... 
     * as it is much faster to access image (without any access checks, its publicly accessible)
     * Using merchant path is slow as before getting the media, it needs to go through all access control, subscripton checks. 
     * E.g. merchant path https://merchant-domain/media/assets/preview/....
     * 
     * @param type $secure
     * @return type
     */
    public function getUrl($secure=false)
    {
        return $this->media->getPreviewUrl(app()->urlManager->cdnDomain,$secure||request()->isSecureConnection);//always return image (not subject to media status) so need to use previewUrl
    }
    
    public function getName()
    {
        return $this->media->name;
    }    

    public function getSize()
    {
        return $this->media->size;
    }    
    
    public function getMime_type()
    {
        return $this->media->mime_type;
    }    
    
    public function getSrc_url()
    {
        return $this->media->src_url;
    }    
    
    public function getFilename()
    {
        return $this->media->filename;
    }    
    
    public function getFilepath()
    {
        return $this->media->filepath;
    }    
    
}