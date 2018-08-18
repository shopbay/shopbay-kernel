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
 * Description of ApiSubscribeAction
 *
 * @author kwlok
 */
class ApiSubscribeAction extends CRUDBaseAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    /**
     * The call back function after subscribe
     * @var type 
     */
    public $afterSubscribe;
    /**
     * Indicate if subscription is done for specific user (but action by login user (authenticated)
     * E.g. use case is for Admin user (internal plan)
     * <pre>
     *   [api.domain]/v1/plans/subscribe/{id}/{subscriber}
     * </pre>
     * @var integer Default to null
     */
    private $_subscriber;
    /**
     * @var integer The plan id.
     */
    private $_plan;
    /**
     * Constructor
     * @param type $controller
     * @param type $id
     * @param type $plan
     * @param type $subscriber
     */
    public function __construct($controller, $id, $plan, $subscriber=null) 
    {
        if (!isset($plan))
            throw new CHttpException(500,Sii::t('sii','Plan not defined'));    
        $this->_plan = $plan;
        $this->_subscriber = $subscriber;
        parent::__construct($controller, $id);
    }
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = 'POST';
        $this->retryAccessToken = true;
        $this->apiRoute = '/plans/subscribe';
        $this->queryParams = '/'.$this->_plan;
        if ($this->_subscriber!=null){
            $this->queryParams .= '/'.$this->_subscriber;
        }
        $this->httpPostField = true;
        $this->traitInit();
    }   
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        $this->findAccessToken(true);//set $force to true as subscription cannot failed due to token expired
        $this->execCurl($this->getAuthBearerHeader());
    } 
    
    public function onSuccess($response,$httpCode)
    {
        unset($_POST);
        if (isset($this->afterSubscribe))
            $this->controller->{$this->afterSubscribe}(json_decode($response,true));
        
    }
    
    public function onError($error, $httpCode) 
    {
        if (isset($error->details))
            throw new CHttpException($httpCode,Helper::htmlErrors($error->details));
        else
            throw new CHttpException($httpCode,$error->message);
    }
    
}
