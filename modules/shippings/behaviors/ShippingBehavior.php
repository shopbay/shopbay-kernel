<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shippings.behaviors.ShippingBaseBehavior");
/**
 * Description of ShippingBehavior
 *
 * @author kwlok
 */
class ShippingBehavior extends ShippingBaseBehavior 
{
    public function getZonesArray($locale=null)
    {
        $data = new CMap();
        foreach (Zone::model()->findAllByAttributes(['shop_id'=>$this->getOwner()->shop_id]) as $model) {
            $data->add($model->id,$model->displayLanguageValue('name',$locale));
        }
        return $data->toArray();
    }    
    
    public function hasTiers()
    {
        return $this->getOwner()->tiers!=null;
    }
    
    public function getTierBase()
    {
        if ($this->hasTiers()){
            foreach ($this->getOwner()->tiers as $tier) {
                return $tier;//return first tier, interested in tier type only
                break;
            }
        }
        else
            return null;
    }    
}
