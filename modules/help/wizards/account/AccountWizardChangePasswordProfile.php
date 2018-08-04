<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.account.AccountWizardProfile');
/**
 * Description of AccountWizardChangePasswordProfile
 *
 * @author kwlok
 */
class AccountWizardChangePasswordProfile extends AccountWizardProfile
{
    /**
     * Profile constructor
     * @param type $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        $this->setName(Sii::t('sii','Caution'));
        $this->setIcon('<i class="fa fa-exclamation-circle"></i>');
        $this->setTheme('notice');
        $this->addNotices();
    }     
    /**
     * Add notices
     */
    public function addNotices()
    {
        $this->addRequirement(Sii::t('sii','This will not affect your current session, but you are required to use new password for future logins.'));
    }
}
