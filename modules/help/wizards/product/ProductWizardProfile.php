<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardProfile');
/**
 * Description of ProductWizardProfile
 *
 * @author kwlok
 */
class ProductWizardProfile extends WizardProfile
{
    /*
     * List of profiles supported
     */
    const STARTER = 'ProductWizardStarterProfile';//a basic profile to have minimal setup for a product to start business
    /**
     * Profile constructor
     * @param Shop $shop shop model
     */
    public function __construct($id,$product)
    {
        parent::__construct($id);
        $this->setProduct($product);
    }
    /**
     * @return behaviors
     */
    public function behaviors()
    {
        return [
            'class'=>'common.modules.help.wizards.product.ProductWizardBehavior',
        ];
    }        
}
