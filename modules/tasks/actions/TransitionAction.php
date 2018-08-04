<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.tasks.controllers.TransitionController");
/**
 * Description of TransitionAction
 *
 * @author kwlok
 */
class TransitionAction extends CAction 
{
    /**
     * Name of the model type to load and transition status. Defaults to 'undefined'
     * @var string
     */
    public $modelType = 'undefined';
    /**
     * Name of the key attribute to load model type. Defaults to 'id'
     * @var string
     */
    public $keyAttribute = 'id';
    /**
     * Name of filter for model. Defaults to 'mine'
     * @var string
     */
    public $modelFilter = 'mine';
    /**
     * Name of the name attribute to load model type. Defaults to 'name'
     * @var string
     */
    public $nameAttribute = 'name';
    /**
     * Name of the model validation scenario to validate. Defaults to 'empty array'
     * Two keys in array; 'scenario' and 'field'
     * @var string
     */
    public $validate = [];
    /**
     * Title of flash message. Defaults to 'Transition'
     * @var string
     */
    public $flashTitle = 'Transition';
    /**
     * Message body of flash message. Defaults to 'Transition'
     * @var string
     */
    public $flashMessage = 'Object is transitioned successfully';
    /**
     * Indicate if flash message is multi-lange (requires LanguageBehavior). Defaults to 'false'
     * @var string
     */
    public $flashMessageMultilang = false;
    /**
     * View file name. Defaults to 'view'
     * @var string
     */
    public $viewFile = 'view';
    /**
     * Php evaluation script
     * Extra data to pass to view file. Defaults return 'empty array()'
     * Example: return array('key1'=>'value1','key2'=>'value2');
     * @var array
     */
    public $viewData = 'return array();';
    /**
     * Type of flash message. Defaults to 'Error', other options: notice, success
     * @var string
     */
    public $errorType = 'error';
    /**
     * Title of flash message. Defaults to 'Error'
     * @var string
     */
    public $errorTitle = 'Transition Error';
    /**
     * View file name when error happens. Defaults to 'view'
     * @var string
     */
    public $errorViewFile = 'view';       
    /**
     * Check model ownership access; Default to "true"
     * @var boolean
     */
    public $checkAccess = true;       
    /**
     * Transition item
     * @param integer $_POST[$this->modelType]['id']
     */
    public function run() 
    {
        if (isset($_REQUEST[$this->modelType][$this->keyAttribute])){

            $type = $this->modelType;

            $finder = isset($this->modelFilter) ? $type::model()->{$this->modelFilter}() : $type::model();

            $model = $finder->findByPk($_REQUEST[$this->modelType][$this->keyAttribute]);

            if (Yii::app()->request->getIsPostRequest()){

                try {       
                    
                    if ($model===null)
                       throw new CException(Sii::t('sii','{object} not found',array('{object}'=>$this->modelType)));

                    if (!empty($this->validate)){
                        $v = (object)$this->validate;
                        $model->setScenario($v->scenario);
                        if (!$model->validate(array($v->field)))
                            throw new CException($model->getError($v->field));
                    }
                    
                    if (TransitionController::transition($model,$this->getController()->action->id,$this->checkAccess)==WorkflowManager::FAILURE)
                        throw new CException(Sii::t('sii','Transition Error'));

                    user()->setFlash(get_class($model),array('message'=>$this->getSuccessMessage($model),
                                   'type'=>'success',
                                   'title'=>$this->flashTitle));

                    unset($_POST);


                } catch(CException $e) {
                    logError(__METHOD__.' '.$e->getMessage(),[],false);
                    user()->setFlash(get_class($model),array('message'=>$e->getMessage(),
                                   'type'=>$this->errorType,
                                   'title'=>$this->errorTitle));
                    $this->getController()->render($this->errorViewFile, array_merge(array('model'=>$model),eval($this->viewData)));
                    Yii::app()->end();
                }

            }
            
            $this->getController()->render($this->viewFile, array_merge(array('model'=>$model),eval($this->viewData)));
            
            Yii::app()->end();
            
        }

        throwError403(Sii::t('sii','Unauthorized Access')); 
    }  
    
    public function getSuccessMessage($model)
    {
        if (isset($this->nameAttribute)){
            if ($this->flashMessageMultilang)
                return str_replace('{name}',$model->displayLanguageValue($this->nameAttribute,user()->getLocale()),$this->flashMessage);
            else 
                return str_replace('{name}',$model->{$this->nameAttribute},$this->flashMessage);
        }
        else
            return $this->flashMessage;
    }
}