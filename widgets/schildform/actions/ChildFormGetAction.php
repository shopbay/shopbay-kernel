<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ChildFormGetAction
 *
 * @author kwlok
 */
class ChildFormGetAction extends CAction 
{
    /**
     * Name of the session state variable to temporary store attribute objects. Default to 'undefined'
     * @var string
     */
    public $stateVariable = 'undefined';
    /**
     * Name of the session state variable as the key used to construct child form.
     * @var string
     */
    public $formKeyStateVariable = 'undefined';
    /**
     * Name of the child form. Default to 'undefined'
     * @var string
     */
    public $formModel = 'undefined';
    /**
     * Name of the child form view. Default to 'common.widgets.schildform.views._form'
     * @var string
     */
    public $formView = 'common.widgets.schildform.views._form';
    /**
     * Indicate if child form is a language form; Default to "true"
     * @var string
     */
    public $useLanguageForm = true;
    /**
     * The mandatory GET parameter must exists before proceed
     * @var string
     */
    public $mandatoryGetParam;
    /**
     * The mandatory GET parameter will be set to model corresponing attribute
     * @var string
     */
    public $mandatoryGetParamAttributeMapping;
    
    public function init( ) 
    {
        parent::init();
    }
    /**
     * Get subcategory form and add session
     * @param integer $type the type of attribute to get
     */
    public function run() 
    {
        if (isset($this->mandatoryGetParam)) {
            if (!isset($_GET[$this->mandatoryGetParam])) 
                throwError403(Sii::t('sii','Unauthorized Access'));        
        }
        
        $form = new $this->formModel(SActiveSession::get($this->formKeyStateVariable));
        if ($this->useLanguageForm && !$form instanceof LanguageForm)
            throw new CException(Sii::t('sii','Invalid form.'));
        $form->id = $this->getTempId();
        if ($this->mandatoryGetParamAttributeMapping){
            $form->{$this->mandatoryGetParamAttributeMapping} = $_GET[$this->mandatoryGetParam]=='undefined'?null:$_GET[$this->mandatoryGetParam];
        }
        SActiveSession::add($this->stateVariable, $form);             
        header('Content-type: application/json');
        echo CJSON::encode(array(
            'status'=>'success',
            'form'=>Yii::app()->controller->renderPartial($this->formView,array('form'=>$form),true),
        ));
        Yii::app()->end();  
        
    }    
    /**
     * Temporary id for new child
     * Need to be big enough so that it is impossible to get found at id column
     * Current id column at db table is int(11) by default
     * @return type
     */
    protected function getTempId($seed=null)
    {
        return (isset($seed)?$seed:user()->getId()).time()*1000;
    }
}