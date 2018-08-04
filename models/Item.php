<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Workflowable");
Yii::import("common.modules.comments.models.Comment");
Yii::import("common.modules.carts.behaviors.CartItemOptionBehavior");
Yii::import("common.modules.orders.components.GuestOrderTrait");
/**
 * This is the model class for table "item".
 *
 * The followings are the available columns in table 'item':
 * @property integer $id
 * @property integer $account_id
 * @property integer $order_id
 * @property integer $order_no
 * @property integer $shipping_id
 * @property string $shipping_no
 * @property string $tracking_no
 * @property string $tracking_url
 * @property string $name
 * @property string $unit_price
 * @property string $weight
 * @property integer $quantity
 * @property string $option_fee
 * @property string $shipping_surcharge
 * @property string $total_price
 * @property integer $total_weight
 * @property string $payment_method
 * @property string $campaign
 * @property string $options
 * @property integer $shop_id
 * @property integer $product_id
 * @property string $product_sku
 * @property string $product_url
 * @property string $product_image
 * @property string $product_review
 * @property string $currency
 * @property string $weight_unit
 * @property string $refund
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property PurchaseOrder $po
 *
 * @author kwlok
 */
class Item extends Workflowable
{
    use GuestOrderTrait;
    const DEMO_ITEM = -1;
    /**
     * Returns the static model of the specified AR class.
     * @return Item the static model class
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
        return Sii::t('sii','Item|Items',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_item';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'attachment' => [
                'class'=>'common.modules.media.behaviors.AttachmentBehavior',
                'stateVariable'=>SActiveSession::ATTACHMENT,
            ],
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
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.ItemWorkflowBehavior',
            ],               
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'product',
            ],
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],             
            'campaignable' => [
                'class'=>'common.modules.campaigns.behaviors.CampaignBgaBehavior',
            ], 
            'itemoptionbehavior' => [
                'class'=>'common.modules.carts.behaviors.CartItemOptionBehavior',
            ],         
            'paymentformbehavior' => [
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ],            
            'refundable' => [
                'class'=>'common.modules.orders.behaviors.RefundableBehavior',
            ], 
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, order_id, order_no, name, unit_price, total_price, shop_id, shipping_id, product_id, product_sku, product_url, product_image, currency, weight_unit, status', 'required'),
            array('order_id, shipping_order_id, quantity, total_weight, shop_id, product_id, shipping_id, product_review', 'numerical', 'min'=>1, 'integerOnly'=>true),
            array('account_id', 'length', 'max'=>12),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            array('name', 'length', 'max'=>2000),
            array('product_sku', 'length', 'max'=>30),
            array('unit_price, weight, option_fee, shipping_surcharge, total_price, total_weight', 'length', 'max'=>10),
            array('order_no, shipping_order_no, tracking_no, status', 'length', 'max'=>20),
            array('product_url, product_image, tracking_url', 'length', 'max'=>500),
            //This column stored json encoded payment_method info in different languages, 
            array('payment_method', 'length', 'max'=>1500),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages and 5 options, assuming each encoded option value takes 50 chars.
            array('options', 'length', 'max'=>5000),
            //This column stored json encoded campaign name/text in different languages, 
            array('campaign', 'length', 'max'=>5000),
            array('refund', 'length', 'max'=>500),
            array('currency, weight_unit', 'length', 'max'=>3),
            array('shipping_surcharge', 'default', 'setOnEmpty'=>true, 'value' => null),
            array('create_time', 'safe'),//else cannot do filter search - setting at scenario is not enough
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, account_id, order_id, order_no, shipping_order_id, shipping_order_no, tracking_no, tracking_url, name, unit_price, weight, quantity, option_fee, shipping_surcharge, total_price, total_weight, payment_method, campaign, options, shop_id, product_id, product_sku, product_url, product_image, shipping_id, product_review, currency, weight_unit, refund, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'order' => [self::BELONGS_TO, 'Order', 'order_id'],
            'shippingOrder' => [self::BELONGS_TO, 'ShippingOrder', 'shipping_order_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'product' => [self::BELONGS_TO, 'Product', 'product_id'],
            'shipping' => [self::BELONGS_TO, 'Shipping', 'shipping_id'],
        ];
    }

    public function scopes() 
    {
        return array();
    }

    public function order($id) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'order_id='.$id,
        ));
        return $this;
    }  

    public function shipping($id) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'shipping_id=\''.$id.'\'',
        ]);
        return $this;
    }       

    public function byOrderNo($orderNo,$id) 
    {
        $criteria=new CDbCriteria;
        $criteria->addColumnCondition([
            'id'=>$id,
            'order_no'=>$orderNo,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        logTrace(__METHOD__,$criteria);
        return $this;
    }       
    
    public function selectDistinctShop() 
    {
        $this->getDbCriteria()->mergeWith([
            'select'=>'shop_id',
            'distinct'=>true,
        ]);
        return $this;
    }    
    /**
     * This selects shops with active and non-expired subscription
     * @return $this
     */
    public function withShopSubscription()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.*';
        $criteria->join = 'INNER JOIN '.Shop::model()->tableName().' sh ON t.shop_id = sh.id and sh.status = \''.Process::SHOP_ONLINE.'\'';
        $criteria->join .= ' INNER JOIN '.Subscription::model()->tableName().' sc ON sc.account_id = sh.account_id and sh.status = \''.Process::SHOP_ONLINE.'\'';
        $criteria->condition = 'sc.status=\''.Process::SUBSCRIPTION_ACTIVE.'\' AND \''.Helper::getMySqlDateFormat(time()).'\' between sc.start_date AND sc.end_date';
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Format item data (multiple fields) as a column
     * @param type $locale
     * @param type $viewAsBuyer If false, the url link will be pointing to product; If true, its url is a purchased item (mainly for buyer use)
     * @param type $showQuantity
     * @return \CMap
     */
    public function getItemColumnData($locale=null,$viewAsBuyer=false,$showQuantity=false)
    {
        $list = new CMap();
        $columnData = [
                        'image'=>$this->getProductImageItemColumnData(),
                        'sku'=>['SKU'=>$this->product_sku],
                        'tracking'=>$this->getTrackingInfo(),
                        'options'=>$this->getOptions($locale),
                    ];
        if ($this->isCampaignItem())
            $columnData = array_merge($columnData,['campaign_tag'=>[false=>$this->getCampaignColorTag($locale)]]);        
        if ($this->weight!=null)
            $columnData = array_merge($columnData,['weight'=>[$this->getAttributeLabel('weight')=>$this->formatWeight($this->weight)]]);
        if ($showQuantity==true)
            $columnData = array_merge($columnData,['quantity'=>$this->quantity]);
        $list->add(CHtml::link($this->displayLanguageValue('name',user()->getLocale()),$viewAsBuyer?Item::getAccessUrl($this):($this->existsProduct?$this->product->viewUrl:Product::model()->getViewUrl($this->product_id))),$columnData);
        return $list;
    }        

