<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
/**
 * Description of DeleteAction
 *
 * @author kwlok
 */
class DeleteAction extends CRUDBaseAction 
{
    /**
     * The service name to invoke in ServiceManager. Defaults to 'delete'
     * @var string
     */
    public $service = 'delete';
    /**
     * The call back function before delete
     * @var type 
     */
    public $beforeDelete;
    /**
     * Redirect url upon success. Defaults to index page
     * @var string
     */
    public $redirectUrl = ['index'];    
    /**
     * Run the action
     */
    public function run()             
    {
        $model = $this->controller->loadModel($_GET['id'],$this->model);
        
        if (!isset($this->controller->module->serviceManager))
            throw new CHttpException(500,Sii::t('sii','Service not found'));        
        
        if ($this->controller->module->serviceManager->checkObjectAccess(user()->getId(),$model)){
            
            try {
                
                if (isset($this->beforeDelete))
                    $this->controller->{$this->beforeDelete}($model);
                                        
                $skipCheckAccess = false;//since this is already done at above lines
                $this->invokeService([user()->getId(),$model,$skipCheckAccess]);

            } catch (CException $e) {
                logError('delete exception', $e->getMessage());
                logError('delete model error', $model->getErrors());
                $this->setErrorFlash($model,$e);
                $this->controller->redirect($model->{$this->viewUrl});
            }
        }
        else {
            logError('Unauthorized access', $model->getAttributes());
            throwError403(Sii::t('sii','Unauthorized Access'));
        }
        
    }     
    /**
     * Generate success flash
     * 
     * @param type $model
     * @return type
     */
    protected function setSuccessFlash($model)
    {
        if (!isset($this->flashTitle))
            $this->flashTitle = Sii::t('sii','{model} Delete');
        if (!isset($this->flashMessage))
            $this->flashMessage = Sii::t('sii','{name} is deleted successfully');
        parent::setSuccessFlash($model);
    }   
    /**
     * Generate error flash
     * 
     * @param type $model
     * @return type
     */
    protected function setErrorFlash($model,$exception)
    {
        if (!isset($this->flashTitle))
            $this->flashTitle = Sii::t('sii','{model} Delete');
        parent::setErrorFlash($model,$exception);
    }    
}