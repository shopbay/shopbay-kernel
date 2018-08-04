<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.EmailForm');
Yii::import('common.modules.accounts.models.PasswordForm');//need this in view file
/**
 * Description of ChangeEmailAction
 *
 * @author kwlok
 */
class ChangeEmailAction extends CAction
{
    public $accountType = 'Account';
    public $emailCondition = [];
    
    public function run()
    {
        if (!user()->isAuthorizedActivated)
            throwError403 (Sii::t('sii','Unauthorized Access'));
        
        $this->controller->setPageTitle(Sii::t('sii','Change Email'));

        $form = new EmailForm();
        $form->setAccountType($this->accountType);
        if (!empty($this->emailCondition))
            $form->setEmailCondition($this->emailCondition);
            
        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='email-form')
        {
            echo CActiveForm::validate($form,array('email','cemail'));
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['EmailForm']))
        {
            try {
                
                $form->attributes=$_POST['EmailForm'];

                $this->controller->module->serviceManager->changeEmail(user()->getId(),$form);
            
                $message = Sii::t('sii','Email is changed successfully. You are required to re-activate your account at your next login.');
                $message .= '<br>'.Sii::t('sii','A new activation token key is sent to your new mailbox <em>{email}</em>',array('{email}'=>$form->email));
                    
                user()->setFlash(get_class($form),array(
                        'message'=>$message,
                        'type'=>'success',
                        'title'=>Sii::t('sii','Change Email')));
                
                user()->resetEmail($form->email);
                
                $form->unsetAttributes();
                
            } catch (CException $e) {
                user()->setFlash(get_class($form),array(
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>null));
            }            
            
        }

        $form->unsetAttributes(array('password'));//always have to re-enter password for new submission

        // display the email form
        $this->controller->render('email',array('form'=>$form));

    }
}
