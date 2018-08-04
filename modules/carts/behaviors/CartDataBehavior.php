<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopPage");
Yii::import('common.modules.shops.components.FacebookShopTrait');
/**
 * Description of CartDataBehavior
 *
 * @author kwlok
 */
class CartDataBehavior extends CBehavior 
{
    use FacebookShopTrait;
    
    public  $cartUrl;
    private $_cart;//cart instance
    const COMPLETED = 'complete';
    
    public function getCart()
    {
        if (!isset($this->_cart)){
            $this->_cart = Yii::app()->serviceManager->getCart();
            $this->_cart->onPreview = $this->getOwner()->inPreviewMode;//follow controller preview condition
        }
        return $this->_cart;
    }    
    
    public function getCartUrl($shop=null)
    {
        if (isset($this->cartUrl['callback']))
            return $this->getOwner()->{$this->cartUrl['callback']}();
        
        if (isset($shop))
            $url = ShopPage::getPageUrl($this->cart->getShop($shop), ShopPage::CART, false, true);
        elseif (isset($this->cartUrl) && is_scalar($this->cartUrl))
            $url = $this->cartUrl;
        else
            $url = url('cart');//default
        
        return $this->getOwner()->appendQueryParams($url);
    }
    /**
     * Get Cart data
     * @param type $shop
     * @param type $queryParams Any additional query params, e.g. use case can be facebook shop
     * @return type
     */
    public function getCartData($shop,$queryParams=[]) 
    {
        if ($this->cart->isEmpty($shop)){
            return CHtml::tag('div',['class'=>'cart-empty'],$this->getOwner()->renderView('carts.empty', ['shop'=>$shop], true));
        }
        else {
            return CHtml::tag('div',['class'=>'cart-container'],
                        $this->getOwner()->renderView('carts.cartform', [
                            'action'=>'Index',
                            'shop'=>$shop,
                            'queryParams'=>$queryParams,
                        ], 
                        true));
        }
    }
    /**
     * Return cart data group by shop
     * @param type $locale
     * @return type
     */
    public function getCartSectionsData($locale=null) 
    {
        $sections = new CList();
        if ($this->cart->isEmpty()){
            $sections->add([
                'id'=>'cart-empty',
                'name'=> 'cart-empty',
                'html'=>$this->getOwner()->renderView('carts.empty', [], true),
            ]);
        }
        else {
            $cnt = 0;
            foreach ($this->cart->getShops(true) as $model ){
                $sections->add([
                    'id'=>$this->getOwner()->getCartName($model->id),
                    'name'=> CHtml::link($model->displayLanguageValue('name',$locale),$model->url.'/cart'),
                    'heading'=>true,'top'=>$cnt==0?true:null,
                    'html'=>$this->getOwner()->renderView('carts.cartform', ['shop'=>$model->id], true),
                    'htmlOptions'=>['class'=>'cart-container'],
                ]);
                $cnt++;
            }
        }
        return $sections->toArray();
    }    
    /**
     * Return data provider of cart shipping 
     * @param type $checkout
     * @param type $shop_id
     * @return \CArrayDataProvider
     */
    public function getCartShippingDataProvider($checkout,$shop_id) 
    {
        $rawData = $checkout?$this->cart->getCheckoutShippingsByShop($shop_id):$this->cart->getShippingsByShop($shop_id);
        return new CArrayDataProvider( array_keys($rawData->toArray()),['keyField'=>false,'sort'=>false,'pagination'=>false]);
    }    
    /**
     * Return data provider of cart item 
     * @param type $checkout
     * @param type $shop_id
     * @param type $shipping_id
     * @return \CArrayDataProvider
     */
    public function getCartItemDataProvider($checkout,$shop_id,$shipping_id) 
    {
        $rawData = $checkout?$this->cart->getCheckoutItemsByShopShipping($shop_id,$shipping_id):$this->cart->getItemsByShopShipping($shop_id,$shipping_id);
        return new CArrayDataProvider($rawData->toArray(),['keyField'=>false,'sort'=>false,'pagination'=>false]);
    } 
    /**
     * Return data provider of cart item detailed information
     * @param type $item
     * @return \CArrayDataProvider
     */
    public function getCartItemInfoDataProvider($item,$checkout=false,$queryParams=[],$showQuantity=false,$showSubtotal=false) 
    {
        $itemName = $item->getName();
        if ($showQuantity)
            $itemName = $item->getQuantity().' x '.$itemName;
        
        if (!empty($queryParams))
            $itemLink = CHtml::link($itemName, 'javascript:void(0);', ['onclick'=>'productview("'.Helper::constructUrlQuery($item->getProductUrl(request()->isSecureConnection), $queryParams).'")']);
        else
            $itemLink = CHtml::link($itemName, $item->getProductUrl(request()->isSecureConnection));
        
        $data = new CList();
        $data->add(['id'=>'item_name','key'=>false,'value'=>$itemLink,'cssClass'=>'info']);
        if ($showSubtotal)
            $data->add(['id'=>'item_subtotal','key'=>false,'value'=>$item->getProductModel()->formatCurrency($item->getTotalPrice()),'cssClass'=>'info']);
        $data->add(['id'=>'item_sku','key'=>Sii::t('sii','SKU'),'value'=>$item->getProductSKU(),'cssClass'=>'info']);
        $options = $item->parseOptions(user()->getLocale());
        if (!empty($options)){
            foreach($options as $key => $value){
                $data->add(['id'=>$key,'key'=>$key,'value'=>$value,'cssClass'=>'info']);
            }
        }
        if ($item->getWeight()!=null){
            $data->add(['id'=>'item_weight','key'=>Sii::t('sii','Weight'),'value'=>$item->getProductModel()->formatWeight($item->getWeight()),'cssClass'=>'info']);
        }
        if ($item->isCampaignItem()){
            $data->add(['id'=>'item_promotion_flag','key'=>false,'value'=>Helper::htmlColorTag(Sii::t('sii','Promotion'),'palevioletred',false),'cssClass'=>'info']);
        }
        
        if (!$checkout && !$item->hasAffinity())
            $data->add(['id'=>'item_remove_link','key'=>false,'value'=>CHtml::link(Sii::t('sii','Remove this item'),'javascript:void(0);',['onclick'=>'removeitem("'.$item->getKey().'");']),'cssClass'=>'remove-link']);

        return new CArrayDataProvider($data->toArray(),['keyField'=>false,'sort'=>false,'pagination'=>false]);
    }  
    /**
     * Return data provider of cart item detailed pricing information
     * @param type $item
     * @return \CArrayDataProvider
     */
    protected function getCartItemPriceInfoDataProvider($item) 
    {
        $shopModel = $this->cart->getShop($item->getShop());
        $data = new CList();
        $data->add(array('id'=>'price','key'=>false,'value'=>$shopModel->formatCurrency($item->getPrice()),'cssClass'=>'info'));
        if ($item->isCampaignItem()){
            logTrace(__METHOD__.' isCampaignItem',$item->getAttributes());
            logTrace(__METHOD__.' getCampaignData',$item->getCampaignData());
            $data->add(array('id'=>'usual_price','key'=>false,'value'=>$shopModel->formatCurrency($item->unit_price),'cssClass'=>'info'));
            $data->add(array('id'=>'offer_tag','key'=>false,'value'=>Helper::htmlColorText($item->getCampaignModel()->getOfferTag(false,true)).$this->getTooltip($item->getCampaignModel()->getCampaignText(user()->getLocale())),'cssClass'=>'info'));
        }
        if ($item->getOptionFee()>0){
            $data->add(array('id'=>'option_fee','key'=>false,'value'=>Sii::t('sii','plus ').$shopModel->formatCurrency($item->getOptionFee()).$this->getOwner()->getTooltip(Sii::t('sii','This is product option fee')),'cssClass'=>'info'));
        }
        if ($item->getShippingSurcharge()>0){//item level shipping surcharge
            $data->add(array('id'=>'shipping_surcharge','key'=>false,'value'=>Sii::t('sii','plus ').$shopModel->formatCurrency($item->getShippingSurcharge()).$this->getTooltip(Sii::t('sii','This is product shipping surcharge')),'cssClass'=>'info'));
        }
        return new CArrayDataProvider($data->toArray(),array('keyField'=>false,'sort'=>false,'pagination'=>false));
    }
    /**
     * Return data provider of shipping level subtotal 
     * @param type $shop
     * @param type $shipping
     * @return \CArrayDataProvider
     */
    protected function getCartSubTotalDataProvider($shop,$shipping) 
    {
        $shopModel = $this->cart->getShop($shop);
        $shippingData = (object)$this->cart->getCheckoutSubTotalByShipping($shipping);
        $dataArray = array(
            array('id'=>'items_subtotal_'.$shippingData->shipping_id,'key'=>Sii::t('sii','Subtotal'),'value'=>$shopModel->formatCurrency($shippingData->price),'cssClass'=>'total'),
            array('id'=>'shippingRate_subtotal_'.$shippingData->shipping_id,'key'=>Sii::t('sii','Shipping Fee'),'value'=>$shopModel->formatCurrency($shippingData->shipping_rate),'cssClass'=>'total'),
        );
        //if ($shippingData->shipping_surcharge>0){
        //    $dataArray = array_merge($dataArray,array(
        //        array('id'=>'shippingSurcharge_subtotal_'.$shippingData->shipping_id,'key'=>Sii::t('sii','Shipping Surcharge'),'value'=>$shopModel->formatCurrency($shippingData->shipping_surcharge),'cssClass'=>'total'),
        //    ));
        //}
        return new CArrayDataProvider($dataArray,array('keyField'=>false,'sort'=>false,'pagination'=>false));
    }      
    /**
     * Return data provider of shop level total 
     * Calculate order level discount if any
     * 
     * @param type $shop
     * @return \CArrayDataProvider
     */
    protected function getCartTotalDataProvider($shop) 
    {
        $shopModel = $this->cart->getShop($shop);
        $total = $this->cart->getCheckoutTotal($shop,Helper::NO_FORMAT,true,false);
        logTraceDump(__METHOD__.' checkout total data',$total);
        $dataArray = array(
            array('id'=>'items_total_'.$shop,'key'=>Sii::t('sii','Total Price'),'value'=>$shopModel->formatCurrency($total->price),'cssClass'=>'total'),
        );
        
        //put a placeholder for sale campaign discount total column, if there is sale data, put them in
        if ($shopModel->hasCampaign()){
            $dataArray = array_merge($dataArray,array(
                array('id'=>'discount_total_'.$shop,'key'=>$this->getOwner()->stooltipWidget($shopModel->getCampaign()->getCampaignText(user()->getLocale()),array('position'=>SToolTip::POSITION_TOP),true).Sii::t('sii','Discount {offer_tag}',array('{offer_tag}'=>$shopModel->getCampaign()->getOfferTag())),
                      'value'=>$total->hasSale?$total->discountSaleData->discount_text:'','cssClass'=>'total discount'.($total->hasSale?'':' hidden')),
            ));
        }
        
        //put a placeholder for promocode total column, if there is promocode data, put them in
        if ($shopModel->hasPromocodes()){
            $dataArray = array_merge($dataArray,array(
                array('id'=>'promocode_total_'.$shop,'key'=>$this->getOwner()->stooltipWidget(CHtml::tag('span',array('class'=>'promocode-tooltip-content'),$total->hasPromo?$total->discountPromoData->discount_tip:''),array('position'=>SToolTip::POSITION_LEFT),true).CHtml::tag('span',array('class'=>'promocode-label'),$total->hasPromo?$total->discountPromoData->campaign['text'][user()->getLocale()]:''),
                      'value'=>$total->hasPromo?$total->discountPromoData->discount_text:'','cssClass'=>'total promocode'.($total->hasPromo?'':' hidden')),
            ));
        }
        
        //retreive tax payable breakdowns
        foreach ($this->cart->getTaxes($shop) as $tax => $payable) {
            $taxData = $this->getOwner()->module->serviceManager->getTaxManager()->parseTaxData($payable);
            logTrace(__METHOD__.' taxData',$taxData);
            $dataArray = array_merge($dataArray,array(
                array('id'=>'tax_total_'.$shop.'_'.$tax,'key'=>$shopModel->parseLanguageValue($taxData->name,user()->getLocale()).' '.$taxData->rate_text,'value'=>$shopModel->formatCurrency($taxData->amount),'cssClass'=>'total tax'),
            ));
        }

        $dataArray = array_merge($dataArray,array(
            array('id'=>'shippingFee_total_'.$shop,'key'=>Sii::t('sii','Total Shipping Fee'),'value'=>$shopModel->formatCurrency($total->shippingRate),'cssClass'=>'total shipping'),
            //put a placeholder for free shipping discount column, if there is promocode data, put them in
            array('id'=>'shippingFee_discount_'.$shop,'key'=>$this->getOwner()->stooltipWidget(CHtml::tag('span',array('class'=>'promocode-tooltip-content'),$total->onFreeShipping?$total->freeShippingTip:''),array('position'=>SToolTip::POSITION_TOP),true).CHtml::tag('span',array('class'=>'promocode-label'),Sii::t('sii','Free Shipping')),
                  'value'=>$total->onFreeShipping?$total->freeShippingDiscountText:'','cssClass'=>'total freeshipping'.($total->onFreeShipping?'':' hidden')),
            array('id'=>'grand_total_'.$shop,'key'=>Sii::t('sii','Grand Total'),'value'=>$shopModel->formatCurrency($total->grandTotal),'cssClass'=>'total grandtotal'),
        ));
        
        return new CArrayDataProvider($dataArray,array('keyField'=>false,'sort'=>false,'pagination'=>false));
    }     
    /**
     * Get hash cart name
     * @param string $salt
     * @return string
     */
    public function getCartName($salt)
    {
        return 'cart-'.hash('crc32b',$salt);
    }   
    /**
     * Get add on buttons in a button bar
     * @param type $action
     * @param type $params
     * @return type
     */
    public function getAddOnButtons($action,$params=[])
    {
        //todo This may not required as $this->queryParams (in PreviewControllerTrait) may already collected this)
        $this->setCartOnFacebook(isset($params['queryParams'])?$params['queryParams']:[]);
        
        $shopUrl = $this->getOwner()->appendQueryParams($params['shopModel']->getUrl(request()->isSecureConnection));
        $likeScript = $this->getOwner()->inPreviewMode ? $this->getPreviewScript() : 'like(\''.$this->getCartName($params['shopModel']->id).'\');' ;
        
        $btn_ContinueShopping   = array('name'=>$this->getButtonName('shopping-'.$params['shopModel']->id),
                                        'caption'=>Sii::t('sii','Continue Shopping'),
                                        'onclickJS'=>'window.location = "'.$shopUrl.'"',
                                        'htmlOptions'=>array('class'=>'ui-button',),
                                        'disabled'=>false);
        $btn_Like               = array('name'=>$this->getButtonName('like-'.$params['shopModel']->id),
                                        'caption'=>Sii::t('sii','Like'),
                                        'onclickJS'=>$likeScript,
                                        'htmlOptions'=>array('class'=>'ui-button',),
                                        'disabled'=>false);
        //construct buttons
        $buttons = [];
        
        if (!$this->cartOnFacebook){//facebook mode will not show button for continue shopping
            $buttons[] = $btn_ContinueShopping;
        }
        if (strcasecmp($action,'Index')==0){//entry page for order
            if (!user()->isGuest)
                $buttons[] = $btn_Like;
        }
        return $buttons;                
    }
    /**
     * Get checkout buttons in cart summary
     * @param type $action
     * @param type $params
     * @return type
     */
    public function getCheckoutButtons($action,$params=[])
    {
        //todo This may not required as $this->queryParams (in PreviewControllerTrait) may already collected this)
        $this->setCartOnFacebook(isset($params['queryParams'])?$params['queryParams']:[]);
        
        if ($this->cartOnFacebook)
            $checkoutScript = $this->getCheckoutScriptOnFacebook($params['shopModel']);
        else
            $checkoutScript = $this->getCheckoutScript($params['shopModel']);
        
        $btn_Checkout = [
            'name'=>$this->getButtonName('checkout-'.$params['shopModel']->id),
            'caption'=>Sii::t('sii','Checkout'),
            'onclickJS'=>$checkoutScript,
            'disabled'=>false,
            'htmlOptions'=>['class'=>'ui-button proceed-button'],
        ];

        if ($this->cart->hasShippingMethodPickupOnly() && $action=='SelectPaymentMethod')
            $prevAction = 'Checkout';//skip address
        else
            $prevAction = WorkflowManager::getPreviousAction(
                            Cart::model()->tableName(), 
                            WorkflowManager::getPreviousAction(Cart::model()->tableName(), $action)
                        );
                     
        $prevActionUrl = $this->getOwner()->appendQueryParams('/cart/management/'.$prevAction);
        $nextActionUrl = $this->getOwner()->appendQueryParams('/cart/management/'.ucfirst($action));
        /**
         * NOTE: Next button action is also dynamically changed for step to select payment method
         * @see ButtonGetAction
         * @see cart.js selectmethod()
         */
        $btn_PrevAction = [
            'name'=>$this->getButtonName('prev-'.$params['shopModel']->id),
            'caption'=>'<i class="fa fa-arrow-left"></i>',
            'onclickJS'=>'previous(\''.$prevActionUrl.'\');',
            'htmlOptions'=>['class'=>'ui-button back-button',],
            'disabled'=>false,
        ];
        $btn_Action = [
            'name'=>$this->getButtonName('action-'.$params['shopModel']->id),
            'caption'=>array_key_exists('label', $params)?$params['label']:'<i class="fa fa-arrow-right"></i>',
            'onclickJS'=>'proceed(\''.$nextActionUrl.'\');',
            'htmlOptions'=>['class'=>'ui-button proceed-button'],
            'disabled'=>array_key_exists('disable', $params)?$params['disable']:false,
        ];
        //construct buttons
        $buttons = [];
        
        if (strcasecmp($action,'Index')==0){//entry page for order
            $buttons[] = $btn_Checkout;
            return $buttons;
        }
        else if ($action==self::COMPLETED){ 
            return $buttons;                
        }
        else {//for checkout and confirm
            $btn_BackToCart = [
                'name'=>$this->getButtonName('back-'.$params['shopModel']->id),
                'caption'=>'<i class="fa fa-arrow-left"></i>',
                'onclickJS'=>'window.location = "'.$this->getCartUrl($params['shopModel']->id).'"',
                'htmlOptions'=>['class'=>'ui-button back-button'],
                'disabled'=> false,
            ];
            $buttons[] = is_null($prevAction)?$btn_BackToCart:$btn_PrevAction;
            $buttons[] = $btn_Action;
            return $buttons;
        }
    }    
    /**
     * Get hash button name
     * @param string $salt
     * @return string
     */
    public function getButtonName($salt)
    {
        return 'button-'.hash('crc32b',$salt);
    }    
    
