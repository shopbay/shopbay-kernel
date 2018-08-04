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
 * Description of ApiLogoutAction
 *
 * @author kwlok
 */
class ApiLogoutAction extends CRUDBaseAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpContentType = 'application/x-www-form-urlencoded';
        $this->httpVerb = 'POST';
        $this->apiVersion = '';//no version
        $this->httpPostField = true;
        $this->postFields = 'token='.user()->getApiAccessToken();
        $this->traitInit();
    }     
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        if (!isset($this->apiRoute))
            throw new CException('Unknown logout route.');
        
        try {
            $this->execCurl($this->getAuthBearerHeader());
            
        } catch (Exception $ex) {
            //catch exception in case user has no access token (likely expired)
            //but logout must be always allowed and cannot fail
            logWarning(__METHOD__.' '.$ex->getMessage().', force logout...');
            $this->logout();
        }
    }     
    
    public function onSuccess($response,$httpCode)
    {
        $this->logout();
    }
    
    public function onError($error, $httpCode) 
    {
        if (isset($error->message) && strpos($error->message,'The access token provided has expired')!=false){
            logWarning(__METHOD__.' access token provided has expired, force logout...');
            $this->logout();
        }
        else {
            logError(__METHOD__." error code $httpCode, but force logout...",$error->message);
            $this->logout();
            //throw new CHttpException($httpCode,$error->message);
        }
    }

    protected function logout()
    {
        Yii::app()->user->deleteApiAccessToken();
        Yii::app()->user->logout();
    }
}
