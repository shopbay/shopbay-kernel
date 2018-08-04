<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.tasks.components.TaskBaseController");
/**
 * Description of TransitionController
 *
 * @author kwlok
 */
class TransitionController extends TaskBaseController 
{
    protected $modelType = 'undefined';
    protected $transitionView = '../workflow/transition';
    protected $messageKey = 'name';
    public    $searchView = 'undefined';

    public function actionActivate()
    {
        $model=new $this->modelType($this->action->id);
        $model->unsetAttributes();  // clear any default values
        if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
            header('Content-type: application/json');
            echo $this->search($model);
            Yii::app()->end();
        }
        if (request()->getIsAjaxRequest() && isset($_POST['task-checkbox'])) { //for purpose of batch activation  
            header('Content-type: application/json');
            echo $this->_actionInternal($model,$_POST['task-checkbox'],$this->action->id);
            Yii::app()->end();
        }
        $this->_render($this->getAction()->getId(),$model);
    }
    public function actionDeactivate()
    {
        $model=new $this->modelType($this->action->id);
        $model->unsetAttributes();  // clear any default values
        if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
            header('Content-type: application/json');
            echo $this->search($model);
            Yii::app()->end();
        }
        if (request()->getIsAjaxRequest() && isset($_POST['task-checkbox'])) { //for purpose of batch activation  
            header('Content-type: application/json');
            echo $this->_actionInternal($model,$_POST['task-checkbox'],$this->action->id);
            Yii::app()->end();
        }
        $this->_render($this->getAction()->getId(),$model);
    }
    private function _getOutputData($model,$success,$error)
    {
        if ($success->getCount()>0)
            user()->setFlash(get_class($model).'.success',array(
                'message'=>Helper::htmlList($success),
                'type'=>'success',
                'title'=>Sii::t('sii','{object} Task Execution',array('{object}'=>$this->modelType))));
        if ($error->getCount()>0)
            user()->setFlash(get_class($model).'.error',array(
                'message'=>Helper::htmlList($error),
                'type'=>'error',
                'title'=>Sii::t('sii','{object} Task Execution',array('{object}'=>$this->modelType))));

        return CJSON::encode($this->renderPartial($this->transitionView,
                                    array('action'=>$this->getAction()->getId(),
                                          'dataProvider'=>$this->_getDataProvider($model),
                                          'searchModel'=>$model),
                                     true));
    }
    
    protected function _actionInternal($model,$ids,$action)
    {
        $success = new CList;
        $error = new CList;
        foreach ($ids as $key => $value) {

            $type = $this->modelType;
            $typeModel = $type::model()->findByPk($value);
            if($typeModel===null)
                throw new CHttpException(404,Sii::t('sii','Model not found'));
            
            $typeModel->setScenario($action);
            logTrace(__METHOD__.' validating scenario: '.$typeModel->getScenario());
            
            if ($typeModel->hasBehaviors('locale'))
                $message = Helper::rightTrim($typeModel->displayLanguageValue($this->messageKey,user()->getLocale()),50);
            else
                $message = Helper::rightTrim($typeModel->{$this->messageKey},50);
    
            if (!$typeModel->validate())
                $error->add($this->modelType.' "'.l($message,$typeModel->viewUrl).'" '.$typeModel->getError('status'));
            else{
                if (self::transition($typeModel,$action)==WorkflowManager::FAILURE)
                    $error->add(Sii::t('sii','{object} "{message}" cannot be activated.',array('{object}'=>$this->modelType,'{message}'=>l($message,$typeModel->viewUrl))));
                else
                    $success->add(Sii::t('sii','{object} "{message}" is {status} now.',array('{object}'=>$this->modelType,'{message}'=>l($message,$typeModel->viewUrl),'{status}'=>Helper::htmlColorText($typeModel->getStatusText()))));
            }
        }

        return $this->_getOutputData($model, $success, $error);

    }
    
    protected function _render($action,$model)
    {
        $this->render($this->transitionView,
                array('action'=>$action,
                      'dataProvider'=>$this->_getDataProvider($model),
                      'searchModel'=>$model)
                );
    }        
    protected function search($model)
    {
        if(isset($_GET[$this->modelType]))
            $model->attributes=$_GET[$this->modelType];

        return CJSON::encode($this->renderPartial($this->searchView,
                                     array('dataProvider'=>$this->_getDataProvider($model),
                                           'searchModel'=>$model),
                                     true));
    }    
    /**
     * Gateway method to run ServiceManager::transition()
     * 
     * @param type $model
     * @param type $action
     * @return type
     */
    public static function transition($model,$action,$checkAccess=true)
    {
        try {      
            return Yii::app()->getModule('tasks')
                      ->getServiceManager($model,self::_parseTransitionModel($model))
                      ->transition(user()->getId(),$model, $action, self::_parseTransitionModel($model,'iconReference'),true, true , $checkAccess);
            
        } catch(Exception $e) {
            logError($e->getTraceAsString(), $model->getAttributes());
            return WorkflowManager::FAILURE;
        }

    }    
    /**
     * Parse transition model 
     * @param type $model
     * @param string $mode
     * @return string
     */    
    private static function _parseTransitionModel($model,$mode='attribute')
    {
        if (get_class($model)=='Question'){
            if (!$model->isCommunityQuestion()){
                if ($mode=='attribute')
                    return 'answer_by';
                if ($mode='iconReference')
                    return 'answerer';
            }
        }
        //default return null; auto handled by ServiceManager
        return null;
    }
}