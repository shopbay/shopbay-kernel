<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiAuthorizeAction
 * A wrapper to get authorization code for an oauth client
 * 
 * @author kwlok
 */
class ApiAuthorizeAction extends CRUDBaseAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }

    public $client_id;
    public $client_secret;
    public $user_id;
    public $redirect_uri;//to be matched with db record
    public $state = 'default';
    public $redirectUriOnSuccess;//actual url to redirect when authorization code is obtained
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = 'POST';
        $this->apiVersion = '';//no version
        $this->apiRoute = 'oauth2/authorize';
        $this->traitInit();
    }     
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        $this->queryParams = '?'.http_build_query([
            'response_type'=>'code',
            'redirect_uri'=>$this->redirect_uri,
            'client_id'=>$this->client_id,
            'client_secret'=>$this->client_secret,
            'user_id'=>$this->user_id,
            'state'=>$this->state,
        ]);
        $this->execCurl($this->getAuthBearerHeader());
    }     
    
    public function onSuccess($response,$httpCode)
    {
        $returnData = json_decode($response,true);
        $this->controller->redirect($this->redirectUriOnSuccess.'&authorization_code='.$returnData['authorization_code']);
    }
    
    public function onError($error, $httpCode) 
    {
        $errorMessage = '';
        if (isset($error->message))
            $errorMessage = $error->message;
        logError(__METHOD__." error code $httpCode",$errorMessage);
        throw new CException("Authorization code request $errorMessage, code=$httpCode");
    }

}
