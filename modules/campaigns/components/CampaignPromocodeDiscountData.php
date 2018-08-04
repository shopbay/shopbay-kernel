<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CampaignPromocodeDiscountData
 *
 * @author kwlok
 */
class CampaignPromocodeDiscountData extends CComponent
{
    public $locale;
    public $promocode;
    public $offer_price;
    public $has_offer = false;
    public $discount;
    public $discount_text;
    public $discount_tip;
    public $campaign;
    public $free_shipping = false;//default to false; True means shipping is free
    /**
     * Return data as array
     * @return array
     */
    public function toArray()
    {
        return array(
            'locale'=>user()->getLocale(),
            'promocode'=>$this->promocode,
            'offer_price'=>$this->offer_price,
            'has_offer'=>$this->has_offer,
            'discount'=>$this->discount,
            'discount_text'=>$this->discount_text,
            'discount_tip'=>$this->discount_tip,
            'campaign'=>$this->campaign,
            'free_shipping'=>$this->free_shipping,
        );
    }
    
    public function createCampaignData($campaign)
    {
        if (!($campaign instanceof CampaignPromocode))
            throw new CException('Invalid campaign type');
        
        //scan thru all supported locales at shop level
        $campaignText = new CMap();
        foreach ($campaign->shop->getLanguageKeys() as $language) {
            $campaignText->add($language,$campaign->getCampaignText($language));
        }
        
        $this->campaign = array(
            'id' => $campaign->id,
            'shop' => $campaign->shop_id,
            'type' => get_class($campaign),
            'code' => $campaign->code,
            'offer_value' => $campaign->offer_value,
            'offer_type' => $campaign->offer_type,
            'validity' => $campaign->getValidityText(),
            'text' => $campaignText->toArray(),
            'tip' => $this->discount_tip,            
        );
    }    
}
