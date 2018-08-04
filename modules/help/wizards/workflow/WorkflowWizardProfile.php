<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardProfile');
/**
 * Description of WorkflowWizardProfile
 *
 * @author kwlok
 */
class WorkflowWizardProfile extends WizardProfile 
{
    /*
     * List of profiles supported
     */
    const REMINDER = 'WorkflowWizardReminderProfile';//a general workflow reminder profile to merchants
    /**
     * Profile constructor
     * @param type $id
     * @param type $role
     * @param type $modelFilterMethod
     */
    public function __construct($id,$role,$modelFilterMethod)
    {
        parent::__construct($id);
        $this->setRole($role);
        $this->setModelFilterMethod($modelFilterMethod);
    }     
    /**
     * @return behaviors
     */
    public function behaviors()
    {
        return array(
            'class'=>'common.modules.help.wizards.workflow.WorkflowWizardBehavior',
        );
    }        
}
