<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardContainer');
Yii::import('common.modules.help.wizards.workflow.*');
/**
 * Description of WorkflowWizard
 *
 * @author kwlok
 */
class WorkflowWizard extends WizardContainer
{
    /**
     * Wizard constructor
     * @param mixed $profile id
     */
    public function __construct($profile,$role,$modelFilterMethod=null)
    {
        parent::__construct(__CLASS__);
        $this->setProfile(new $profile($profile,$role,$modelFilterMethod));
        $this->setName($this->profile->name);
    }
}
