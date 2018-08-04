<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.PasswordForm');
/**
 * Description of ChangePasswordAction
 *
 * @author kwlok
 */
class ChangePasswordAction extends CAction
{
    public $accountType = 'Account';
    
    public function run()
    {
        if (!user()->isAuthorizedActivated)
            throwError403 (Sii::t('sii','Unauthorized Access'));
                
        $this->controller->setPageTitle(Sii::t('sii','Change Password'));

        $form = new PasswordForm();
        $form->setAccountType($this->accountType);
        // if it is ajax validation request
        // currently disabled as genenrally disable ajax validation across system
        if(isset($_POST['ajax']) && $_POST['ajax']==='password-form'){
            echo CActiveForm::validate($form,array('newPassword','confirmPassword'));
            Yii::app()->end();
        }
        
        if(isset($_POST['PasswordForm'])){
            
            try {
                
                $form->attributes=$_POST['PasswordForm'];

                $this->controller->module->serviceManager->changePassword(user()->getId(),$form);
            
                user()->setFlash(get_class($form),array(
                        'message'=>Sii::t('sii','Password changed successfully.'),
                        'type'=>'success',
                        'title'=>Sii::t('sii','Change Password')));
                
            } catch (CException $e) {
                user()->setFlash(get_class($form),array(
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>Sii::t('sii','Change Password')));
            }
        }
        
        $form->unsetAttributes();
        //always have to re-enter password for new submission
        //$form->unsetAttributes(array('currentPassword','newPassword','confirmPassword'));
        
        $this->controller->render('password',array('form'=>$form));
        
    }
}
