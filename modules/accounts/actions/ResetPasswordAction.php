<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.ResetPasswordForm');
/**
 * Description of ResetPasswordAction
 *
 * @author kwlok
 */
class ResetPasswordAction extends CAction
{
    public $layout = 'common.views.layouts.site';//default is layout of SController
    public $appName;
    
    public $accountType = 'Account';
    public $emailCondition = [];
    
    public function run()
    {
        if (!isset($this->appName))
            $this->appName = Yii::app()->name;
        
        $this->controller->layout = $this->layout;
        $this->controller->setPageTitle(Sii::t('sii','Forgot Password'));

        $form = new ResetPasswordForm();
        $form->unsetAttributes(['email','verify_code']);
        $form->setAccountType($this->accountType);
        if (!empty($this->emailCondition))
            $form->setEmailCondition($this->emailCondition);

        // if it is ajax validation request
        // currently disabled as genenrally disable ajax validation across system
        if(isset($_POST['ajax']) && $_POST['ajax']==='reset-password-form'){
            echo CActiveForm::validate($form,['email']);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['ResetPasswordForm'])) {
            
            try {
                
                $form->attributes=$_POST['ResetPasswordForm'];

                $this->controller->module->serviceManager->resetPassword(user()->getId(),$form,$this->emailCondition);
            
                $this->controller->render('common.modules.accounts.views.management.resetpassword_ack',['email'=>$form->email]);
                
                $form->unsetAttributes();

                unset($_POST);
                
                Yii::app()->end();
                
            } catch (CException $e) {
                user()->setFlash(get_class($form),[
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>null,
                    ]);
            }
        }

        $this->controller->render('resetpassword',['model'=>$form]);
        
    }
}
