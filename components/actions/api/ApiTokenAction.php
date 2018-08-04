<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
Yii::import("common.components.actions.api.models.ApiOauthClient");
/**
 * Description of ApiTokenAction
 *
 * @author kwlok
 */
class ApiTokenAction extends CRUDBaseAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $user;//the user who linked to client id
    public $grant_type = 'client_credentials';
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = 'POST';
        $this->apiVersion = '';//no version
        $this->apiRoute = 'oauth2/token';
        $this->httpContentType = 'application/x-www-form-urlencoded';
        $this->httpPostField = true;
        $this->postFields = 'grant_type='.$this->grant_type;
        $this->traitInit();
    } 
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        $client = ApiOauthClient::getClientInfo($this->user);
        if ($client===null)
            throw new CHttpException(500,Sii::t('sii','Client not found.'));    
        if (!is_array($client))
            throw new CHttpException(500,Sii::t('sii','Client data not found.'));    
        
        $this->execCurl($this->getAuthBasicHeader($client['id'], $client['secret']));
    } 
    
    public function onSuccess($response,$httpCode)
    {
        if ($httpCode==200){
            $return = json_decode($response,true);
            if (isset($return['access_token'])){
                user()->setApiAccessToken($return['access_token']);
                logTrace(__METHOD__.' store new access token in cache',user()->getApiAccessToken());
            }
        }
    }
    
    public function onError($error, $httpCode) 
    {
        throw new CException($error->message);
    }
    
    
}
