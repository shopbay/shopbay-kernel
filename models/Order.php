<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Workflowable");
Yii::import("common.modules.orders.components.GuestOrderTrait");
/**
 * This is the model class for table "s_order".
 *
 * The followings are the available columns in table 's_order':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $order_no
 * @property string $item_total
 * @property string $item_count
 * @property string $item_weight
 * @property string $item_shipping
 * @property string $payment_method 
 * @property string $currency
 * @property string $weight_unit
 * @property string $discount
 * @property string $tax
 * @property string $shipping_total
 * @property string $grand_total
 * @property string $refund
 * @property string $remarks
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Item[] $items
 * 
 * @author kwlok
 */
class Order extends Workflowable 
{
    use GuestOrderTrait;
    const DEMO_ORDER = -1;
   /**
     * Returns the static model of the specified AR class.
     * @return Order the static model class
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
        return Sii::t('sii','Purchase Order|Purchase Orders',[$mode]);
    }     
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_order';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
                'shopProxy'=>true,
            ],            
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],              
            'attachment' => [
                'class'=>'common.modules.media.behaviors.AttachmentBehavior',
                'stateVariable'=>SActiveSession::ATTACHMENT,
            ],     
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.OrderWorkflowBehavior',
            ],    
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'order_no',
                'iconUrlSource'=>'product',
            ],            
            'discountable' => [
                'class'=>'common.modules.campaigns.behaviors.DiscountableBehavior',
            ], 
            'campaignsaleable' => [
                'class'=>'common.modules.campaigns.behaviors.CampaignSaleBehavior',
            ],               
            'campaignpromocodeable' => [
                'class'=>'common.modules.campaigns.behaviors.CampaignPromocodeBehavior',
            ],               
            'shippable' => [
                'class'=>'common.modules.shippings.behaviors.ShippableBehavior',
            ],   
            'taxable' => [
                'class'=>'common.modules.taxes.behaviors.TaxableBehavior',
            ],     
            'refundable' => [
                'class'=>'common.modules.orders.behaviors.RefundableBehavior',
            ], 
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],            
            'paymentformbehavior' => [
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ],        
            'orderbehavior' => [
                'class'=>'common.modules.orders.behaviors.OrderBehavior',
            ],                  
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop_id', 'numerical', 'integerOnly'=>true],
            ['account_id', 'length', 'max'=>12],
            ['item_weight, item_total, shipping_total, grand_total', 'length', 'max'=>10],
            ['order_no', 'length', 'max'=>20],
            ['tax', 'length', 'max'=>500],
            ['currency, weight_unit', 'length', 'max'=>3],
            ['item_count', 'length', 'max'=>127],//tinyint datatype
            ['discount', 'length', 'max'=>2000],
            ['item_shipping', 'length', 'max'=>2500],
            ['payment_method', 'length', 'max'=>1500],
            ['refund', 'length', 'max'=>1000],
            ['remarks', 'length', 'max'=>100],

            //The following rule is used by OrderManager::submit()
            ['account_id, shop_id, order_no, item_total, item_count, item_shipping, shipping_total, currency, weight_unit, payment_method', 'required', 'on'=>'Cart'],

            ['id, account_id, shop_id, item_total, currency, weight_unit, item_weight, item_shipping, discount, tax, shipping_total, grand_total, refund, payment_method, status, create_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'items' => [self::HAS_MANY, 'Item', 'order_id'],
            'address' => [self::HAS_ONE, 'OrderAddress', 'order_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'shippingOrders' => [self::HAS_MANY, 'ShippingOrder', 'order_id'],
        ];
    }

    public function scopes() 
    {
        return [];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Buyer'),
            'shop_id' => Sii::t('sii','Shop'),
            'order_no' => Sii::t('sii','Order No'),
            'item_total' => Sii::t('sii','Item Total'),
            'item_count' => Sii::t('sii','Item Count'),
            'item_weight' => Sii::t('sii','Item Weights'),
            'item_shipping' => Sii::t('sii','Item Shippings'),
            'currency' => Sii::t('sii','Currency'),
            'weight_unit' => Sii::t('sii','Weight Unit'),
            'tax' => Sii::t('sii','Tax'),
            'discount' => Sii::t('sii','Discount'),
            'shipping_total' => Sii::t('sii','Shipping Total'),            
            'grand_total' => Sii::t('sii','Grand Total'),
            'payment_method' => Sii::t('sii','Payment Method'),
            'refund' => Sii::t('sii','Refund'),
            'remarks' => Sii::t('sii','Remarks'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Purchase Date'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }

    public  function getItemColumnData() 
    {
        $list = new CMap();
        foreach ($this->items as $item){
            $image = isset($item->product)?$item->product->image:null;
            $list->add($item->displayLanguageValue('name',user()->getLocale()),[
                'image'=>[
                    'type'=>
                    'MediaAssociation',
                    //'imagePath'=>'/files/products/',
                    'id'=>$image==null?Image::DEFAULT_IMAGE_PRODUCT:$image,
                    'version'=>Image::VERSION_XSMALL,
                ],
                'quantity'=>$item->quantity,
                'status'=>$item->getStatusText(),
                'tracking'=>['num'=>$item->tracking_no, 'url'=>$item->tracking_url],
            ]);
        }
        return $list;
    }
    
    public function saveCustomerRecord()
    {
        return Yii::app()->serviceManager->getCustomerManager()->saveRecord($this);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('order_no',$this->order_no,true);
        $criteria->compare('item_total',$this->item_total,true);
        $criteria->compare('item_count',$this->item_count,true);
        $criteria->compare('item_weight',$this->item_weight,true);
        $criteria->compare('item_shipping',$this->item_shipping,true);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('weight_unit',$this->weight_unit,true);
        $criteria->compare('discount',$this->discount,true);
        $criteria->compare('tax',$this->tax,true);
        $criteria->compare('shipping_total',$this->shipping_total,true);
        $criteria->compare('grand_total',$this->grand_total,true);
        $criteria->compare('refund',$this->refund,true);
        $criteria->compare('payment_method',$this->payment_method);
        $criteria->compare('remarks',$this->remarks);
        $criteria->compare('status',$this->status);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
                'criteria'=>$criteria,
                'sort'=>false,
        ]);
    }
    
    public function searchItems()
    {
        return new CActiveDataProvider('Item',[
                'criteria'=>['condition'=>'order_id='.$this->id],
                'sort'=>false,
        ]);
    }      
    /**
     * Search shipping whithin item_shipping data only (not into s_shippig table)
     * This is leverage on Shippable model and only relevant fields are captured.
     * 
     * Note: ShippingOrder here are used as a proxy object to access Shippable
     * 
     * @return \CArrayDataProvider
     */
    public function searchShippings()
    {
        $data = new CList();
        foreach ($this->getShippings() as $shippingId) {
            $order = new ShippingOrder();//a subset of order containing individual shippings
            $order->shipping_id = $shippingId;
            $order->shop_id = $this->shop_id;
            $order->order_id = $this->id;
            $order->item_shipping = json_encode([$shippingId=>$this->getShippingValues($shippingId)]);
            $data->add($order);
        } 
        return new CArrayDataProvider($data->toArray(),['keyField'=>false,'sort'=>false,'pagination'=>false]);
    }    
    /**
     * Route to view this model
     * @return string route
     */
    public function getViewRoute()
    {
        return 'order/view/'.$this->order_no;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        $route = $this->getViewRoute();
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,true);
        else
            return url($route);//$route cannot start with "/" else host info not following current scheme
    }
    /**
     * Url to contact merchant
     * @return string url
     */
    public function getContactMerchantUrl()
    {
        return url('message/compose/order/'.$this->order_no);
    }    
    /**
     * This is used for NotificationManager to send emails to merchant
     * for using RECIPIENT_CLASSMETHOD
     * @return type
     */
    public function getMerchantNotification()
    {
        return [
            'email'=>$this->shop->email,
            'recipient'=>$this->shop->contact_person,
        ];
    }  
    
    public function hasAddress()
    {
        if ($this->address!=null)
            return $this->address->hasLongAddress();
        return false;
    }
    
    public static function getAccessUrl($orderNo,$guestCheckout=true,$domain=null)
    {
        if (!isset($domain))
            $domain = param('HOST_DOMAIN');
        
        if ($guestCheckout)
            $route = 'order/track/'.$orderNo;
        else
            $route = 'order/view/'.$orderNo;
        
        return app()->urlManager->createDomainUrl($domain,$route,request()->isSecureConnection);
    }
    
}