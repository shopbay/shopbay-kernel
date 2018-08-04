<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SErrorController
 *
 * @author kwlok
 */
class SErrorController extends SController 
{
    CONST FORMAT_JSON = 'json';
    CONST FORMAT_HTML = 'html';
    /*
     * Error response format
     */
    public $errorFormat = self::FORMAT_HTML;//default
    
    public $error;
    
    public $errorView = 'common.views.error.error';

    public $maintenanceView = 'common.views.error.maintenance';
    /**
     * Force logout for error code found
     * @var type 
     */
    public $forceLogout = [];
    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->registerCommonFiles();
        $this->registerFormCssFile();
        $this->registerJui();
        $this->registerFontAwesome();
        $this->getOwner()->registerMaterialIcons();
        $this->registerCssFile('application.assets.css','application.css');
        $this->error = Yii::app()->errorHandler->error;
    }     
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>Yii::app()->id,
                'pathAlias'=>'application.assets',
            ],
        ];
    }      
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function beforeAction($action)
    {
        $this->pageTitle = $this->action->id;
        if (in_array($action->id, $this->forceLogout))
            Yii::app()->user->logout();//forced logout to destory session variables
        return true;
    }    
    /**
     * This is the action to handle external exceptions.
     */
    public function actionIndex() 
    {
        $this->action404();
    }
    /**
     * This is the action to handle external exceptions.
     */
    public function action400() 
    {
        $response = [
            'code'=>400,
            'messages'=>[
                Sii::t('sii','We are sorry. Your request cannot be fulfilled due to bad syntax.'),
                YII_DEBUG?$this->errorMessage:'',
            ]
        ];
        $this->sendResponse(400,$response);
        Yii::app()->end();
    }    
    
    public function action401() 
    {
        $response = [
            'code'=>401,
            'messages'=>[
                Sii::t('sii','We are sorry. You are not permitted to proceed this request.'),
                YII_DEBUG?$this->errorMessage:'',
            ]
        ];
        $this->sendResponse(401,$response);
        Yii::app()->end();
    }    
    
    public function action403() 
    {
        $response = [
            'code'=>403,
            'messages'=>[
                Sii::t('sii','We are sorry. Your request is denied.'),
                YII_DEBUG?$this->errorMessage:'',
            ]
        ];
        $this->sendResponse(403,$response);
        Yii::app()->end();
    }    
    
    public function action404() 
    {
        $response = [
            'code'=>404,
            'messages'=>[
                Sii::t('sii','We are sorry. The page cannot be found.'),
                YII_DEBUG?$this->errorMessage:'',
            ]
        ];
        $this->sendResponse(404,$response);
        Yii::app()->end();
    }     
    
    public function action500() 
    {
        $response = [
            'code'=>500,
            'messages'=>[
                Sii::t('sii','We are sorry. We are unable to process your request at this time.'),
                Sii::t('sii','The problem is most likely temporary and will be fixed soon.'),
            ]
        ];
        $this->sendResponse(500,$response);
        Yii::app()->end();
    }      
    
    public function actionMaintenance() 
    {
        $this->render($this->maintenanceView);
        Yii::app()->end();
    }      

    public function getErrorMessage()
    {
        return $this->error['message'];
    }
    /**
     * Send http response according to response format
     * @param type $statusCode
     * @param type $response
     */
    protected function sendResponse($statusCode,$response)
    {
        if ($this->errorFormat==self::FORMAT_JSON){
            header('Content-type: application/json');
            http_response_code($statusCode);
            echo CJSON::encode($response);
        }
        else
            $this->render($this->errorView,$response);
    }
}