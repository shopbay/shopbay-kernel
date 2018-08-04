<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SSiteController
 *
 * @author kwlok
 */
class SSiteController extends SController 
{  
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        $this->registerCommonFiles();
        $this->registerFormCssFile();
        $this->registerJui();
    } 
    /**
     * @return array action filters
     */
    public function filters()
    {
        return [
            'accessControl', 
        ];
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
                'actions'=>['locale','error','captcha'],
                'users'=>['*'],
            ],
            //default deny all users anything not specified       
            ['deny',  
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
            ],
        ];
    }
    /**
     * This action changes the site locale
     */
    public function actionLocale()
    {
        if (isset($_POST['language'])){
            $this->setUserLocale($_POST['language']);
        }
        $this->redirect(request()->getUrlReferrer());
    }
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error=Yii::app()->errorHandler->error) {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else {
                if ($error['type']=='TokenExpiredException' && $error['code']==401){
                    logInfo(__METHOD__.' '.$error['message']);
                    user()->deleteApiAccessToken();
                    Yii::app()->user->logout();
                    $this->redirect(url('accounts/authenticate/login',['__expired'=>0]));
                }
                else 
                    $this->forward('error/'.$error['code']);
            }
        }
        else {
            logError(__METHOD__.' Invalid Error Handler');
            throwError500('Internal server error');
        }
    }
    /**
     * Load requested css file in common folder
     * Keep it for future use, if applies
     */
    public function actionCss($script)
    {
        $file = KERNEL.'assets/css/'.$script;
        if (file_exists($file)){
            header('Content-type: text/css');
            $css = file_get_contents($file);
            echo $css;
            Yii::app()->end();
        }
        else 
            throwError404(Sii::t('sii','The requested page does not exist'));
    }
    /**
     * Load requested js file in common folder
     * Keep it for future use, if applies
     */
    public function actionJs($script)
    {
        $file = KERNEL.'assets/js/'.$script;
        if (file_exists($file)){
            header('Content-type: text/javascript');
            //$js = trim ( preg_replace( '/\s+/', ' ', file_get_contents($file) ));
            echo include($file);
            Yii::app()->end();
        }
        else 
            throwError404(Sii::t('sii','The requested page does not exist'));
    }

}
