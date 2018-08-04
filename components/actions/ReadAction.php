<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
/**
 * Description of ReadAction
 *
 * @author kwlok
 */
class ReadAction extends CRUDBaseAction 
{
    /**
     * Filter method of model
     * @var type 
     */
    public $modelFilter = 'mine';
    /**
     * The finder method for search; Default to 'retrieve' 
     * 
     * @see SActiveRecord::retrieve()
     * @var string 
     */
    public $finderMethod = 'retrieve';
    /**
     * The account attribute used to check access rights; Default to null, following the value in ServiceManager
     * @see ServiceManager->ownerAttribute
     * 
     * @var string 
     */
    public $accountAttribute;
    /**
     * The attribute of model to display at page title
     * @var type 
     */
    public $pageTitleAttribute;
    /**
     * The call back function before render
     * @var type 
     */
    public $beforeRender;
    /**
     * The page view file
     * @var string 
     */
    public $viewFile = 'view';
    /**
     * A callback method to load model based on model id; 
     * If not set, it will do a direct calling $this->_findModel($search) method 
     * If set, it is always expect returning $model 
     * 
     * Example: At controller side, there is a method callback "loadModelMethod($search)
     * 
     * public function loadModelMethod($search)
     * {
     *     //do some logic
     *     //...
     *     return $model;
     * }
     * @var array
     */
    public $loadModelMethod;
    /**
     * Run the action
     */
    public function run()             
    {
        logInfo('['.$this->controller->uniqueId.'/'.$this->controller->action->id.'] '.__METHOD__.' $_GET', $_GET);
        if (isset($this->loadModelMethod)){
            $model = $this->controller->{$this->loadModelMethod}();        
        }
        else {
            $search = current(array_keys($_GET));//take the first key as search attribute
            $model = $this->_findModel($search);
        }
        //NOTE: passing $this->serviceInvokeParam into getServiceManager() are mainly for QuestionManager;
        //most other serviceManager does not have this parameter as its method signature
        //php auto ignore it if getServiceManager() has no argument
        $serviceManager = $this->controller->module->getServiceManager($this->serviceInvokeParam);
        if (!isset($serviceManager))
            throw new CHttpException(500,Sii::t('sii','Service Not Found'));        

        if ($serviceManager->checkObjectAccess(user()->getId(),$model,$this->accountAttribute)){
            
            $this->controller->setPageTitle($this->getPageTitle($model));
                
            if (isset($this->beforeRender))
                $this->controller->{$this->beforeRender}($model);
                
            $this->renderPage($model);
        }
        else {
            logError(__METHOD__.' Unauthorized access', $model->getAttributes());
            throwError403(Sii::t('sii','Unauthorized Access'));
        }
    }     
    
    protected function _findModel($search)
    {
        try {
            $type = $this->model;
            if (isset($this->modelFilter))
                $model = $type::model()->{$this->modelFilter}()->{$this->finderMethod}($search)->find();
            else
                $model = $type::model()->{$this->finderMethod}($search)->find();

            if($model===null){
                throw new CHttpException(404,Sii::t('sii','Page not found'));
            }
            return $model;
        } catch (CException $e) {
            logError(__METHOD__.' '.$type.'->'.$this->modelFilter.'()->'.$this->finderMethod.'(\''.$search.'\') error',array(),false);
            throwError404(Sii::t('sii','The requested page does not exist'));
        }
    }
    
    public function getPageTitle($model)
    {
        $title = $model->displayName();
        if (isset($this->pageTitleAttribute))
            $title = $model->{$this->pageTitleAttribute}.' | '.$title;
        return $title;
    }
    
}
