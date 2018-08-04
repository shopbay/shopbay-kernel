<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_shipping_tier".
 *
 * The followings are the available columns in table 's_shipping_tier':
 * @property integer $id
 * @property integer $shipping_id
 * @property integer $base
 * @property string $floor
 * @property string $ceiling
 * @property string $rate
 *
 * The followings are the available model relations:
 * @property Shipping $shipping
 *
 * @author kwlok
 */
class ShippingTier extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ShippingTier the static model class
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
        return Sii::t('sii','Tier|Tiers',array($mode));
    }       
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_shipping_tier';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('shipping_id, base, floor, rate', 'required'),
            array('id, shipping_id, base', 'numerical', 'integerOnly'=>true),
            array('floor, ceiling, rate', 'length', 'max'=>10),
            array('floor, rate', 'type','type'=>'float', 'allowEmpty'=>false),
            array('ceiling', 'default', 'setOnEmpty'=>true, 'value' => null),
            array('ceiling', 'ruleCeiling'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, shipping_id, base, floor, ceiling, rate', 'safe', 'on'=>'search'),
        );
    }
    /**
     * Validate rate
     */
    public function ruleCeiling($attribute,$params)
    {
        if ($this->ceiling!=null)
            if ($this->ceiling<=$this->floor)
                $this->addError('ceiling',Sii::t('sii','Ceiling must be larger than Floor'));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'shipping' => array(self::BELONGS_TO, 'Shipping', 'shipping_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'shipping_id' => Sii::t('sii','Shipping'),
            'base' => Sii::t('sii','Base'),
            'floor' => Sii::t('sii','Floor'),
            'ceiling' => Sii::t('sii','Ceiling'),
            'rate' => Sii::t('sii','Rate'),
        );
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('shipping_id',$this->shipping_id);
        $criteria->compare('base',$this->base);
        $criteria->compare('floor',$this->floor,true);
        $criteria->compare('ceiling',$this->ceiling,true);
        $criteria->compare('rate',$this->rate,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    const BASE_SUBTOTAL  = 0;
    const BASE_WEIGHT    = 1;
    public static function getBases()
    {
        return array(
            ShippingTier::BASE_SUBTOTAL => Sii::t('sii','Order Subtotal'),
            ShippingTier::BASE_WEIGHT => Sii::t('sii','Weight'),
            //rest types yet to be supported...
        );
    }
    public function getBaseDesc()
    {
        $bases = self::getBases();
        return $bases[$this->base];
    }

    public function getFormatFloor()
    {
        return $this->base==ShippingTier::BASE_WEIGHT?$this->shipping->formatWeight($this->floor):$this->shipping->formatCurrency($this->floor);
    }

    public function getFormatCeiling()
    {
        if ($this->ceiling==null)
            return '';
        else
            return $this->base==ShippingTier::BASE_WEIGHT?$this->shipping->formatWeight($this->ceiling):$this->shipping->formatCurrency($this->ceiling);
    }
}