<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * This is the model class for table "s_cart".
 *
 * The followings are the available columns in table 's_cart':
 * @property integer $id
 * @property string $shopper
 * @property integer $shop_id
 * @property integer $product_id
 * @property integer $shipping_id
 * @property string $itemkey
 * @property string $name
 * @property string $unit_price
 * @property integer $quantity
 * @property integer $weight
 * @property string $payment_method
 * @property string $campaign
 * @property string $options
 * @property string $option_fee
 * @property string $shipping_surcharge
 * @property string $total_price
 * @property string $total_weight
 * @property string $currency
 * @property string $weight_unit
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Cart extends Transitionable
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Pcart the static model class
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
        return Sii::t('sii','Cart|Carts',array($mode));
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_cart';
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
            'account' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'shopper',
            ),
            'workflow' => array(
                'class'=>'common.services.workflow.behaviors.CartWorkflowBehavior',
            ),               
            'campaignable' => array(
                'class'=>'common.modules.campaigns.behaviors.CampaignBgaBehavior',
            ),     
            'paymentformbehavior' => array(
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ),             
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('shopper, shop_id, product_id, shipping_id, itemkey, name, unit_price, total_price, currency, weight_unit, status', 'required'),
            array('shop_id, product_id, shipping_id, quantity, weight, total_weight', 'numerical', 'integerOnly'=>true),
            array('shopper', 'length', 'max'=>20),
            array('itemkey', 'length', 'max'=>200),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            array('name', 'length', 'max'=>2000),
            array('unit_price, option_fee, shipping_surcharge, total_price, status', 'length', 'max'=>10),
            //This column stored json encoded payment_method info in different languages, 
            array('payment_method', 'length', 'max'=>1500),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages and 5 options, assuming each encoded option value takes 50 chars.
            array('options', 'length', 'max'=>5000),
            //This column stored json encoded campaign name/text in different languages, 
            array('campaign', 'length', 'max'=>5000),
            array('currency, weight_unit', 'length', 'max'=>3),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, shopper, shop_id, product_id, shipping_id, itemkey, name, unit_price, quantity, weight, payment_method, campaign, option_fee, shipping_surcharge, total_price, options, currency, weight_unit, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array();
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'shopper' => Sii::t('sii','Shopper'),
            'shop_id' => Sii::t('sii','Shop'),
            'product_id' => Sii::t('sii','Product'),
            'shipping_id' => Sii::t('sii','Shipping'),
            'itemkey' => Sii::t('sii','Item Key'),
            'name' => Sii::t('sii','Item Name'),
            'unit_price' => Sii::t('sii','Unit Price'),
            'quantity' => Sii::t('sii','Quantity'),
            'weight' => Sii::t('sii','Weight'),
            'payment_method' => Sii::t('sii','Payment Method'),
            'campaign' => Sii::t('sii','Campaign'),
            'options' => Sii::t('sii','Options'),
            'option_fee' => Sii::t('sii','Option Fee'),
            'shipping_surcharge' => Sii::t('sii','Shipping Surcharge'),
            'total_price' => Sii::t('sii','Amount'),
            'total_weight' => Sii::t('sii','Total Weight'),
            'currency' => Sii::t('sii','Currency'),
            'weight_unit' => Sii::t('sii','Weight Unit'),
            'status' => Sii::t('sii','Status'),
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
        $criteria->compare('shopper',$this->shopper,true);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('product_id',$this->product_id);
        $criteria->compare('shipping_id',$this->shipping_id);
        $criteria->compare('itemkey',$this->itemkey,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('unit_price',$this->unit_price,true);
        $criteria->compare('quantity',$this->quantity);
        $criteria->compare('weight',$this->weight);
        $criteria->compare('payment_method',$this->payment_method,true);
        $criteria->compare('campaign',$this->campaign,true);
        $criteria->compare('options',$this->options,true);
        $criteria->compare('option_fee',$this->option_fee,true);
        $criteria->compare('shipping_surcharge',$this->shipping_surcharge,true);
        $criteria->compare('total_price',$this->total_price,true);
        $criteria->compare('total_weight',$this->total_weight,true);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('weight_unit',$this->weight_unit,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    
    public function complete() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status = \''.Process::CHECKOUT_CONFIRM.'\'',
        ));
        return $this;
    }         

    public function incomplete() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status NOT IN (\''.Process::CHECKOUT_CONFIRM.'\',\''.Process::ERROR.'\')',
        ));
        return $this;
    }         
    public static function beginProcess()
    {
        return Process::CART;
    }
    public static function endProcess()
    {
        return Process::CHECKOUT_CONFIRM;
    }
    public static function beginAction()
    {
        return WorkflowManager::getActionAfterProcess(self::model()->tableName(),self::beginProcess());
    }
    public static function endAction()
    {
        return WorkflowManager::getActionbeforeProcess(self::model()->tableName(),self::endProcess());
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('cart');//not utilized for now
    }         
        
}