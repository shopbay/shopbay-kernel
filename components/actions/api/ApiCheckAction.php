<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.ReadAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiCheckAction
 *
 * @author kwlok
 */
class ApiCheckAction extends ReadAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'POST';
    /**
     * @var string The permission to check; This property must be set.
     */
    public $permission;
    /**
     * @var string Redirect url when has no permission. Defaults to null
     */
    public $redirectUrlOnRejection;
    /**
     * @var string Return json response url when has no permission. Defaults to false
     */
    public $jsonResponseOnRejection = false;
    /**
     * @var Exceptoin throw exception when has no permission. Defaults to false
     */
    public $throwExceptionOnRejection = false;
    /**
     * @var boolena refresh to force find access token
     */
    public $refreshAccessToken = true;
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->apiRoute = '/subscriptions/check';
        $this->httpPostField = true;
        $this->traitInit();
        if (!isset($this->permission))
            throw new CHttpException(500,Sii::t('sii','Permission not defined'));    
    }   
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        $this->findAccessToken($this->refreshAccessToken);
        $this->queryParams = '/'.$this->permission;
        //set raw body fields
        $this->postFields = json_encode($this->postFields);
        $this->execCurl($this->getAuthBearerHeader());
    } 
    
    public function onSuccess($response,$httpCode)
    {
        //user has permission and check passed! 
        //do nothing and pass on to caller to continue 
        logTrace(__METHOD__.' ok! $httpCode='.$httpCode,$response);
    }

    public function onError($error, $httpCode) 
    {
        logError(__METHOD__.' Hit error! code '.$httpCode, $error);

        $title = isset($this->flashTitle)?$this->flashTitle:Sii::t('sii','Service Not Available');
        $message = isset($this->flashMessage)?$this->flashMessage:Sii::t('sii','You have not subscribed to this service: {service}');
        
        //handle upper limit exception, and catch the limit value
        if ($httpCode==403 && isset($error->message) && strstr($error->message,'Storage')!=false){
            $message = isset($this->flashMessage)?$this->flashMessage:Sii::t('sii','Storage is full: {limit}');
            $message = str_replace ('{limit}', $error->message, $message);
        }
        if ($httpCode==403 && isset($error->message) && strstr($error->message,'Upper limit')!=false)
            $message = str_replace ('{limit}', $error->code, $message);
        if ($httpCode==401 && isset($error->message))
            $message = str_replace ('{service}', Sii::t('sii',$error->message), $message);
        if ($httpCode==500 && isset($error->message))
            $message = Sii::t('sii',$error->message);
        
        if ($this->throwExceptionOnRejection){
            throw new CException($message);
        }
        elseif ($this->jsonResponseOnRejection){
            header('Content-type: application/json');
            echo CJSON::encode([
                'status'=>'serviceNotAvailable',
                'message'=>$message,
            ]);
        }
        else {
            logError(__METHOD__.' Http code '.$httpCode.' with redirectUrlOnRejection '.$this->redirectUrlOnRejection, $error, false);
            
            $message .= '<div style="margin:10px 0px 5px;">'.CHtml::link(Sii::t('sii','Click here to upgrade plan'),url('plans/subscription'),['style'=>'color:black']).'</div>';
            user()->setFlash(isset($this->flashId)?$this->flashId:$this->model,[
                'message'=>$message,
                'type'=>'error',
                'title'=>$title,
            ]);
            $this->controller->redirect($this->redirectUrlOnRejection);
        }
        
        Yii::app()->end();
    }

}
