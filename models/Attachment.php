<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_attachment".
 *
 * The followings are the available columns in table 's_attachment':
 * @property integer $id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $name
 * @property string $group Attachments grouping per object
 * @property string $description
 * @property string $filename
 * @property string $src_url
 * @property string $mime_type
 * @property integer $size
 * @property integer $create_by
 * @property integer $create_time
 * 
 * @author kwlok
 */
class Attachment extends Downloadable
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Attachment the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_attachment';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'create_by',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'create_by',
            ],
        ];
    }     
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('obj_type, obj_id, group, name, filename, src_url, mime_type, size', 'required'),
            array('obj_id, size', 'numerical', 'integerOnly'=>true),
            array('create_by', 'length', 'max'=>12),
            array('obj_type', 'length', 'max'=>20),
            array('group', 'length', 'max'=>50),
            array('description', 'length', 'max'=>500),
            array('name, filename, src_url, mime_type', 'length', 'max'=>255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, obj_type, obj_id, group, name, description, filename, src_url, mime_type, size, create_by, create_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'obj_type' => 'Obj Type',
            'obj_id' => 'Obj',
            'group' => Sii::t('sii','Process'),
            'name' => Sii::t('sii','Name'),
            'description' => Sii::t('sii','Description'),
            'filename' => Sii::t('sii','Filename'),
            'src_url' => Sii::t('sii','Url'),
            'mime_type' => Sii::t('sii','Mime Type'),
            'size' => Sii::t('sii','Byte Size'),
            'create_by' => Sii::t('sii','Uploaded By'),
            'create_time' => Sii::t('sii','Upload Time'),
        );
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
        $criteria->compare('group',$this->group,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('filename',$this->filename,true);
        $criteria->compare('src_url',$this->src_url,true);
        $criteria->compare('mime_type',$this->mime_type,true);
        $criteria->compare('size',$this->size);
        $criteria->compare('create_by',$this->create_by);
        $criteria->compare('create_time',$this->create_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if(parent::beforeSave())
        {
            if($this->isNewRecord)
            {
                $this->create_by=user()->getId();
                $this->create_time=time();
            }
            return true;
        }
        else
            return false;
    }         
    /**
     * A wrapper method to return records by $filename of this model
     * @param type $filename
     * @return \Attachment
     */
    public function findFile($filename) 
    {
        $this->getDbCriteria()->mergeWith(array('condition'=>'filename = \''.$filename.'\''));
        return $this;
    }  
    /**
     * A wrapper method to return records by matching object owner
     * @param type $filename
     * @return \Attachment
     */
    public function findObjectOwner($filename,$account_id) 
    {
        $this->getDbCriteria()->mergeWith(array('condition'=>'filename = \''.$filename.'\''));
        return $this;
    }  
    
    public function getFileOwner() 
    {
        return $this->create_by;
    }

    public function getFilename() 
    {
        return $this->filename;
    }

    public function getViewUrl()
    {
        return $this->getDownloadUrl();
    }
    /**
     * Return the attachment object
     * @return class
     */
    public function getObject()
    {
        $type = SActiveRecord::resolveTablename($this->obj_type);
        return $type::model()->findByPk($this->obj_id);
    }       

    public static function isMerchantObject($objectType)
    {
        return in_array($objectType, [
            Item::model()->tableName(),
            Order::model()->tableName(),
            ShippingOrder::model()->tableName(),
        ]);
    }
}