<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.carts.components.CartData");
/**
 * OrderItemForm
 *
 * @author kwlok 
 */
class OrderItemForm extends CFormModel 
{    
    public $shop_id;
    public $product_id;
    public $shipping_id;
    public $campaign_id;
    protected $campaign_model = 'CampaignBga';
    public $itemkey;//unique key in cart
    public $name;
    public $unit_price;
    public $option_fee;
    public $options = [];
    public $shipping_surcharge=0.0;
    public $quantity=1;
    public $weight;
    public $weight_unit;
    public $currency;
    public $payment_method;
    /**
     * Indicate this item has been checkout; Default to false.
     * @var boolean 
     */
    public $checkout=false;
    /**
     * Indicate this is a campaign item (on promotion); If yes, it will has campaign item data;
     * Default to 'null.
     * @var boolean 
     */
    public $campaign_item;
    /**
     * Indicate the parent product of the promotion item; Default to null.
     * If not null, the value should be the parent item key (which is running the promotion)
     * @var string 
     */
    public $affinityKey;
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'shopModel',
            ],              
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],                      
        ];
    }  
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [			
            //addCart scenario
            ['product_id, quantity, shipping_id', 'required','on'=>'addCart'],
            ['quantity', 'numerical', 'integerOnly'=>true, 'message'=>Sii::t('sii','Please enter correct Quantity'), 'on'=>'addCart'],
            ['quantity', 'numerical', 'min'=>1, 'on'=>'addCart'],
            //2000 chars caters for multi-lang values - refer to s_cart, s_item $name column
            ['name', 'length', 'max'=>2000, 'on'=>'addCart'],
            ['unit_price, option_fee', 'length', 'max'=>10, 'on'=>'addCart'],
            ['itemkey', 'length', 'max'=>200, 'on'=>'addCart'],
            ['weight', 'numerical', 'integerOnly'=>true,'on'=>'addCart'],
            ['shop_id, shipping_id, campaign_id, campaign_item', 'safe','on'=>'addCart'],
            ['currency, weight_unit', 'length', 'max'=>3, 'on'=>'addCart'],
            ['options', 'ruleOptions','on'=>'addCart'],
            ['shipping_surcharge','ruleShipping','on'=>'addCart'],
            //This column stored json encoded payment_method info in different languages, 
            ['payment_method', 'length', 'max'=>1500],
                        
            //addOffer scenario, less strict as most of validation rules are already done by driver product on scenario campaign
            ['product_id, quantity, shipping_surcharge', 'required','on'=>'campaign'],
            ['quantity', 'numerical', 'integerOnly'=>true, 'message'=>Sii::t('sii','Please enter correct Quantity'), 'on'=>'campaign'],
            ['quantity', 'numerical', 'min'=>1, 'on'=>'campaign'],
            //2000 chars caters for multi-lang values - refer to s_cart, s_item $name column
            ['name', 'length', 'max'=>2000, 'on'=>'campaign'],
            ['unit_price, option_fee', 'length', 'max'=>10, 'on'=>'campaign'],
            ['itemkey', 'length', 'max'=>200, 'on'=>'campaign'],

            //only quantity scenario
            ['quantity', 'required','on'=>'quantity'],
            ['quantity', 'numerical', 'integerOnly'=>true,'message'=>Sii::t('sii','Please enter correct Quantity'), 'on'=>'quantity'],
            ['quantity', 'numerical', 'min'=>1, 'message'=>Sii::t('sii','Please enter correct Quantity'), 'on'=>'quantity'],

            //only shippingSurcharge scenario
            ['shipping_surcharge', 'numerical', 'min'=>0, 'max'=>999999.99, 'on'=>'shippingSurcharge'],
            //array('shipping_surcharge', 'type', 'type'=>'float', 'on'=>'shippingSurcharge'],

            //get all safe attributes scenario
            ['id, shop_id, product_id, shipping_id, itemkey, name, unit_price, quantity, option_fee, shipping_surcharge, options, currency, weight_unit', 'safe', 'on'=>'all'],
        ];
    }
    /**
     * Validate options data
     */
    public function ruleOptions($attribute,$params)
    {
        foreach ($this->options as $key => $option) {
            if ($option==null)
                $this->addError('options_'.$key,Sii::t('sii','Please specify {key}',['{key}'=>$key]));
        }
    }
    /**
     * Validate shipping data
     */
    public function ruleShipping($attribute,$params)
    {
        if ($this->shipping_surcharge!=null) {
            $shipping = explode('|', $this->shipping_surcharge);
            $model = new OrderItemForm('shippingSurcharge');
            $model->shipping_surcharge = $shipping[1];
            if (!$model->validate())
                 $this->addError('shipping_surcharge',$model->getError('shipping_surcharge'));
        }
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'quantity'=>Sii::t('sii','Quantity'),            
            'shipping_id'=>Sii::t('sii','Shipping'),
        );
    }

    public function setCheckout($bool) {
        $this->checkout = $bool;
    }
    public function getCheckout() {
        return (bool) $this->checkout;
    }
    public function getShop(){return $this->shop_id;}
    public function getProduct(){return $this->product_id;}
    public function getShipping(){return $this->shipping_id;}
    public function getCampaign(){return $this->campaign_id;}
    public function getName(){return $this->displayLanguageValue('name',user()->getLocale());}
    /**
     * Return item price; 
     * If there are discount or campaign, will return price after discount or campaign
     * 
     * @return type
     */
    public function getPrice()
    {
        if ($this->hasCampaign()&&$this->isCampaignItem())
            return $this->getCampaignModel()->getOfferPrice($this->getProductModel());      
        else
            return $this->unit_price;
    }
    public function getQuantity(){return $this->quantity;}
    public function getWeight(){return $this->weight;}
    public function getShippingSurcharge(){return $this->shipping_surcharge;}
    public function getOptions(){return $this->options;}
    public function getOptionFee(){return $this->option_fee;}
    public function getCurrency(){return $this->currency;}
    public function getWeightUnit(){return $this->weight_unit;}
    public function getKey(){return $this->itemkey;}
    public function getProductSKU(){
        return CartData::parseSKU($this->itemkey);//restore sku from item key
    } 
    public function getProductUrl($secure=false)
    {
        $model = $this->getProductModel();
        return $model==null?'#':$model->getUrl($secure);
    }
    public function getProductImage($htmlOptions=[])
    {
        return $this->getProductModel()->getImageThumbnail(Image::VERSION_XSMALL,$htmlOptions);
    }
    public function getProductImageUrl($version=Image::VERSION_ORIGINAL)
    {
        return $this->getProductModel()->getImageUrl($version);
    }
    
    private $_productModel;
    public function getProductModel()
    {
        if ($this->_productModel===null){
            $model = Product::model()->findByPk($this->product_id);
            if ($model===null){
                 logError(__METHOD__.' Product not found id='.$this->product_id);
                 throw new CHttpException(404,Sii::t('sii','Product not found'));
            }
            else
                $this->_productModel = $model;
        }
        return $this->_productModel;
    }
    
    private $_shopModel;
    public function getShopModel()
    {
        if ($this->_shopModel===null){
            $model = Shop::model()->findByPk($this->shop_id);
            if ($model===null){
                 logError(__METHOD__.' Shop not found id='.$this->shop_id);
                 throw new CHttpException(404,Sii::t('sii','Shop not found'));
            }
            else
                $this->_shopModel = $model;
        }
        return $this->_shopModel;
    }
    
    private $_campaignModel;
    public function getCampaignModel()
    {
        if ($this->_campaignModel===null){
            $type = $this->campaign_model;
            $model = $type::model()->findByPk($this->campaign_id);
            if ($model!=null){
                $this->_campaignModel = $model;
            }
        }
        return $this->_campaignModel;
    }
    public function hasCampaign()
    {
        return $this->getCampaignModel()!=null;
    }
    public function hasCampaignBgaG()
    {
        return $this->hasCampaign()?$this->getCampaignModel()->hasG():false;
    }
    public function getCampaignData()
    {
        $data = [];
        if ($this->hasCampaign()){
            //scan thru all supported locales at shop levels
            $campaignText = new CMap();
            $offerTag = new CMap();
            foreach ($this->shopModel->getLanguageKeys() as $language) {
                $campaignText->add($language,$this->getCampaignModel()->getCampaignText($language));
                $offerTag->add($language,$this->getCampaignModel()->getOfferTag(true,true,$language));
            }
            $data = [
                'campaign_id'=>$this->campaign_id,
                'campaign_model'=>$this->campaign_model,
                'campaign_name'=>$this->getCampaignModel()->name,
                'campaign_offer_type'=>$this->getCampaignModel()->offer_type,
                'campaign_text'=>$campaignText->toArray(),
                'campaign_at_offer'=>$this->getCampaignModel()->at_offer,
                'campaign_offer_tag'=>$offerTag->toArray(),
                'campaign_offer_price'=>$this->getCampaignModel()->getOfferPrice($this->getProductModel()),
                'campaign_usual_price'=>$this->getCampaignModel()->getUsualPrice($this->getProductModel()),
                'campaign_item'=>$this->campaign_item,
                'affinity_key'=>$this->affinityKey,
            ];
        }
        return $data;
    }
    
    public function getCampaignLocaleValue($field,$locale)
    {
        return $this->getCampaignData()[$field][$locale];
    }
    
    public function setCampaignItem($status)
    {
        $this->campaign_item = $status;
    }
    public function isCampaignItem()
    {
        return $this->campaign_item!=false;
    }
    public function setAffinityKey($key)
    {
        $this->affinityKey = $key;
    }
    public function getAffinityKey()
    {
        return $this->affinityKey;
    }
    public function hasAffinity()
    {
        return $this->affinityKey!=null;
    }

}
