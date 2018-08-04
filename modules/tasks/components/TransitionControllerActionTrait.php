<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TransitionControllerActionTrait
 *
 * @author kwlok
 */
trait TransitionControllerActionTrait 
{
    protected $transitionModelFilter = 'mine';
    protected $transitionCheckAccess = true;
    protected $transitionViewFile = 'view';
    protected $transitionErrorViewFile = 'view';
    /**
     * Customize transition action view file.
     */
    public function setTransitionViewFile($viewFile)
    {
        $this->transitionViewFile = $viewFile;
        $this->transitionErrorViewFile = $viewFile;
    }
    /**
     * Declares class-based actions.
     */
    public function transitionActions($validateActivate=true,$validateDeactivate=false,$nameAttribute=null,$multilang=true)
    {
        $model = $this->modelType;
        return [
            'activate'=>[
                'class'=>'common.modules.tasks.actions.TransitionAction',
                'modelType'=>$model,
                'modelFilter'=>$this->transitionModelFilter,
                'checkAccess'=>$this->transitionCheckAccess,
                'nameAttribute'=>$nameAttribute!=null?$nameAttribute: 'name',
                'validate'=>$validateActivate ? ['scenario'=>'activate','field'=>'status'] : [],
                'flashTitle'=>Sii::t('sii','{object} Activation',['{object}'=>$model::model()->displayName()]),
                'flashMessage'=>Sii::t('sii','"{name}" is activated successfully.'),
                'flashMessageMultilang'=>$multilang,
                'errorType'=>'notice',
                'errorTitle'=>Sii::t('sii','{object} Activation',['{object}'=>$model::model()->displayName()]),
                'errorViewFile'=>$this->transitionErrorViewFile,
                'viewFile'=>$this->transitionViewFile,
            ],
            'deactivate'=>[
                'class'=>'common.modules.tasks.actions.TransitionAction',
                'modelType'=>$model,
                'modelFilter'=>$this->transitionModelFilter,
                'checkAccess'=>$this->transitionCheckAccess,
                'nameAttribute'=>$nameAttribute!=null?$nameAttribute: 'name',
                'validate'=>$validateDeactivate ? ['scenario'=>'deactivate','field'=>'status'] : [],
                'flashTitle'=>Sii::t('sii','{object} Deactivation',['{object}'=>$model::model()->displayName()]),
                'flashMessage'=>Sii::t('sii','"{name}" is deactivated successfully.'),
                'flashMessageMultilang'=>$multilang,
                'errorTitle'=>Sii::t('sii','{object} Deactivation',['{object}'=>$model::model()->displayName()]),
                'errorViewFile'=>$this->transitionErrorViewFile,
                'viewFile'=>$this->transitionViewFile,
            ],
        ];        
    }
}
