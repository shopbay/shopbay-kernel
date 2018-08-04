<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends AccountBaseController 
{
    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','Account');
        $this->modelType = 'AccountProfile';
        //-----------------
        // @see ImageControllerTrait
        $this->setSessionActionsExclude([
            'update',//this is required as avatar image is finally saved from actionUpdate
        ]);        
        // check if module requisites exists
        $missingModules = $this->getModule()->findMissingModules();
        if ($missingModules->getCount()>0)
            user()->setFlash($this->getId(),array('message'=>Helper::htmlList($missingModules),
                                            'type'=>'notice',
                                            'title'=>'Missing Module'));  
        //-----------------
        // Exclude following actions from rights filter 
        // @see ImageControllerTrait
        $this->rightsFilterActionsExclude = $this->getRightsFilterImageActionsExclude([
            'captcha',
            'forgotpassword',
            'stateget',
            $this->imageUploadAction, 
        ]);
        //-----------------//        
    }
    /**
     * IMPORTANT NOTE:
     * This controller is not including 'subscription' filter as 
     * Account management is made available even without subscription
     * @return array action filters
     */
    public function filters()
    {
        $filters = parent::filters();
        foreach ($filters as $key => $value) {
            if ($value=='subscription')
                unset($filters[$key]);
        }
        return $filters;
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge($this->imageActions(),[
            'index'=>[
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'viewFile'=>'index',
                'loadModelMethod'=>'prepareModel',
            ],  
            'update'=>[
                'class'=>'common.components.actions.UpdateAction',
                'model'=>$this->modelType,
                'loadModelMethod'=>'prepareModel',
                'loadModelAttribute'=>null,
                'setAttributesMethod'=>'setModelAttributes',
                'viewFile'=>'index',
            ],            
            'networks'=>[
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
                'loadModelMethod'=>'prepareModel',
                'viewFile'=>'networks',
            ],  
            // captcha action renders the CAPTCHA image displayed on the form page
            'captcha'=>[
                'class'=>'CCaptchaAction',
                'backColor'=>0xFFFFFF,
                'testLimit'=>1,
            ],
            'stateget'=>[
                'class'=>'common.components.actions.AddressStateGetAction',
            ],            
            'password'=>[
                'class'=>'common.modules.accounts.actions.ChangePasswordAction',
            ],             
            'email'=>[
                'class'=>'common.modules.accounts.actions.ChangeEmailAction',
            ],               
            'forgotpassword'=>[
                'class'=>'common.modules.accounts.actions.ResetPasswordAction',
            ],               
        ]);
    }
    
    public function prepareModel()
    {
        $type = $this->modelType;
        $model = $type::model()->mine()->find();
        if($model===null)
            throw new CHttpException(404,Sii::t('sii','The requested page does not exist.'));
        if($model->account->address===null){
            $model->account->address = new AccountAddress();
            $model->account->address->account_id = $model->account->id;
        }
        return $model;
    }

    public function setModelAttributes($model)
    {
        if (isset($_POST['AccountProfile'])) {
            $model->attributes=$_POST['AccountProfile'];
            if (isset($_POST['AccountAddress'])){
                $model->account->address->attributes=$_POST['AccountAddress'];
            }
            return $model;
        }
        throwError400(Sii::t('sii','Bad Request'));
    }      
    /**
     * This action performs account closure
     */
    public function actionClose()
    {
        try {

            $this->module->serviceManager->close(user()->account);

            $this->render('closed');

            Yii::app()->end();

        } catch (CException $e) {
            user()->setFlash($this->modelType,array(
                'message'=>$e->getMessage(),
                'type'=>'error',
                'title'=>null));
        }

        $this->run('index');
    }
}