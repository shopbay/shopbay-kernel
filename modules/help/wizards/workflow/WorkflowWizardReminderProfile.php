<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.workflow.WorkflowWizardProfile');
/**
 * Description of WorkflowWizardReminderProfile
 *
 * @author kwlok
 */
class WorkflowWizardReminderProfile extends WorkflowWizardProfile
{
    /**
     * Profile constructor
     * @param type $id
     * @param type $role
     * @param type $modelFilterMethod
     */
    public function __construct($id,$role,$modelFilterMethod)
    {
        parent::__construct($id,$role,$modelFilterMethod);
        $this->setName(Sii::t('sii','Gentle Reminder'));
        $this->setIcon('<i class="fa fa-bell"></i>');
        $this->setTheme('notice');
        $this->addReminders();
    }     
    /**
     * Add reminders
     */
    public function addReminders()
    {
        $reminderDataProvider = TaskBaseController::getReminderDataProvider($this->getRole(),$this->getModelFilterMethod());
        if ($reminderDataProvider->getItemCount()>0){
            foreach ($reminderDataProvider->data as $data) {
                $this->addRequirement($this->reminderItem($data));
            }
        }
    }
    /**
     * Reminder item 
     * @param type $data
     * @return type
     */
    public function reminderItem($data)
    {
        $item = Sii::t('sii','You has {n} {object} pending {actionLink}.|You have {n} {object}s pending {actionLink}.',
        //$item = Sii::t('sii','You has {n} {object} pending {actionLink}, or {hint}.|You have {n} {object}s pending {actionLink}, or {hint}.',
            array($data['count'],'{object}'=>strtolower($data['object']),'{actionLink}'=>$data['actionLink'],
                  '{hint}'=>TaskBaseController::getHint($data['class'], $data['action'], strtolower($data['object']), true),
        ));
        return $item;        
    }
}
