<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.shipping.ShippingWizardProfile');
/**
 * Description of ShippingWizardStarterProfile
 *
 * @author kwlok
 */
class ShippingWizardStarterProfile extends ShippingWizardProfile
{
    /**
     * Profile constructor
     * @param Shop $shop shop model
     */
    public function __construct($shop)
    {
        parent::__construct(__CLASS__,$shop);
        $this->setName(Sii::t('sii','Shipping Starter Guide').': '.$shop->parseName(user()->getLocale()));
        $this->addZoneRequirements();
    }   
    /**
     * Zone requirement specifications
     */    
    public function addZoneRequirements()
    {
        $prerequisite = $this->shop->searchZones()->itemCount==0;
        $this->addRequirement([
          'status'=>$prerequisite,
          'advice'=>Sii::t('sii','You need to first setup the zone areas that you want to ship your products.'),
          'action'=>$this->formatLink('shippings/zone/create?sid='.$this->shop->id),
        ]); 
    }
    
}