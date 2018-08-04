<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.inventory.InventoryWizardProfile');
/**
 * Description of InventoryWizardStarterProfile
 *
 * @author kwlok
 */
class InventoryWizardStarterProfile extends InventoryWizardProfile
{
    /**
     * Profile constructor
     * @param Shop $shop shop model
     */
    public function __construct($shop)
    {
        parent::__construct(__CLASS__,$shop);
        $this->setName(Sii::t('sii','Inventory Starter Guide').': '.$shop->parseName(user()->getLocale()));
        $this->addProductRequirements();
    }   
    /**
     * Product requirement specifications
     */    
    public function addProductRequirements()
    {
        $prerequisite = $this->shop->searchProducts()->itemCount==0;
        $this->addRequirement([
          'status'=>$prerequisite,
          'advice'=>Sii::t('sii','You need to have product first before you can set inventory.'),
          'action'=>$this->formatLink('products/management/create?sid='.$this->shop->id),
        ]); 
    }
    
}