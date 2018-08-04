<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardContainer');
Yii::import('common.modules.help.wizards.account.*');
/**
 * Description of AccountWizard
 *
 * @author kwlok
 */
class AccountWizard extends WizardContainer
{
    /**
     * Wizard constructor
     */
    public function __construct($profile)
    {
        parent::__construct(__CLASS__);
        $this->setProfile(new $profile($profile));
        $this->setName($this->profile->name);
    }
}
