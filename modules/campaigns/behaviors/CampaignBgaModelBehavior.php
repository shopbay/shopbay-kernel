<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.behaviors.CampaignBaseModelBehavior");
/**
 * Description of CampaignBgaModelBehavior
 *
 * @author kwlok
 */
class CampaignBgaModelBehavior extends CampaignBaseModelBehavior 
{
    public function getProductsArray($locale=null)
    {
        $data = new CMap();
        foreach (Product::model()->locateShop($this->getOwner()->shop_id)->active()->findAll() as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();        
    } 
    
    public function getShippingsArray($locale=null)
    {
        $data = new CMap();
        foreach (Shipping::model()->mine()->locateShop($this->getOwner()->shop_id)->findAll() as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();
    }    
    
    public function getOfferScenario() 
    {
        if (!$this->getOwner()->hasG()) {
            if ($this->getOwner()->buy_x_qty>1)
                return CampaignBga::X_OFFER_MORE;
            else
                return CampaignBga::X_OFFER;    
        }
        else {
            if ($this->getOwner()->buy_x==$this->getOwner()->get_y)
                return CampaignBga::X_X_OFFER;
            else
                return CampaignBga::X_Y_OFFER;
        }            
    }
    
    public function onOfferFree()
    {
        return $this->getOwner()->offer_type==Campaign::OFFER_FREE;
    }         
    
    public function onOfferXMore()
    {
        return $this->getOwner()->getOfferScenario()==CampaignBga::X_OFFER_MORE;
    }         

    public function onOfferXOnly()
    {
        return $this->getOwner()->getOfferScenario()==CampaignBga::X_OFFER;
    }         
    
    public function hasG()
    {
        return $this->getOwner()->get_y!=null && $this->getOwner()->get_y_qty!=null;
    }       
}
