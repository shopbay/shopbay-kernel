<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardProfile');
/**
 * Description of ShopWizardProfile
 *
 * @author kwlok
 */
class ShopWizardProfile extends WizardProfile
{
    /*
     * List of profiles supported
     */
    const STARTER = 'ShopWizardStarterProfile';//a basic profile to have minimal setup for a shop to start business
    /**
     * Profile constructor
     * @param Shop $shop shop model
     */
    public function __construct($id,$shop)
    {
        parent::__construct($id);
        $this->setShop($shop);
    }
    /**
     * @return behaviors
     */
    public function behaviors()
    {
        return array(
            'class'=>'common.modules.help.wizards.shop.ShopWizardBehavior',
        );
    }        
}
