<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.shop.ShopWizardProfile');
/**
 * Description of ShopWizardStarterProfile
 *
 * @author kwlok
 */
class ShopWizardStarterProfile extends ShopWizardProfile
{
    /**
     * Profile constructor
     * @param Shop $shop shop model
     */
    public function __construct($shop)
    {
        parent::__construct(__CLASS__,$shop);
        $this->setName(Sii::t('sii','Shop Starter Guide').': '.$shop->parseName(user()->getLocale()));
        $this->addShopRequirements();
        $this->addPaymentMethodRequirements();
        $this->addShippingRequirements();
        $this->addProductRequirements();
        $this->setProfileMessage(array(
            'visible'=>$this->shop->offline()||$this->shop->approval(),
            'message'=>Sii::t('sii','Congratulations! You are now one step away from getting your shop ready for business. {action}',array('{action}'=>$this->formatLink('tasks/shop/activate', Sii::t('sii','Bring shop online.')))),
        ));
    }   
    /**
     * Shop requirement specifications
     */
    public function addShopRequirements()
    {
        if ($this->shop->prototype()){
            $this->addRequirement([
                'status'=>$this->shop->prototype(),
                'advice'=>Sii::t('sii','Configure your first shop basic information.'),
                'action'=>$this->formatLink('shops/management/update?id='.$this->shop->id),
            ]);          
        }
        $this->addRequirement([
            'status'=>!$this->shop->hasLogo(),
            'advice'=>Sii::t('sii','Upload your shop logo.'),
            'action'=>$this->formatLink('shops/management/update?id='.$this->shop->id),
        ]);          
    }
    /**
     * Product requirement specifications
     */
    public function addProductRequirements()
    {
        $prerequisite = $this->shop->searchProducts()->itemCount==0;
        $this->addRequirement([
            'status'=>$prerequisite,
            'advice'=>Sii::t('sii','Add your first product.'),
            'action'=>$this->formatLink('products/management/create?sid='.$this->shop->id),
        ]);  
        //if $prerequisite is fullfiled
        if (!$prerequisite && $this->shop->searchProducts(Process::PRODUCT_ONLINE)->itemCount==0) {
            $this->addRequirement([
                'status'=>true,
                'advice'=>Sii::t('sii','Bring your products online.'),
                'action'=>$this->formatLink('tasks/product/activate'),
            ]); 
        }
    }
    /**
     * Payment method requirement specifications
     */
    public function addPaymentMethodRequirements()
    {
        $prerequisite = $this->shop->searchPaymentMethods()->itemCount==0;
        $this->addRequirement([
            'status'=>$prerequisite,
            'advice'=>Sii::t('sii','Tell your customers how to pay your order, e.g. bank transfer, cash on delivery etc.'),
            'action'=>$this->formatLink('payments/management/create?sid='.$this->shop->id),
        ]); 
        //if $prerequisite is fullfiled
        if (!$prerequisite && $this->shop->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE)->itemCount==0) {
            $this->addRequirement([
                'status'=>true,
                'advice'=>Sii::t('sii','Bring your payment methods online.'),
                'action'=>$this->formatLink('tasks/paymentMethod/activate'),
            ]); 
        }
    }
    /**
     * Shipping requirement specifications
     */    
    public function addShippingRequirements()
    {
        $prerequisite = $this->shop->searchShippings()->itemCount==0;
        $this->addRequirement([
          'status'=>$prerequisite,
          'advice'=>Sii::t('sii','Specify how do you want to ship your products when your customers place orders.'),
          'action'=>$this->formatLink('shippings/management/create?sid='.$this->shop->id),
        ]); 
        //if $prerequisite is fullfiled
        if (!$prerequisite && $this->shop->searchShippings(Process::SHIPPING_ONLINE)->itemCount==0) {
            $this->addRequirement([
                'status'=>true,
                'advice'=>Sii::t('sii','Bring your shipping options online.'),
                'action'=>$this->formatLink('tasks/shipping/activate'),
            ]); 
        }
    }
    
}