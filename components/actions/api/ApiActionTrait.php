<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.api.exceptions.*");
/**
 * Description of ApiActionTrait
 *
 * @author kwlok
 */
trait ApiActionTrait 
{
    public $httpContentType = 'application/json';    
    public $httpVerb;    
    public $httpPostField = false;//if true, http raw body will be set
    public $apiVersion = 'v1';    
    public $apiBaseUrl;    
    public $apiRoute;
    public $queryParams;
    public $postFields;
    public $retryAccessToken = false;//when true, will retry to get access token when expired
    public $user;
    private $_m;//model instance
    private $_curl;//curl handler
    /**
     * Init trait
     */
    public function init()
    {
        if (!isset($this->user))
            $this->user = user()->getId();
        
        $this->apiBaseUrl = param('API_DOMAIN');
        if (request()->isSecureConnection)
            $this->apiBaseUrl = 'https://'.$this->apiBaseUrl;
    }
    /**
     * Run the action. Default to "api" mode.
     */
    public function run() 
    {
        $this->init();
        if ($this->validateApiSettings())
            $this->callApi();
    }      
    /**
     * Validate API settings
     */
    public function validateApiSettings() 
    {
        if (!isset($this->httpVerb))
            throw new CHttpException(500,Sii::t('sii','HTTP verb not defined'));    
        if (!isset($this->apiRoute))
            throw new CHttpException(500,Sii::t('sii','API Route not defined'));    
        return true;
    }       
    /**
     * Get HTTP Basic Auth token
     * @param type $account
     * @return type
     */
    protected function getApiEndpoint() 
    {
        $endpoint = $this->apiBaseUrl;        
        if (isset($this->apiVersion))
            $endpoint .= '/'.$this->apiVersion;
        
        $endpoint .= $this->apiRoute;
        
        if (isset($this->queryParams))
            $endpoint .= $this->queryParams;
        logInfo(__METHOD__.' '.strtoupper($this->httpVerb),$endpoint);
        return $endpoint;
    }
    /**
     * Get HTTP Basic Auth header
     * @param type $username
     * @param type $password
     * @return type
     */
    protected function getAuthBasicHeader($username,$password) 
    {
        return 'Basic '.base64_encode($username.':'.$password);
    }
    /**
     * Get HTTP Bearer Auth header
     * @return type
     */
    protected function getAuthBearerHeader() 
    {
        return 'Bearer '.$this->getAccessToken();
    }    
    /**
     * Set api model
     * @param type $model
     */
    protected function setApiModel($model)
    {
        $this->_m = $model;
    }
    /**
     * @return api model
     */
    protected function getApiModel()
    {
        return $this->_m;
    }       
    /**
     * Reset api model
     */
    protected function resetApiModel()
    {
        $this->_m = null;
    } 
    /**
     * Reset curl 
     */
    protected function resetCurl()
    {
        $this->_curl = null;
    } 
    /**
     * Init curl 
     */
    protected function getCurl()
    {
        if (!isset($this->_curl))
            $this->_curl = curl_init();
        return $this->_curl;
    } 
    /**
     * @return array curl options
     */
    protected function getCurlOptions($authHeader=null)
    {
        $optHttpHeader = [
            'cache-control: no-cache',
            'content-type: '.$this->httpContentType,
        ];
        if (isset($authHeader)){
            $optHttpHeader = array_merge($optHttpHeader,['authorization: '.$authHeader]);
        }
        //logTrace(__METHOD__.' optHttpHeader',$optHttpHeader);
        
        $options = [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->httpVerb,
            CURLOPT_HTTPHEADER => $optHttpHeader,
        ];
        if ($this->httpPostField)
            $options[CURLOPT_POSTFIELDS] = $this->parsePostFields();
        return $options;
    }    
    /**
     * Execute curl 
     */
    protected function execCurl($authHeader=null)
    {
        curl_setopt_array($this->curl, $this->getCurlOptions($authHeader));
        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        logTrace(__METHOD__." response HTTP code $httpCode", $response, false);
        $this->parseResponse($response, $httpCode);
        curl_close($this->curl);      
    }        
    /**
     * Parse HTTP response from curl
     * @param type $response
     * @param type $httpCode
     * @throws CException
     */
    protected function parseResponse($response,$httpCode)
    {
        if ($response===false){
            $err = curl_error($this->curl);
            logError(__METHOD__.' error ', $err, false);
            $error = new stdClass();
            $error->message = $err;
            $error->name = Sii::t('sii','System Processing Error');
            $this->onError($error,500);
        }
        elseif ($httpCode >= 400){//4xx Client Error, 5xx Server Error
            $error = json_decode($response);
            logError(__METHOD__.' http code '.$httpCode.' with response errors', $error, false);
            $this->onError($error,$httpCode);
        }
        elseif ($httpCode>=200 && $httpCode<300) {//2xx Success
            $this->onSuccess($response,$httpCode);
        }
        else {
            $error = new stdClass();
            $error->message = Sii::t('sii','Oops! Our system is having an unexpected error.');
            $error->name = Sii::t('sii','System Processing Error');
            $this->onError($error,500);
        }
    }
    /**
     * Generate error flash by response
     * 
     * @param type $error
     * @param type $httpCode
     * @return type
     */
    protected function setResponseErrorFlash($error,$httpCode)
    {
        switch ($httpCode) {
            case 401:
                if (isset($error->message) && strpos($error->message,'The access token provided has expired')!=false){
                    if ($this->retryAccessToken)
                        $this->retryAccessToken();
                    else
                        throw new TokenExpiredException(Sii::t('sii','Token Expired'));
                }
                elseif (isset($error->message) && strpos($error->message,'The access token provided is invalid')!=false)
                    throw new TokenExpiredException(Sii::t('sii','Token Expired'));
                elseif (isset($error->message) && strpos($error->message,'You are requesting with an invalid credential')!=false)
                    throw new InvalidUserCredentialsException(Sii::t('sii','Invalid credential'));
                
                break;//no break, and go to default to get error flash
            case 422:
                user()->setFlash(isset($this->flashId)?$this->flashId:get_class($this->apiModel),array(
                    'message'=>$error->details!=null?Helper::htmlErrors($error->details):$error->name,
                    'type'=>'error',
                    'title'=>$error->message,
                ));
                if (isset($error->details))
                    $this->apiModel->addErrors($error->details);
                break;
            default:
                user()->setFlash(isset($this->flashId)?$this->flashId:get_class($this->apiModel),array(
                    'message'=>$error->message,
                    'type'=>'error',
                    'title'=>$error->name,
                ));
                break;
        }
    } 
    /**
     * Refresh api models with response data
     * Mainly to get id to generate view url
     * @param type $response
     */
    protected function refreshApiModel($response,$attributes=[],$extraAttributes=[])
    {
        foreach (json_decode($response,true) as $field => $value) {
            if (in_array($field,$attributes) || empty($attributes) || in_array($field,$extraAttributes))
                $this->apiModel->$field = $value;
        }
        logTrace(__METHOD__.' '.$this->model.' refreshed',$this->apiModel->attributes);
    }
    
