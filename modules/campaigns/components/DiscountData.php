<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.components.CampaignSaleDiscountData");
Yii::import("common.modules.campaigns.components.CampaignPromocodeDiscountData");
/**
 * Description of DiscountData
 *
 * @author kwlok
 */
class DiscountData extends CComponent
{
    public $discount = 0;//normally is -ve value
    public $total = 0;//discount total value (not order total price)
    public $has_sale = false;
    public $sale_data;
    public $has_promo = false;
    public $promo_data;
    public $free_shipping = false;//default to false; True means shipping is free
    public $shipping_data = array('free'=>false,'by_sale'=>false,'by_promo'=>false);
    /**
     * Check if has discount
     */
    public function hasDiscount()
    {
        return $this->total < 0;//total is -ve
    }
    /**
     * Get discount rate
     */
    public function getRate($base)
    {
        if ($base==0)
            return 0;
        else   
            return $this->total/$base;
    }
    /**
     * Increment total by $discount
     */
    public function touchTotal()
    {
        $this->total += $this->discount;
    }
    
    public function createSaleData($offerPrice,$discountText,$campaignData,$freeshipping=false)
    {
        $s = new CampaignSaleDiscountData();
        $s->offer_price = $offerPrice;
        $s->has_offer = $freeshipping?false:true;
        $s->free_shipping = $freeshipping;
        $s->discount = $this->discount;
        $s->discount_text = $discountText;
        $s->discount_tip = $campaignData['tip'];
        $s->campaign = $campaignData;   
        $this->sale_data = $s->toArray();
        if ($freeshipping){
            $this->shipping_data['free'] = true;
            $this->shipping_data['by_sale'] = true;
        }
    }
    
    public function createPromoData($offerPrice,$discountText,$campaignData,$promocode,$freeshipping=false)
    {
        $p = new CampaignPromocodeDiscountData();
        $p->offer_price = $offerPrice;
        $p->has_offer = true;
        $p->discount = $this->discount;
        $p->discount_text = $discountText;
        $p->discount_tip = $campaignData['tip'];
        $p->campaign = $campaignData;
        $p->promocode = $promocode;
        $this->promo_data = $p->toArray();
        if ($freeshipping){
            $this->shipping_data['free'] = true;
            $this->shipping_data['by_promo'] = true;
        }
    }    
    /**
     * This packages a json data structure to store discount information (all breakdown)
     * @return string json encoded
     */
    public function packageData()
    {
        return json_encode(array(//store discount breakdown
            'total'=>$this->total,
            'has_sale'=>$this->has_sale,
            'sale_data'=>$this->sale_data,//including campaign info
            'has_promo'=>$this->has_promo,
            'promo_data'=>$this->promo_data,//including campaign info
            'free_shipping'=>$this->free_shipping,//true or false
            'shipping_data'=>$this->shipping_data,//including free shipping info
        ));
    } 
    /**
     * Bulk assign data
     * @param type $total
     * @param type $has_sale
     * @param type $sale_data
     * @param type $has_promo
     * @param type $promo_data
     * @param type $free_shipping
     */
    public function assignData($total,$has_sale,$sale_data,$has_promo,$promo_data,$free_shipping=false)
    {
        $this->total = $total;
        $this->has_sale = $has_sale;
        $this->sale_data = $sale_data;
        $this->has_promo = $has_promo;
        $this->promo_data = $promo_data;
        $this->free_shipping = $free_shipping;
    }
}
