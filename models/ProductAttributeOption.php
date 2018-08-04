<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_product_attribute_option".
 *
 * The followings are the available columns in table 's_product_attribute_option':
 * @property integer $id
 * @property integer $attr_id
 * @property string $code
 * @property string $name
 * @property string $surcharge
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property ProductAttribute $attr
 *
 * @author kwlok
 */
class ProductAttributeOption extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ProductAttributeOption the static model class
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
        return Sii::t('sii','Option|Options',array($mode));
    }      
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_product_attribute_option';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ),
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),            
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('attr_id, code, name', 'required'),
            array('id, attr_id', 'numerical', 'integerOnly'=>true),
            array('code', 'length', 'max'=>2),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            array('name', 'length', 'max'=>1000),
            array('surcharge', 'length', 'max'=>10),
            array('surcharge', 'type', 'type'=>'float'),
            array('surcharge', 'default', 'setOnEmpty'=>true, 'value' => null),
            array('surcharge', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, attr_id, code, name, surcharge, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'attribute' => array(self::BELONGS_TO, 'ProductAttribute', 'attr_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'attr_id' => Sii::t('sii','Attribute'),
            'code' => Sii::t('sii','Option Code'),
            'name' => Sii::t('sii','Option Name'),
            'surcharge' => Sii::t('sii','Surcharge'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
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
        $criteria->compare('attr_id',$this->attr_id);
        $criteria->compare('code',$this->code,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('surcharge',$this->surcharge,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    public function getShop()
    {
        return $this->attribute->product->shop;
    }
    /**
     * Encode option data (includes multi languages)
     * Default: <attr code> + <option code> (concatenation, no separator)
     * Verbose: <attr code> + <attr name> + <option code> + <option name> + <surcharge> (optional)
     * 
     * Usage:
     * @see CartItemForm::assignOptions()
     * @see Inventory::formatSKU()
     * 
     * @param boolean $verbose
     * @return type
     */
    public function encodeOption($verbose=false)
    { 
        if ($verbose)
            return  $this->attribute->code.Helper::PIPE_SEPARATOR.
                    base64_encode($this->attribute->name).Helper::PIPE_SEPARATOR.
                    $this->code.Helper::PIPE_SEPARATOR.
                    base64_encode($this->name).Helper::PIPE_SEPARATOR.
                    (isset($this->surcharge)?$this->surcharge:'');
        else
            return $this->attribute->code.$this->code;
    }  

    public function getOptionText($locale=null,$verbose=false)
    {
        $text = '';
        if ($verbose)
            $text .= $this->code.' - ';
        $text .= $this->displayLanguageValue('name',$locale);
        $text .= $this->getSurchargeText();
        return $text;
    }  

    public function getSurchargeText()
    {
        return self::getSurchargeTextTemplate($this->surcharge, $this->attribute->product);
    }  
    
    public static function getSurchargeTextTemplate($surcharge, $formatOwner)
    {
        return ($surcharge==null || $surcharge==0)?'':Sii::t('sii',' ({amount} surcharge)',array('{amount}'=>$formatOwner->formatCurrency($surcharge)));
    }  
    
    public function hasAssociations()
    {
        if (Inventory::lookupSKUOption($this->attribute->product_id, $this->attribute->code.$this->code))
            return true;
        return false;
    }         
}