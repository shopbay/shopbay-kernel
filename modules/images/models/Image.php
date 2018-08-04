<?php
/**
 * Image active record class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @since 0.5
 */

/**
 * This is the model class for table "Image".
 *
 * The followings are the available columns in table 'Image':
 * @property integer $id
 * @property string $parent
 * @property integer $parentId
 * @property string $filename
 * @property string $extension
 * @property integer $byteSize
 * @property string $mimeType
 * @property string $created
 * @property integer $deleted
 */
class Image extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Image the static model class
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
		return 'Image';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('filename, extension, byteSize, mimeType', 'required'),
			array('parentId, byteSize', 'numerical', 'integerOnly'=>true),
			array('parent, filename, extension, mimeType, created', 'length', 'max'=>255),
			array('id, parent, parentId, filename, extension, byteSize, mimeType, created', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}
        
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Img::t('core','Id'),
			'parentId' => Img::t('core','Parent'),
			'parent' => Img::t('core', 'Parent Type'),
			'filename' => Img::t('core','Filename'),
			'extension' => Img::t('core','Extension'),
			'byteSize' => Img::t('core','Byte Size'),
			'mimeType' => Img::t('core','Mime Type'),
			'created' => Img::t('core','Created'),
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
		$criteria->compare('filename',$this->filename,true);
		$criteria->compare('extension',$this->extension,true);
		$criteria->compare('byteSize',$this->byteSize);
		$criteria->compare('mimeType',$this->mimeType,true);
		$criteria->compare('created',$this->created,true);

		return new CActiveDataProvider(get_class($this),array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Renders this image.
	 * @param string $version the image version to render.
	 * @param string $alt the alternative text.
	 * @param array $htmlOptions the html options.
	 */
	public function render($version,$alt='',$htmlOptions=array())
	{
            
                $versions=Yii::app()->image->versions;
		if(isset($versions[$version]))
			$thumb=Yii::app()->image->createVersion($this->id,$version);
                        
		$src = Yii::app()->image->getURL($this->id, $version);
		echo CHtml::image($src,$alt,$htmlOptions);
	}
}
