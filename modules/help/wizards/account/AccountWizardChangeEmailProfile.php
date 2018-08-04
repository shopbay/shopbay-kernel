<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.account.AccountWizardProfile');
/**
 * Description of AccountWizardChangeEmailProfile
 *
 * @author kwlok
 */
class AccountWizardChangeEmailProfile extends AccountWizardProfile
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
        $message = Sii::t('sii','Changing email address is effectively changing your login id, and after changing you current email address will be invalid for login.<br>');
        $message .= Sii::t('sii','You are required re-activate your account using new email address to login.');
        $this->addRequirement($message);
    }
}
