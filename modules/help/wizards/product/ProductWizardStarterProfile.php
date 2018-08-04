<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.product.ProductWizardProfile');
/**
 * Description of ProductWizardStarterProfile
 *
 * @author kwlok
 */
class ProductWizardStarterProfile extends ProductWizardProfile
{
    /**
     * Profile constructor
     * @param Product $product Product model
     */
    public function __construct($product)
    {
        parent::__construct(__CLASS__,$product);
        $this->setName(Sii::t('sii','Product Starter Guide').': '.$product->localeName(user()->getLocale()));
        $this->addInventoryRequirements();
        $this->addAttributeRequirements();
    }   
    /**
     * Inventory requirement specifications
     */    
    public function addInventoryRequirements()
    {
        $this->addRequirement([
          'status'=>!$this->product->hasInventory(),
          'advice'=>Sii::t('sii','Product need to have inventory to go online.'),
          'action'=>$this->formatLink('inventories/management/create',Sii::t('sii','Add inventory now.')),
        ]); 
    }
    /**
     * Product attribute advices specifications
     */    
    public function addAttributeRequirements()
    {
        $this->addRequirement([
          'status'=>count($this->product->attrs)==0,
          'advice'=>Sii::t('sii','If product has attributes, product inventory will be setup according to attributes. Hence attribute must be setup first before inventory.'),
          'action'=>$this->formatLink('product/attribute',Sii::t('sii','Add attribute now.')),
        ]); 
    }    
}