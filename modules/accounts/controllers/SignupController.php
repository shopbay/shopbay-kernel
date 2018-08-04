<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.SignupForm');
/**
 * Description of SignupController
 * This controller extends directly from Controller, and bypass AuthenticatedController and AccountBaseController
 * 
 * @author kwlok
 */
class SignupController extends SController 
{
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
    }     
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'accounts',
                'pathAlias'=>'accounts.assets',
            ],
        ];
    }
    /**
     * IMPORTANT NOTE: This controller is not including "subscription" filter
     * @return array action filters
     */
    public function filters()
    {
        $filters = [
            [Yii::app()->ctrlManager->pageTitleSuffixFilter,'useShopName'=>true],//when shop scope is true
        ];
        $filters = array_merge($filters,Yii::app()->filter->rules);
        foreach ($filters as $key => $value) {
            if ($value=='subscription')
                unset($filters[$key]);
        }
        $filters = array_merge($filters,['accessControl']);
        logTrace(__METHOD__,$filters);
        return $filters;
    }       
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return [
            ['allow',  
                'actions'=>['captcha','index','getform','resend','complete','customer'],
                'users'=>['*'],
            ],
            ['allow', // allow authenticated user to perform actions
                'actions'=>[],//nothing
                'users'=>['@'],
            ],
            ['deny',  // deny all users
                'users'=>['*'],
            ],
        ];        
    }    
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha'=>[
                'class'=>'CCaptchaAction',
                'backColor'=>0xFFFFFF,
                'testLimit'=>1,
            ],
            'customer'=>[
                'class'=>'common.modules.accounts.actions.SignupCustomerAction',
            ],
            'complete' => [
                'class'=>'common.modules.accounts.actions.SignupCompleteAction',
            ],
        ];
    }
    /**
     * Get signup form in JSON/JSONP format
     */
    public function actionGetForm()
    {
        $model = new SignupForm;
        if (isset($_GET['callback'])){
            header('Content-type: application/javascript');
            $data = [
                'container'=>isset($_GET['container'])?$_GET['container']:'',
                'action'=>isset($_GET['action'])?$_GET['action']:'',
                'html'=>$this->renderPartial('_form',['model'=>$model],true),
            ];
            echo $_GET['callback'].'('.CJSON::encode($data).')';
        }
        else {
            header('Content-type: application/json');
            echo CJSON::encode($this->renderPartial('_form',['model'=>$model],true));
        }
        Yii::app()->end();      
    }
    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() 
    {
        $this->setPageTitle(Sii::t('sii','Signup'));
        
        $form = new SignupForm('FullForm');

        if(isset($_POST['ajax']) && $_POST['ajax']==='signup-form')
        {
            //Bugfix: should not validate 'verify_code', else its value will change by internal calls
            echo CActiveForm::validate($form,['name','email','password']);
            Yii::app()->end();
        }

        if(isset($_POST['SignupForm'])){
            
            try {
                
                $form->attributes=$_POST['SignupForm'];
    
                $this->module->serviceManager->signup($form);
            
                header('Content-type: application/json');

                $this->redirect(url('account/signup/complete',['email'=>$form->email]));

                unset ($_POST);

                Yii::app()->end();
                
                
            } catch (CException $e) {
                $form->unsetAttributes(['password','confirmPassword']);
                user()->setFlash(get_class($form),[
                    'message'=>$e->getMessage(),
                    'type'=>'error',
                    'title'=>null,
                ]);
            }            

        }

        if ($this->exceedCapacity())
            $this->render('hold');
        else
            $this->render('index',['model'=>$form]);
    }
    /**
     * Resend activation string
     * @param type $email
     */
    public function actionResend($email=null)
    {
        if (isset($email)){
            
            try {
                
                $this->module->serviceManager->resendActivationEmail($email);
            
                $this->render('resend',['email'=>$email]);
                   
                Yii::app()->end();
                
                
            } catch (CException $e) {
                
                user()->setFlash('complete',[
                        'message'=>$e->getMessage(),
                        'type'=>'error',
                        'title'=>null,
                ]);
                $this->render('complete',['email'=>$email]);
                
                Yii::app()->end();
            }            
        }
        throwError403(Sii::t('sii','Unauthorized Access'));  
    }
    /**
     * Check if sign up has reached capacity
     * @return boolean
     */
    protected function exceedCapacity()
    {
        $totalSignup = Account::model()->count();
        return $totalSignup > Config::getSystemSetting('signup_capacity');
    }
}