    protected function parsePostFields() 
    {
        if (!isset($this->postFields)){
            $rawBody = [];
            foreach ($_POST[$this->model] as $field => $value) {//scan through submitted fields in $_POST
                if (isset($this->apiModel->$field))
                    $rawBody[$field] = $this->apiModel->$field;
            }
            $this->postFields = json_encode($rawBody);
        }
        logTrace(__METHOD__.' postFields ', $this->postFields, false);
        return $this->postFields;
    }
    /**
     * Find the oauth access token
     * @param boolean $force True to find access token again and not using cache
     */
    public function findAccessToken($force=false)
    {
        if (!user()->hasApiAccessToken()||$force){
            Yii::import('common.components.actions.api.ApiTokenAction');
            $action = new ApiTokenAction($this->controller,__METHOD__);
            $action->user = $this->user;
            $this->controller->runAction($action);
        }
        else
            logTrace(__METHOD__.' use existing session token',user()->getApiAccessToken());
    }    
    /**
     * @return type
     * @throws CHttpException
     */
    public function getAccessToken()
    {
        if (user()->hasApiAccessToken()){
            return user()->getApiAccessToken();
        }
        throw new CHttpException(401,Sii::t('sii','Access token not found.'));    
    }
   
    private $_bak = [];
    public function backupParams($fields=[])
    {
        foreach ($fields as $field) {
            $this->_bak[$field]= $this->$field;
        }
    }
    public function restoreParams()
    {
        foreach ($this->_bak as $field => $value) {
            $this->$field = $value;
        }
    }
    
    public function retryAccessToken()
    {
        user()->deleteApiAccessToken();
        $this->findAccessToken();
        logInfo(__METHOD__.' ok');
    }
}
