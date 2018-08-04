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
 * Description of ApiLoginAction
 *
 * @author kwlok
 */
class ApiLoginAction extends CRUDBaseAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    /**
     * @var CFormModel Expecting login form; This property must be set.
     */
    private $_model;
    /**
     * Constructor
     * @param type $controller
     * @param type $id
     * @param type $loginForm
     */
    public function __construct($controller, $id, $loginForm) 
    {
        if (!$loginForm instanceof LoginForm)
            throw new CHttpException(500,Sii::t('sii','Login Form not defined'));    
        $this->_model = $loginForm;
        parent::__construct($controller, $id);
    }
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = 'POST';
        $this->apiVersion = '';//no version
        $this->traitInit();
    }   
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        if (!isset($this->apiRoute))
            throw new CException('Unknown authentication route.');
        
        $this->execCurl($this->getAuthBasicHeader($this->username,$this->password));
    } 
    
    public function onSuccess($response,$httpCode)
    {
        if ($httpCode == 200){//authentication successful
            $returnData = json_decode($response,true);
            logTrace(__METHOD__.' Return user data',$returnData);
            
            if (!isset($returnData['access_token'])){
                Yii::app()->user->deleteApiAccessToken();
                Yii::app()->user->logout();
                throw new CException(Sii::t('sii','Login failed. Please try again.'));
            }
            
            if (Yii::app()->user instanceof WebUser){
                if (userOnScope('shop') && isset($returnData['shop_id'])){
                    $cid = IdentityCustomer::createCid($returnData['shop_id'], $this->username);
                    $identity = new IdentityCustomer($cid,$this->password);
                    logInfo(__METHOD__.' user on shop scope: shop',$returnData['shop_id']);
                }   
                else
                    $identity = new IdentityUser($this->username,$this->password);
            }
            else if (Yii::app()->user instanceof WebAdmin)
                $identity = new IdentityAdmin($this->username,$this->password);
            
            $identity->setId($returnData['user_id']);
            Yii::app()->user->login($identity,$this->_model->rememberMeDuration);
            Yii::app()->user->setApiAccessToken($returnData['access_token']);
        }        
    }
    
    public function onError($error, $httpCode) 
    {
        if ($httpCode==401)
            throw new InvalidUserCredentialsException(Sii::t('sii','Wrong username or password'));
        else
            throw new CException(Sii::t('sii','Oops, we have problem logging you in. Please try again.'));
    }
    
    protected function getUsername()
    {
        return $this->_model->username;
    }
    
    protected function getPassword()
    {
        return $this->_model->password;
    }

}
