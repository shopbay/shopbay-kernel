<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardProfile');
/**
 * Description of AccountWizardProfile
 *
 * @author kwlok
 */
class AccountWizardProfile extends WizardProfile 
{
    /*
     * List of profiles supported
     */
    const REMINDER = 'AccountWizardChangePasswordProfile';//a general change password notice profile
    /**
     * Profile constructor
     * @param type $id
     * @param type $role
     * @param type $modelFilterMethod
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }     
}
