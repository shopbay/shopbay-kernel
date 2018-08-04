<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Workflowable");
/**
 * This is the model class for table "s_shipping_order".
 *
 * The followings are the available columns in table 's_shipping_order':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property integer $shipping_id
 * @property string $shipping_no
 * @property integer $order_id
 * @property string $order_no
 * @property string $item_total
 * @property integer $item_count
 * @property string $item_shipping
 * @property string $payment_method 
 * @property string $discount
 * @property string $tax
 * @property string $grand_total
 * @property string $refund
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Order $order
 * @property Account $account
 * @property Shop $shop
 *
 * @author kwlok
 */
class ShippingOrder extends Workflowable 
{
    const DEMO_SHIPPING_ORDER = -1;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ShippingOrder the static model class
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
        return 's_shipping_order';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shipping Order|Shipping Orders',array($mode));
    }      
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'attachment' => array(
                'class'=>'common.modules.media.behaviors.AttachmentBehavior',
                'stateVariable'=>SActiveSession::ATTACHMENT,
            ),
            'account' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
            ),
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ), 
            'merchant' => array(
                'class'=>'common.components.behaviors.MerchantBehavior',
            ),
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
            ),      
            'workflow' => array(
                'class'=>'common.services.workflow.behaviors.ShippingOrderWorkflowBehavior',
            ),
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'order_no',
                'iconUrlSource'=>'product',
            ),             
            'discountable' => array(
                'class'=>'common.modules.campaigns.behaviors.DiscountableBehavior',
            ), 
            'campaignsaleable' => array(
                'class'=>'common.modules.campaigns.behaviors.CampaignSaleBehavior',
            ),               
            'campaignpromocodeable' => array(
                'class'=>'common.modules.campaigns.behaviors.CampaignPromocodeBehavior',
            ),               
            'shippable' => array(
                'class'=>'common.modules.shippings.behaviors.ShippableBehavior',
                'shippingIdAttribute'=>'shipping_id',
            ),  
            'refundable' => array(
                'class'=>'common.modules.orders.behaviors.RefundableBehavior',
            ), 
            'taxable' => array(
                'class'=>'common.modules.taxes.behaviors.TaxableBehavior',
            ),      
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),  
            'paymentformbehavior' => array(
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ),             
            'orderbehavior' => array(
                'class'=>'common.modules.orders.behaviors.OrderBehavior',
                'orderIdAttribute'=>'order_id',
                'paymentReferenceAttribute'=>'shipping_no',
            ),      
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, shipping_id, shipping_no, order_id, order_no, item_total, item_count, item_shipping, payment_method, grand_total, status', 'required'),
            array('account_id, shop_id, shipping_id, order_id, item_count', 'numerical', 'integerOnly'=>true),
            array('shipping_no, order_no', 'length', 'max'=>20),
            array('item_total, grand_total, status', 'length', 'max'=>10),
            array('item_shipping', 'length', 'max'=>2500),
            array('discount', 'length', 'max'=>2000),
            array('refund', 'length', 'max'=>1000),
            array('payment_method', 'length', 'max'=>1500),
            array('tax', 'length', 'max'=>500),
             
            //for to be fulfilled order
            array('id', 'ruleDecisionFulfill','on'=>WorkflowManager::DECISION_FULFILL),
            //for to be cancelled order
            array('id', 'ruleDecisionCancel','on'=>WorkflowManager::DECISION_CANCEL),

            array('create_time', 'safe'),//else cannot do filter search - setting at scenario is not enough
            //Scenario Verify - used by tasks module
            array('shipping_no, order_no, item_count, item_total, item_shipping, grand_total, create_time', 'safe', 'on'=>'Verify'),
            //Scenario Process - used by tasks module
            array('shipping_no, order_no, item_count, item_total, item_shipping, grand_total, create_time', 'safe', 'on'=>'Process'),
            //Scenario Refund - used by tasks module
            array('shipping_no, order_no, item_count, item_total, item_shipping, grand_total, create_time', 'safe', 'on'=>'Refund'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, account_id, shop_id, shipping_id, shipping_no, order_id, order_no, item_total, item_count, item_shipping, payment_method, refund,  discount, tax, grand_total, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'order' => array(self::BELONGS_TO, 'Order', 'order_id'),
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }
    /**
     * Workflow decision rule check for fulfillment
     */
    public function ruleDecisionFulfill($attribute,$params)
    {
        if (!($this->orderCancelled() || $this->skipWorkflow())){
        //exclude cancelled order as it has no decision or when in mode skipWorkflow
            if (!$this->fulfillable())
                $this->addError('id',Sii::t('sii','You can fulfill order only for purchased product with status {status}',array('{status}'=>Process::getHtmlDisplayText(Process::SHIPPED))));
        }
    }
    /**
     * Workflow decision rule check for cancel
     */
    public function ruleDecisionCancel($attribute,$params)
    {
        if (!($this->orderCancelled() || $this->skipWorkflow())){
        //exclude cancelled order as it has no decision or when in mode skipWorkflow
            if (!$this->cancellable()){
                $this->addError('id',Sii::t('sii','You can cancel order with all purchased product in status {status}',array('{status}'=>Process::getHtmlDisplayText(Process::ORDERED))));
            }
        }
    }

    public function createShippingOrder()
    {
        $this->insert();
        $items = Item::model()->order($this->order_id)->locateShop($this->shop_id)->shipping($this->shipping_id)->findAll();
        foreach ($items as $item) {
            $item->shipping_order_id = $this->id;
            $item->shipping_order_no = $this->shipping_no;
            $item->update();
        }
    }
    
    public function scopes() 
    {
        return [];
    }
    
    public function findOrder($orderNo) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['order_no'=>$orderNo,'shipping_no'=>$orderNo], 'OR');
        $this->getDbCriteria()->mergeWith($criteria);//search into both order and shipping no column, and expect to pick up one. This is assume PO and SO number are unique
        return $this;//return the first found record
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Merchant'),
            'shop_id' => Sii::t('sii','Shop'),
            'shipping_id' => Sii::t('sii','Shipping Order'),
            'shipping_no' => Sii::t('sii','Shipping No'),
            'order_id' => Sii::t('sii','Purchase Order'),
            'order_no' => Sii::t('sii','Order No'),
            'item_total' => Sii::t('sii','Total Price'),
            'item_count' => Sii::t('sii','Total Item'),
            'item_shipping' => Sii::t('sii','Total Shipping Fee'),
            'payment_method' => Sii::t('sii','Payment Method'),
            'discount' => Sii::t('sii','Discount'),
            'tax' => Sii::t('sii','Tax'),
            'grand_total' => Sii::t('sii','Shipping Order Total'),
            'refund' => Sii::t('sii','Refund'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Purchase Date'),
            'update_time' => $this->status==Process::REFUND?Sii::t('sii','Refund Date'):Sii::t('sii','Update Time'),
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
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('shipping_id',$this->shipping_id);
        $criteria->compare('shipping_no',$this->shipping_no,true);
        $criteria->compare('order_id',$this->order_id);
        $criteria->compare('order_no',$this->order_no,true);
        $criteria->compare('item_total',$this->item_total,true);
        $criteria->compare('item_count',$this->item_count);
        $criteria->compare('item_shipping',$this->item_shipping,true);
        $criteria->compare('payment_method',$this->payment_method,true);
        $criteria->compare('refund',$this->refund,true);
        $criteria->compare('discount',$this->discount,true);
        $criteria->compare('tax',$this->tax,true);
        $criteria->compare('grand_total',$this->grand_total,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'sort'=>false,
        ));
    } 
    /**
     * Return all purchased items of this shipping order
     * @return type
     */
    public function getItems()
    {
        return Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id)->findAll();
    }
    
    public function searchItems()
    {
        Item::model()->resetScope();
        return new CActiveDataProvider(Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id),
                                       array('sort'=>false));
    }        
    public function searchRefundableItems()
    {
        return new CActiveDataProvider(Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id)->cancelled(),
                                       array('sort'=>false));
    }        
    public function searchRefundedItems()
    {
        return new CActiveDataProvider(Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id)->refunded(),
                                       array('sort'=>false));
    }        
    public function searchShippedItems()
    {
        return new CActiveDataProvider(Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id)->shipped(),
                                       array('sort'=>false));
    } 

    public function getOwnDataProvider()
    {
        return new CActiveDataProvider('ShippingOrder',array(
            'criteria'=>array('condition'=>'id=\''.$this->id.'\''),
            'sort'=>false,
        ));
    }      

    public  function getItemColumnData()
    {
        $list = new CMap();
        $items = Item::model()->merchant($this->shop_id)->order($this->order_id)->shipping($this->shipping_id)->findAll();
        foreach ($items as $item){
             $list->add($item->displayLanguageValue('name',user()->getLocale()),
                       array('image'=>$item->getProductImageItemColumnData(),
                            'quantity'=>$item->quantity,
                            'status'=>$item->getStatusText(),
                            'tracking'=>array('num'=>$item->tracking_no, 'url'=>$item->tracking_url)));

        }
        return $list;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        $route = 'order/view/'.$this->shipping_no;
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,true);
        else
            return url($route);//$route cannot start with "/" else host info not following current scheme
    }
    /**
     * Url to view its purchase order model
     * @return string url
     */
    public function getPOViewUrl()
    {
        return url('order/view/'.$this->order_no);
    }
    /**
     * @override
     * Custom url to work on task for this model
     * 
     * @see urlManager for mapping (main.php)
     * @see Transitionable::getTaskUrl()
     * @return string url
     */
    public function getTaskUrl($action)
    {
        return url('tasks/shippingOrder/'.strtolower($action));
    }     
    
    public  function getOrderTotal() 
    {
        $data = app()->db->createCommand()
                ->select('sum(grand_total) as total')
                ->from($this->tableName())
                ->where('order_id=:order and shop_id=:shop', array(':order'=>$this->order_id,':shop'=>$this->shop_id))
                ->queryRow();
        return $data['total'];
    }
    /**
     * Refund includes item total, shipping surcharge and tax
     * Shippping fee by default is not refundable in suggestion
     * @see Item::getRefundSuggestion()
     * @param type $dataProvider
     * @return \stdClass
     */
    public function getRefundable($dataProvider=null) 
    {
        if (!isset($dataProvider))
            $dataProvider = $this->searchRefundableItems();
        $refundable = new stdClass();
        $refundable->total_item = $dataProvider->getTotalItemCount();
        $refundable->total_amount = 0;
        $refundable->total_price = 0;
        $refundable->total_shipping_surcharge = 0;
        $refundable->total_tax = 0;
        foreach ($dataProvider->data as $data){
            logTrace(__METHOD__,$data->getAttributes());
            $refundable->total_price += $data->total_price + $data->orderDiscount;
            $refundable->total_shipping_surcharge += $data->shipping_surcharge;
            $refundable->total_tax += $data->taxPrice;
            $refundable->total_amount += $data->refundSuggestion;
        }
        return $refundable;
    }
}