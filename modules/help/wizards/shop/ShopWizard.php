<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardContainer');
Yii::import('common.modules.help.wizards.shop.*');
/**
 * Description of ShopWizard
 *
 * @author kwlok
 */
class ShopWizard extends WizardContainer
{
    /**
     * Wizard constructor
     * @param Shop $shop shop model
     */
    public function __construct($shop,$profile)
    {
        parent::__construct(__CLASS__.$shop->id);
        $this->setProfile(new $profile($shop));
        $this->setName($this->profile->name);
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