    public function getPaypalCheckoutButton($shopModel)
    {
        $button = '';
        if ($shopModel->hasPaymentMethod(PaymentMethod::PAYPAL_EXPRESS_CHECKOUT)){
            if ($this->cartOnFacebook)
                $checkoutScript = $this->getCheckoutScriptOnFacebook($shopModel);
            elseif (user()->isGuest && $shopModel->isGuestCheckoutAllowed())
                //cannot go direct to paypal express checkout as need guest email to be captured. So route to normal checkout page
                $checkoutScript = $this->getCheckoutScript($shopModel);
            else {
                $checkoutScript = $this->getOwner()->inPreviewMode ? $this->getPreviewScript() : 'paypalexpresscheckout('.$shopModel->id.')';
            }

            $button = CHtml::tag('span',array('class'=>'paypal-express-checkout'),CHtml::link(Chtml::image(PaypalExpressCheckout::getExpressCheckoutButton(),'Paypal Express Checkout', array('style'=>'cursor:pointer')),
                        'javascript:void(0);',
                        array('onclick'=>$checkoutScript)
                    ));
        }
        return $button;
    }
    
    public function getCheckoutScript($shopModel)
    {
        $url = $this->getOwner()->appendQueryParams('/cart/management/checkout');
        return 'checkout(\''.(user()->isGuest?'GET':'POST').'\',\''.$this->getCartName($shopModel->id).'\',\''.$url.'\');';
    }
    
    public function getCheckoutScriptOnFacebook($shopModel)
    {
        return 'checkoutfromfacebook(\''.$this->getCartName($shopModel->id).'\',\''.Yii::app()->urlManager->createHostUrl('/cart/management/checkout',true).'\');';
    }
    
    protected function getPreviewScript()
    {
        return 'alert("'.Sii::t('sii','Action not allowed when shop is in preview').'");';
    }
    
    protected function getTooltip($content,$placement='top')
    {
        return $this->getOwner()->widget('common.widgets.stooltip.SBootstrapToolTip',['content'=>$content,'placement'=>$placement,],true);        
    }
}
