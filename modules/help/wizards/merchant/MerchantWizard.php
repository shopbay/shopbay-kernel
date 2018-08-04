<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardContainer');
Yii::import('common.modules.help.wizards.merchant.*');
/**
 * Description of MerchantWizard
 *
 * @author kwlok
 */
class MerchantWizard extends WizardContainer
{
    /**
     * Wizard constructor
     * @param mixed $profile id
     */
    public function __construct($profile)
    {
        logTrace(__METHOD__,$profile);
        parent::__construct(__CLASS__);
        $this->setProfile(new $profile());
        $this->setName($this->profile->name);
    }
}