    private $_c;//comment model
    public function setComment($model)
    {
         $this->_c = $model;
    }
    public function getComment($reviewUrl) 
    {
        $product = $this->product;
        if ($this->_c===null){
            $this->_c = new CommentForm('review');
            $this->_c->type = get_class($product);
            $this->_c->target = $product->id;
            $this->_c->src_id = $this->id;
            $this->_c->setReviewUrl($reviewUrl);
        }
        return $this->_c;
    }

    public function getReview() 
    {
        return Comment::model()->findByPk($this->product_review);
    }             
    public function saveReview($comment) 
    {
        if (!$comment->validate())
            throw new CException(Sii::t('sii','Review comment validation error'));
        
        $comment->insertEncodedContent('content');
        $comment->updateCounter(1);
        $this->product_review = $comment->id;
        $this->update();
    }             
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account ID'),
            'order_id' => Sii::t('sii','Order ID'),
            'order_no' => Sii::t('sii','Order No'),
            'shipping_order_id' => Sii::t('sii','Shipping Order ID'),
            'shipping_order_no' => Sii::t('sii','Shipping Order No'),
            'tracking_no' => Sii::t('sii','Tracking No'),
            'tracking_url' => Sii::t('sii','Tracking URL'),
            'name' => Sii::t('sii','Item Name'),
            'unit_price' => Sii::t('sii','Unit Price'),
            'weight' => Sii::t('sii','Weight'),
            'quantity' => Sii::t('sii','Quantity'),
            'option_fee' => Sii::t('sii','Option Fee'),
            'shipping_surcharge' => Sii::t('sii','Shipping Surcharge'),
            'total_price' => Sii::t('sii','Total Price'),
            'total_weight' => Sii::t('sii','Total Weight'),
            'payment_method' => Sii::t('sii','Payment Method'),
            'campaign' => Sii::t('sii','Campaign'),
            'options' => Sii::t('sii','Options'),
            'shop_id' => Sii::t('sii','Shop'),
            'shipping_id' => Sii::t('sii','Shipping'),
            'product_id' => Sii::t('sii','Product'),
            'product_sku' => Sii::t('sii','SKU'),
            'product_url' => Sii::t('sii','Item Url'),
            'product_image' => Sii::t('sii','Item Image'),
            'product_review' => Sii::t('sii','Review'),
            'currency' => Sii::t('sii','Currency'),
            'weight_unit' => Sii::t('sii','Weight Unit'),
            'refund' => Sii::t('sii','Refund'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Purchase Date'),
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
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('order_id',$this->order_id);
        $criteria->compare('order_no',$this->order_no);
        $criteria->compare('shipping_order_id',$this->shipping_order_id);
        $criteria->compare('shipping_order_no',$this->shipping_order_no);
        $criteria->compare('tracking_no',$this->tracking_no,true);
        $criteria->compare('tracking_url',$this->tracking_url,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('unit_price',$this->unit_price,true);
        $criteria->compare('weight',$this->weight);
        $criteria->compare('quantity',$this->quantity);
        $criteria->compare('option_fee',$this->option_fee,true);
        $criteria->compare('shipping_surcharge',$this->shipping_surcharge,true);
        $criteria->compare('total_price',$this->total_price,true);
        $criteria->compare('total_weight',$this->total_weight);
        $criteria->compare('options',$this->options,true);
        $criteria->compare('campaign',$this->campaign,true);
        $criteria->compare('payment_method',$this->payment_method,true);
        //$criteria->compare('shop_id',$this->shop_id);
        //$criteria->compare('shipping_id',$this->shipping_id,true);
        //$criteria->compare('product_id',$this->product_id,true);
        //$criteria->compare('product_sku',$this->product_sku,true);
        //$criteria->compare('product_url',$this->product_url,true);
        //$criteria->compare('product_image',$this->product_image,true);
        //$criteria->compare('product_review',$this->product_review,true);
        //$criteria->compare('currency',$this->currency,true);
        //$criteria->compare('weight_unit',$this->weight_unit,true);
        //$criteria->compare('refund',$this->refund,true);
        //$criteria->compare('status',$this->remarks,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
                'sort'=>false,
        ));
    }

    public function searchProductInventory()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>Product::model()->tableName()));
        $criteria->addColumnCondition(array('obj_id'=>$this->product_id));
        $criteria->addColumnCondition(array('sku'=>$this->product_sku));
        return new CActiveDataProvider('Inventory',array(
                'criteria'=>$criteria,
                'sort'=>false,
        ));
    }    

    public function searchShippingAddress()
    {
        return new CActiveDataProvider('OrderAddress',array(
                'criteria'=>array('condition'=>'order_id=\''.$this->order_id.'\''),
                'sort'=>false,
        ));
    }  
    
    public function getHasShippingOrder()
    {
        return $this->shipping_order_id!=null;
    }
    
    public function getTrackingInfo()
    {
        if (isset($this->tracking_no))
            return array('num'=>$this->tracking_no,'url'=>$this->tracking_url);
        return array();//empty
    }
    /**
     * Get option values 
     * @see CartItemOptionBehavior::parseOptions() 
     * @return type
     */
    public function getOptions($locale=null)
    {
        return $this->parseOptions($locale,true);
    }
    /**
     * Item product need to be validated as it may be deleted by seller
     * @return 
     */
    public function getBrand()
    {
        if ($this->existsProduct)
            return Brand::model()->findByPk($this->product->brand_id);
        else
            return null;
    }
    /**
     * This selects recent purchased items only from shops with active and non-expired subscription
     *
     * @param int $limit
     * @param bool $pagination
     * @return CActiveDataProvider
     */
    public function searchRecently($limit=1,$pagination=false)
    {
        $criteria = new CDbCriteria();
        $criteria->distinct = true; 
        $criteria->select = 't.*, b.create_time purchase_time'; 
        $criteria->join = 'INNER JOIN (SELECT c.product_id, c.create_time FROM '.$this->tableName().' c WHERE status != \''.Process::COMPLETED.'\' ORDER BY c.create_time DESC) b on b.product_id = t.product_id';
        $criteria->join .= ' INNER JOIN (SELECT s.id, s.account_id FROM '.Shop::model()->tableName().' s WHERE s.status = \''.Process::SHOP_ONLINE.'\') shop on shop.id = t.shop_id';
        $criteria->join .= ' INNER JOIN (SELECT x.account_id FROM '.Subscription::model()->tableName().' x WHERE x.status = \''.Process::SUBSCRIPTION_ACTIVE.'\' AND \''.Helper::getMySqlDateFormat(time()).'\' between x.start_date AND x.end_date) subscription on subscription.account_id = shop.account_id';
        $criteria->group = 't.product_id';
        $criteria->order = 'purchase_time desc';
        $criteria->limit = $limit;
        $criteria->offset = 0;
        logTrace(__METHOD__,$criteria);
        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
            'pagination'=>$pagination?['pageSize'=>$limit]:false,
        ]);
    }        
    /**
     * Return item price; 
     * If there are discount or campaign, will return price after discount or campaign
     * @return float
     */
    public function getPrice()
    {
        if ($this->hasCampaign())
            return $this->getCampaignOfferPrice();      
        else
            return $this->unit_price;
    }
    /**
     * Item discount by order level 
     * @return float (-ve to indicate discount)
     */
    public function getOrderDiscount($price=null)
    {
        if ($this->order->hasCampaignSale()){
            if (!isset($price))
                $price = $this->total_price;
            $offerPrice = Yii::app()->serviceManager->campaignManager->calculateOfferPrice(
                    $this->order->getCampaignSaleOfferType(),
                    $this->order->getCampaignSaleOfferValue(),
                    $price
                );
            return $offerPrice - $price;
        }
        else
            return 0.0;
    }    
    /**
     * @return boolean 
     */
    public function getHasOrderDiscount()
    {
        return $this->orderDiscount < 0;
    }     
    /**
     * Item discount by order level 
     * @return float 
     */
    public function getOrderDiscountTag($locale)
    {
        if ($this->order->hasCampaignSale())
            return $this->order->getCampaignSaleColorTag($locale,false);
        else
            return null;
    }    
    /**
     * Return tax on item according to order tax rate
     */
    public function getTaxPrice()
    {
        $tax = 0.0;
        foreach ($this->order->getTaxKeys() as $taxKey) {
            $tax += $this->total_price * $this->order->getTaxRate($taxKey);
        }
        return $tax + $this->getOrderDiscount($tax);
    }
    /**
     * Item grand total (after discount, tax etc)
     * @return type
     */
    public function getGrandTotal()
    {
        return $this->total_price + $this->orderDiscount + $this->taxPrice;
    }
    /**
     * Refund includes item total (includes shipping surcharge) and tax
     * Shippping fee by default is not refundable in suggestion
     */
    public function getRefundSuggestion()
    {
        return $this->total_price + $this->orderDiscount + $this->taxPrice;
    }
    /**
     * Return data provider of cart item detailed pricing information
     * @param type $item
     * @return \CArrayDataProvider
     */
    public function getPriceInfoDataProvider($locale=null) 
    {
        $data = new CList();
        $data->add(array('id'=>'price','key'=>false,'value'=>$this->formatCurrency($this->getPrice()),'cssClass'=>'info'));
        if ($this->isCampaignItem()){
            $data->add(array('id'=>'usual_price','key'=>false,'value'=>$this->formatCurrency($this->unit_price),'cssClass'=>'info'));
            $data->add(array('id'=>'offer_tag','key'=>false,'value'=>Helper::htmlColorTag($this->getCampaignOfferTag($locale),'orange').Yii::app()->controller->stooltipWidget($this->getCampaignText($locale),array('position'=>SToolTip::POSITION_TOP),true),'cssClass'=>'info'));
        }
        if ($this->option_fee>0){
            $data->add(array('id'=>'option_fee','key'=>false,'value'=>Sii::t('sii','plus ').$this->formatCurrency($this->option_fee).Yii::app()->controller->stooltipWidget(Sii::tl('sii','This is product option fee',$locale),array('position'=>SToolTip::POSITION_RIGHT,'cssClass'=>SToolTip::WIDTH_200),true),'cssClass'=>'info'));
        }
        if ($this->shipping_surcharge>0){//item level shipping surcharge
            $data->add(array('id'=>'shipping_surcharge','key'=>false,'value'=>Sii::t('sii','plus ').$this->formatCurrency($this->shipping_surcharge).Yii::app()->controller->stooltipWidget(Sii::tl('sii','This is product shipping surcharge',$locale),array('position'=>SToolTip::POSITION_RIGHT,'cssClass'=>SToolTip::WIDTH_200),true),'cssClass'=>'info'));
        }
        return new CArrayDataProvider($data->toArray(),array('keyField'=>false,'sort'=>false,'pagination'=>false));
    }    
    /**
     * Item product need to be validated as it may be deleted by seller
     * @return 
     */
    public function getProductImageThumbnail($version,$htmlOptions)
    {
        if ($this->existsProduct)
            return $this->product->getImageThumbnail($version,$htmlOptions);//get thumbnail for speed reason       
        else 
            return CHtml::image($this->productImageUrl,Sii::t('sii','Image'),array_merge($htmlOptions,array('width'=>$version.'px')));
    }
    /**
     * Item product need to be validated as it may be deleted by seller
     * @return 
     */
    public function getProductImagesCount()
    {
        if ($this->existsProduct)
            return $this->product->getImagesCount();        
        else 
            return 0;
    }
    /**
     * for item, always use product_image as image url source
     */
    public function getProductImageItemColumnData($version=Image::VERSION_XSMALL)
    {
        return array(
            'type'=>'MediaAssociation',
            'version'=>$version,
            'external'=>true,
            'externalImageUrl'=>$this->productImageUrl,
        );
    }
    
    public function getProductImageUrl()
    {
        return $this->product_image;
    }

    public function getProductUrl()
    {
        return $this->product_url;
    }    
 
    public function getExistsProduct()
    {
        return $this->product!=null;
    }    
    /**
     * Get additional item info on top of item name
     * @todo Add option / shipping surcharge? or item level discount? 
     */
    public function getInfo($locale=null)
    {
        $info = 'SKU '.$this->product_sku;
        if ($this->isCampaignItem()){
            $info .= ', '.$this->getCampaignOfferTag($locale);
        }
        
        $options = $this->getOptions($locale);
        if (!empty($options)){
            foreach($options as $key => $value){
                $info .= Sii::t('sii',', {option} {value}',['{option}'=>$key,'{value}'=>$value]).' ';
            }
        }

        if (isset($this->option_fee) && $this->option_fee > 0){
            $info .= Sii::t('sii',', option fee {option_fee}',['{option_fee}'=>$this->currency.$this->option_fee]);
        }
        
        if (isset($this->shipping_surcharge) && $this->shipping_surcharge > 0){
            $info .= Sii::t('sii',', shipping surcharge {shipping_surcharge}',array('{shipping_surcharge}'=>$this->currency.$this->shipping_surcharge));
        }
        
        return $info;
    }
    /**
     * Route to view this model
     * @return string route
     */
    public function getViewRoute()
    {
        return 'item/view/'.$this->order_no.'/'.Helper::urlstrtr($this->id);
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        $route = $this->getViewRoute();
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,request()->isSecureConnection);
        else
            return url($route);//$route cannot start with "/" else host info not following current scheme
    }   
    
    public static function getAccessUrl($item,$domain=null)
    {
        if (!isset($domain))
            $domain = param('HOST_DOMAIN');
        
        if ($item instanceof Item){
            if ($item->byGuestCustomer()){
                $route = 'item/track/'.base64_encode($item->id.Helper::PIPE_SEPARATOR.$item->order_no);
                return app()->urlManager->createDomainUrl($domain,$route,request()->isSecureConnection);
            }
            else 
                //cannot directly use viewUrl as calling from other non-http source (such as Messenger) will fails since dont have SERVER_NAME HTTP params
                return $item->getViewUrl($domain);
        }
        return false;
    }   
    //const RETURN_DAMAGED      = 1;
    //const RETURN_WRONG_ITEM   = 2;
    //const RETURN_ARRIVED_LATE = 3;
    //const RETURN_MISSING      = 4;
    //const RETURN_OTHER_REASON = 9;
    public static function getReturnReasons($code=null) 
    {
        if (!isset($code)){
            return [//put key as text for information display; else need to parse key to get text
                Sii::t('sii','Good Condition')=>Sii::t('sii','Good Condition'),
                Sii::t('sii','Damaged')=>Sii::t('sii','Damaged'),
                Sii::t('sii','Wrong Item')=>Sii::t('sii','Wrong Item'),
                Sii::t('sii','Arrived Late')=>Sii::t('sii','Arrived Late'),
                Sii::t('sii','Missing')=>Sii::t('sii','Missing'),
                Sii::t('sii','Other Reason')=>Sii::t('sii','Other Reason'),
            ];
        }
        else {
            $reasons = self::getReturnReasons();
            return $reasons[$code];
        }
    }
    
}