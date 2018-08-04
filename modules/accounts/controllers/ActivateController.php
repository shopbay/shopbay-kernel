<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ActivateController
 * This controller extends directly from AuthenticatedController, and bypass AccountBaseController
 * 
 * @author kwlok
 */
class ActivateController extends AuthenticatedController 
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return [
            'rights - presignup', 
        ];
    }
    /**
     * Activate a account; 
     * User need to verify both username/email and password to complete activation
     * 
     * Activation url account/activate?token=base64_encode[activate_str]
     * @see Account::getActivationUrl()
     */
    public function actionIndex()
    {
        logTrace(__METHOD__.' Activating account...');
        
        if(isset($_REQUEST['token'])) {
            
            logInfo(__METHOD__.' Activation token='.$_REQUEST['token']);
            
            try {
                
                $this->module->serviceManager->activate($_REQUEST['token']);
                user()->setFlash('welcome',array(
                    'message'=>Sii::t('sii','Welcome! Your account is activated successfully.'),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Account Activation')));
                $this->redirect(url('/welcome'));//on purpose to change url in browser
                Yii::app()->end();
                
            } catch (CException $e) {
                logError(__METHOD__.' error='.$e->getMessage().' code='.$e->getCode().' >> '.$e->getTraceAsString(),[],false);
                $this->redirect(url('/signin?__iu='.base64_encode($e->getCode())));//on purpose to show error
                Yii::app()->end();
            }
        }
        throwError403(Sii::t('sii','Unauthorized Access'));  
    }
    /**
     * Activate a pre-signup account; (for socila network oauth users)
     * User need to verify email and set new password to complete activation
     * 
     * Activation url account/activate/presignup?token=base64_encode[activate_str]&network=[networkname]
     * @see Account::getActivationUrl()
     */
    public function actionPresignup()
    {
        $this->layout='common.views.layouts.site';//layout of SController
        $this->getModule()->registerFormCssFile();
        $this->getModule()->registerCssFile('application.assets.css','application.css');

        $form = new PreSignupForm();

        if (isset($_GET['token']) && isset($_GET['network'])) {
            logInfo(__METHOD__.' Activation token='.$_GET['token'].' and network='.$_GET['network']);
            try {
                $email = $this->module->serviceManager->activate($_GET['token'],true,$_GET['network']);//do activation validation only
                $form->email = $email;
                $form->network = $_GET['network'];
                $form->token = $_GET['token'];
                
            } catch (CException $e) {
                logError(__METHOD__.' error='.$e->getMessage().' code='.$e->getCode(),array(),false);
                if ($e->getCode()==IdentityUser::ERROR_ACTIVATION_TOKEN){
                    throwError404(Sii::t('sii','Page not found'));
                    Yii::app()->end();
                }
                else {
                    user()->setFlash(get_class($form),array(
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>null));
                }
            }
        }
        elseif (isset($_POST['PreSignupForm'])){
    
            logTrace(__METHOD__.' Completing registration with pre-signup account...');
            try {
                
                $form->attributes = $_POST['PreSignupForm'];
    
                logTrace(__METHOD__.' form attributes',$form->attributes);
            
                $form = $this->module->serviceManager->activatePresignup($form);
            
                user()->setFlash('welcome',array(
                    'message'=>Sii::t('sii','Welcome! Your account is activated successfully.'),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Account Activation')));
                    
                $this->redirect(url('/welcome'));//on purpose to change url in browser

                unset($_POST);
                
                Yii::app()->end();
                
                
            } catch (CException $e) {
                $form->unsetAttributes(array('password','confirmPassword'));
                user()->setFlash(get_class($form),array(
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>null));
            }            
        }
        else {
            throwError403(Sii::t('sii','Unauthorized Access'));  
            Yii::app()->end();
        }
        
        //render presignup form
        $this->preparePresignupMessages($form->network);
        $this->render('presignup',array('model'=>$form));
        Yii::app()->end();
        
    }
    /**
     * For scenario when user is registered only but not yet activated 
     * Normally users are coming from social network
     * @param type $network
     * @param type $messageId
     */
    public function preparePresignupMessages($network,$messageId=[])
    {
        if (empty($messageId))
            $messageId = $this->getPresignupMessagesId();
        logTrace(__METHOD__.' flash id',$messageId);
        
        Yii::import('common.modules.accounts.oauth.widgets.OAuthWidget');
        $networkIcon = $this->widget('OAuthWidget',array(
                                'disableLink'=>true,
                                'providers'=>array($network),
                                'iconOnly'=>false,
                        ),true);
        if (isset($messageId[0]))
            user()->setFlash($messageId[0],array(
                'message'=>Sii::t('sii','You need an {app} account to link to your {network} account, and as a backup account as well in the event that you have decided not to use network account anymore, you can always fall back to this account without losing access to {app}.',
                                array('{app}'=>app()->name,'{network}'=>$networkIcon)),
                'disableCloseButton'=>true,
                'type'=>'success',
                'title'=>Sii::t('sii','Thanks for choosing {app}. You are one step away from completing registration.',array('{app}'=>app()->name))));
        if (isset($messageId[1]))
            user()->setFlash($messageId[1],array(
                'message'=>Sii::t('sii','You only need to set password.'),
                'type'=>'notice',
                'icon'=>'<i class="fa fa-info-circle"></i>',
                //'title'=>Sii::t('sii','Create {app} Account',array('{app}'=>app()->name)),
            ));
    }
    
    public function getPresignupMessagesId() 
    {
        return ['presignup','presignup-create-account'];
    }
}