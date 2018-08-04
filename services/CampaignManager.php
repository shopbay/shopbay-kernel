<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.modules.campaigns.components.DiscountData");
Yii::import("common.modules.campaigns.components.CampaignSaleDiscountData");
Yii::import("common.modules.campaigns.components.CampaignPromocodeDiscountData");
/**
 * Description of CampaignManager
 *
 * @author kwlok
 */
class CampaignManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }  
    /**
     * Create CampaignPromocode model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createCampaignPromocode($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->code = strtoupper($model->code);//force convert all to capitals letters
        $model->status = Process::CAMPAIGN_OFFLINE;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update CampaignPromocode model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateCampaignPromocode($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        $model->code = strtoupper($model->code);//force convert all to capitals letters
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ));
    }
    /**
     * Delete CampaignPromocode model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function deleteCampaignPromocode($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                    'recordActivity'=>array(
                        'event'=>Activity::EVENT_DELETE,
                        'account'=>$user,
                    ),
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    } 
    /**
     * Create CampaignBga model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createCampaignBga($user,$model)
    {
        $this->validate($user, $model, false);
        $model->id = null;//set to null to let auto-increment works
        $model->account_id = $user;
        $model->status = Process::CAMPAIGN_OFFLINE;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'insertShippings'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update CampaignBga model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateCampaignBga($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'updateShippings'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ));
    }
    /**
     * Delete CampaignBga model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function deleteCampaignBga($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                    'recordActivity'=>array(
                        'event'=>Activity::EVENT_DELETE,
                        'account'=>$user,
                    ),
                    'detachMediaAssociation'=>self::EMPTY_PARAMS,
                    'deleteShippings'=>self::EMPTY_PARAMS,
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    } 
    /**
     * Create CampaignSale model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createCampaignSale($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::CAMPAIGN_OFFLINE;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update CampaignSale model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateCampaignSale($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ));
    }
    /**
     * Delete CampaignSale model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function deleteCampaignSale($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                    'recordActivity'=>array(
                        'event'=>Activity::EVENT_DELETE,
                        'account'=>$user,
                    ),
                    'detachMediaAssociation'=>self::EMPTY_PARAMS,
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    } 
    /**
     * Check if exists promocode for the shop
     * @param type $shop_id
     * @return boolean
     */
    public function existsPromocode($shop_id,$code)
    {
        return CampaignPromocode::model()->active()->notExpired()->shopAndCode($shop_id,$code)->exists();
    }       
    /**
     * Check if exists any shop offer by CampaignPromocode
     * @param type $shop_id
     * @return boolean
     */
    public function existsCampaignPromocode($shop_id)
    {
        return CampaignPromocode::model()->active()->notExpired()->exists($this->_getShopOfferCondition($shop_id));
    }  
    /**
     * Check if has any shop offer by CampaignPromocode; Always return first found campaign
     * @param type $shop_id
     * @param type $code
     * @return CampaignPromocode Return null if not found
     */
    public function checkCampaignPromocode($shop_id,$code)
    {
        return CampaignPromocode::model()->active()->notExpired()->shopAndCode($shop_id,$code)->find();
    }    
    /**
     * Check if has any shop offers by CampaignSale
     * @param type $shop_id
     * @return array Array of CampaignSale
     */    
    public function checkCampaignSales($shop_id)
    {
        return CampaignSale::model()->active()->notExpired()->findAll($this->_getShopOfferCondition($shop_id));
    }
    /**
     * Check if has any shop offer by CampaignSale; Always return first found campaign
     * @param type $shop_id
     * @return CampaignSale Return null if not found
     */
    public function checkCampaignSale($shop_id)
    {
        return CampaignSale::model()->active()->notExpired()->find($this->_getShopOfferCondition($shop_id));
    }
    /**
     * Check if exists any shop offer by CampaignSale
     * @param type $shop_id
     * @return boolean
     */
    public function existsCampaignSale($shop_id)
    {
        return CampaignSale::model()->active()->notExpired()->exists($this->_getShopOfferCondition($shop_id));
    }    
    /**
     * Count any shop offer by CampaignSale
     * @param integer $buy_x Campaign buy_x
     * @param integer $buy_x_qty Campaign buy_x_qty (optional)
     * @param boolean $includeGetY Whether to also include all campaigns with get_y
     * @return boolean
     */
    public function countCampaignSale($shop_id)
    {
        $count = CampaignSale::model()->active()->notExpired()->count($this->_getShopOfferCondition($shop_id));
        logTrace(__METHOD__.' $count '.$count.' for shop '.$shop_id);
        return $count;
    }    
    /**
     * Check if exists any product offer by CampaignBga
     * @param string $type Campaign scenario
     * @param integer $buy_x Product product id
     * @return boolean
     */
    public function existsCampaignBga($type, $buy_x)
    {
        logTrace(__METHOD__.' type='.$type.' buy_x='.$buy_x);
        switch ($type) {
            case CampaignBga::X_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXOnly($buy_x)->exists();
            case CampaignBga::X_OFFER_MORE:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXMore($buy_x)->exists();
            case CampaignBga::X_X_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXGetX($buy_x)->exists();
            case CampaignBga::X_Y_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXGetY($buy_x)->exists();
            default:
                return false;
        }
    }
    /**
     * Check if has any product offer by CampaignBga; Always return first found campaign
     * @param string $type Campaign scenario
     * @param integer $buy_x Product product id
     * @return CampaignBga Return null if not found
     */
    public function checkCampaignBga($type, $buy_x)
    {
        switch ($type) {
            case CampaignBga::X_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXOnly($buy_x)->find();
            case CampaignBga::X_OFFER_MORE:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXMore($buy_x)->find();
            case CampaignBga::X_X_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXGetX($buy_x)->find();
            case CampaignBga::X_Y_OFFER:
                return CampaignBga::model()->active()->notExpired()->onOfferBuyXGetY($buy_x)->find();
            default:
                return null;
        }
    }
    /**
     * Count any product offers 
     * @param type $buy_x
     * @param type $excludeCampaign
     * @return boolean
     */
    public function countCampaignBga($buy_x, $excludeCampaign=null)
    {
        $count = CampaignBga::model()->active()->notExpired()->forProduct($buy_x,$excludeCampaign)->count();
        logTrace(__METHOD__.' count='.$count);
        return $count;
    }
    /**
     * Return shop offer price by CampaignSale in array of following data elements, for example:
     * <pre>
     * array (
     *     'has_offer' => 'true',
     *     'offer_price' => '$3.00',
     *     'discount' => '-$2.00', //discount price is returned in negative
     * )
     * </pre>
     * @param type $campaign This can be either campaign instance or campaign id
     * @param type $amount The purchased amount
     * @param type $quantity The quantity of purchased items
     * @param type $format
     * @return type
     */
    public function checkShopSalePrice($campaign,$amount,$quantity=null,$format=true)
    {
        $campaign = $this->_getCampaign($campaign, Campaign::SALE);
        
        $discountData = new CampaignSaleDiscountData();
        $discountData->offer_price = $amount;//initial value
        
        if ($campaign->onQuantitySale() && !isset($quantity))
            throw new CException(Sii::t('sii','Purchased quantity not found'));
        
        if (($campaign->onAmountSale() && $amount >= $campaign->sale_value) ||
            ($campaign->onQuantitySale() && $quantity >= $campaign->sale_value)) {
            $discountData->offer_price = $this->calculateOfferPrice($campaign->offer_type, $campaign->offer_value, $amount);
            $discountData->has_offer = $campaign->isFreeShipping?false:true;//set to true for non-free-shipping offer promocode campaign
            $discountData->free_shipping = $campaign->isFreeShipping;
        }
        
        $discountData->discount = $discountData->offer_price - $amount;
        $discountData->discount_text = $campaign->formatCurrency($discountData->discount);
        $discountData->discount_tip = $campaign->getCampaignText(user()->getLocale());
        $discountData->createCampaignData($campaign);
        
        //formatting
        if ($format){
            $discountData->offer_price = $campaign->formatCurrency($discountData->offer_price);
            $discountData->discount = $campaign->formatCurrency($discountData->discount);
        }
        logTrace(__METHOD__.' discount data',$discountData->toArray());
        return $discountData->toArray();
    }   
    /**
     * Return shop offer price by CampaignPromocode in array of following data elements, for example:
     * <pre>
     * array (
     *     'promocode' => 'PROMOCODE',
     *     'has_offer' => 'true',
     *     'offer_price' => '$3.00',
     *     'discount' => '-$2.00', //discount price is returned in negative
     *     'campaign_text' => 'offer tag', 
     *     'campaign_tip' => 'campaign name',
     * )
     * </pre>
     * @param Shop $shop This shop instance
     * @param type $amount The purchased amount
     * @param type $code The input promocode to check discount
     * @param type $format
     * @return type
     */
    public function checkShopPromocodePrice($shop,$amount,$code,$format=true)
    {
        if (!($shop instanceof Shop)){
            throw new CException(Sii::t('sii','Shop instance not found.'));
        }
        
        $discountData = new CampaignPromocodeDiscountData();
        $discountData->promocode = $code;//initial value
        $discountData->offer_price = $amount;//initial value
        
        if (!$this->existsPromocode($shop->id, $code)){
            logError(__METHOD__.' Promotional code not found or matched; shop_id='.$shop->id,$discountData->toArray());
        }
        else{
            $campaign = $this->checkCampaignPromocode($shop->id, $code);
            $discountData->offer_price = $this->calculateOfferPrice($campaign->offer_type, $campaign->offer_value, $amount);
            $discountData->has_offer = $campaign->isFreeShipping?false:true;//set to true for non-free-shipping offer promocode campaign
            $discountData->discount = $discountData->offer_price - $amount;
            $discountData->discount_text = $campaign->formatCurrency($discountData->discount);
            $discountData->discount_tip = $campaign->displayName().': '.$discountData->promocode;
            $discountData->free_shipping = $campaign->isFreeShipping;
            $discountData->createCampaignData($campaign);

            //formatting
            if ($format){
                $discountData->offer_price = $campaign->formatCurrency($discountData->offer_price);
                $discountData->discount = $campaign->formatCurrency($discountData->discount);
            }            
        }
        logTrace(__METHOD__.' discount data',$discountData->toArray());
        return $discountData->toArray();
    }       
    /**
     * Return product price in array of following data elements, for example:
     * <pre>
     * array (
     * 'show_unit_price' => false,
     * 'show_usual_price' => true,
     * 'unit_price' => '$3.00',
     * 'usual_price' => '$3.00',
     * 'offer_price' => '$2.00',
     * )
     * </pre>
     * @param type $product
     * @param type $quantity
     * @param mixed $campaign Can be campaign object, campaign id, 
     *              or campaign key params that required to compute price
     *              e.g. array(
     *                      'type'=>'<value>',//campaign type e.g. bga, rfm, coupon, sale etc
     *                      'at_offer'=>'<value>',
     *                      'offer_type'=>'<value>',
     *                   );
     *               
     * @param type $format Indicate whether to format the price
     * @return array
     */
    public function checkProductPrice($product,$quantity=1,$campaign=null,$format=true)
    {
        //below mainly to support X_X_OFFER
        if (is_array($product)&&isset($product['model'])&&isset($product['xProduct'])){
            $xProduct = $product['xProduct'];//this mean input product x is not targeted for offer
            $product = $this->_getProduct($product['model']);
        }
        else
            $product = $this->_getProduct($product);
        
        //default values
        $data['show_unit_price'] = $quantity==1?false:true;
        $data['show_usual_price'] = false;
        $data['show_offer_price'] = true;
        $data['show_quantity'] = false;
        $data['quantity'] = $quantity;
        $data['unit_price'] = $product->unit_price;
        $data['usual_price'] = $quantity * $product->unit_price;
        $data['offer_price'] = $data['usual_price'];
        //if passed by campaign configuration in array form
        if (is_array($campaign)&&isset($campaign['type'])&&$campaign['type']=='bga'){
            $data['offer_price'] = $this->calculateOfferPrice($campaign['offer_type'], $campaign['at_offer'], $product->unit_price,$quantity);
        }
        //if passed by campaign object or campaign id
        if (!is_array($campaign)&&isset($campaign)&&$campaign!='undefined'){
            $campaign = $this->_getCampaign($campaign, Campaign::BGA);
            if ($campaign->hasG()){
                switch ($campaign->offerScenario) {
                    case CampaignBga::X_X_OFFER:
                        //when input $product is x
                        if ($product->id==$campaign->buy_x&&isset($xProduct)){
                            $data['show_quantity'] = false;
                            break;
                        }
                        //when input $product is y
                        if ($product->id==$campaign->get_y){
                            $data['show_quantity'] = true;
                            $data['show_usual_price'] = true;
                            $data['offer_price'] = $this->calculateOfferPrice($campaign->offer_type, $campaign->at_offer, $product->unit_price,$data['quantity']);
                        }
                        break;
                    case CampaignBga::X_Y_OFFER:
                        //when input $product is y
                        if ($product->id==$campaign->get_y){
                            $data['show_quantity'] = true;
                            $data['show_usual_price'] = true;
                            $data['offer_price'] = $this->calculateOfferPrice($campaign->offer_type, $campaign->at_offer, $product->unit_price,$data['quantity']);
                        }
                        break;
                    default:
                        break;
                }
            }
            else {
                //@see CampaignBga::X_OFFER or X_OFFER_MORE type
                if ($product->id==$campaign->buy_x){//input $product is x
                    $data['offer_price'] = $this->calculateOfferPrice($campaign->offer_type, $campaign->at_offer, $product->unit_price,$quantity);
                    $data['show_usual_price'] = true;
                }
            }
        }
        //formatting
        if ($format){
            $data['unit_price'] = $product->formatCurrency($data['unit_price']);
            $data['usual_price'] = $product->formatCurrency($data['usual_price']);
            $data['offer_price'] = $product->formatCurrency($data['offer_price']);
        }
        //logTrace(__METHOD__.' product='.$product->id.', quantity='.$quantity.', campaign='.(is_object($campaign)?$campaign->id:'array'),$data);
        return $data;
    } 
    /**
     * Wrapper of checkProductPrice but returning data element 'usual_price' only
     * 
     * @param type $product
     * @param type $quantity
     * @param type $format
     * @return type
     */
    public function checkProductUsualPrice($product,$quantity=1,$format=true)
    {
        $data = $this->checkProductPrice($product,$quantity,null,$format);
        return $data['usual_price'];
    }    
    /**
     * Wrapper of checkProductPrice but returning data element 'offer_price' only
     * 
     * @param type $product
     * @param type $quantity
     * @param mixed $campaign Can be campaign object, campaign id, or campaign key params that required to compute price
     * @param type $format
     * @return type
     */
    public function checkProductOfferPrice($product,$quantity=1,$campaign=null,$format=true)
    {
        $data = $this->checkProductPrice($product,$quantity,$campaign,$format);
        return $data['offer_price'];
    }   
    /**
     * Calculate offer price
     * @param type $type
     * @param type $discount
     * @param type $unit_price
     * @param type $quantity
     * @return real
     * @throws CException
     */
    public function calculateOfferPrice($type,$discount,$unit_price,$quantity=1)
    {
        switch ($type) {
            case Campaign::OFFER_PERCENTAGE:
                $price = ($quantity * $unit_price) * (1-$discount/100);
                break;
            case Campaign::OFFER_AMOUNT:
                $price = $quantity * $unit_price - $discount;
                break;
            case Campaign::OFFER_FREE:
                $price = 0.0;
                break;
            case Campaign::OFFER_FREE_SHIPPING:
                $price = $quantity * $unit_price;//price remain unchanged as offer is on shipping fee
                break;
            default:
                throw new CException(Sii::t('sii','Unsupported campaign offer type'));
        }
        return round($price,2);
    }  
    /**
     * Return product model
     * @param Product $product If it is already instance of Product, return back;
     * @return \Product
     * @throws CException
     */
    private function _getProduct($product)
    {
        if ($product instanceof Product)
            return $product;
        $model = Product::model()->findByPk($product);
        if ($model==null)
            throw new CException(Sii::t('sii','Product not found'));
        return $model;
    }
    /**
     * Return campaign model
     * @param CampaignBga $campaign If it is already instance of CampaignBga, return back;
     * @return \CampaignBga
     * @throws CException
     */
    private function _getCampaign($campaign,$type)
    {
        switch ($type) {
            case Campaign::BGA:
                if ($campaign instanceof CampaignBga)
                    return $campaign;
                $model = CampaignBga::model()->findByPk($campaign);
                break;
            case Campaign::SALE:
                if ($campaign instanceof CampaignSale)
                    return $campaign;
                $model = CampaignSale::model()->findByPk($campaign);
                break;
            case Campaign::PROMOCODE:
                if ($campaign instanceof CampaignPromocode)
                    return $campaign;
                $model = CampaignPromocode::model()->findByPk($campaign);
                break;
            default:
                throw new CException(Sii::t('sii','Campaign type undefined'));
        }
        if ($model==null)
            throw new CException(Sii::t('sii','Campaign not found'));
        return $model;
    }
    /**
     * Return shop offer query condition 
     * @param integer $shop_id Shop id
     * @return string
     */
    private function _getShopOfferCondition($shop_id)
    {
        $condition = 'shop_id='.$shop_id;
        return $condition;
    }
}
