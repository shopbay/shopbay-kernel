<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of QuestionController
 *
 * @author kwlok
 */
class QuestionController extends TransitionController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Question';
        $this->searchView = '_questions';
        $this->modelSortOrder = 'question_time DESC';
        $this->messageKey = 'question';
    }
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'rights - ask', //excludes from filter as question may be asked directly from shop portal when user has not yet logined
        );
    }        
    public function actionAsk()
    {
        if(isset($_POST['QuestionForm'])) {

            $form = new QuestionForm();
            
            $form->attributes = $_POST['QuestionForm'];

            if (user()->isGuest) {
                Yii::app()->user->returnUrl = $form->askUrl;
                header('Content-type: application/json');
                user()->loginRequiredAjaxResponse = CJSON::encode(array('status'=>'loginrequired','url'=>user()->loginUrl));
                user()->loginRequired();
                Yii::app()->end();  
            }

            if (!user()->hasRole(Role::CUSTOMER))
                 throwError403(Sii::t('sii','Unauthorized Access'));

            try {    

                if($form->validate()) {

                    $model = new $this->modelType;
                    
                    $model->attributes = $form->getAttributes(array('question','type','obj_id'));

                    $model->obj_type = SActiveRecord::restoreTablename($form->obj_type);

                    $model = $this->module->getServiceManager($model,'question_by')->ask(user()->getId(),$model);

                    user()->setFlash(get_class($model),array(
                        'message'=>Sii::t('sii','We will reply you shortly'),
                        'type'=>'success',
                        'title'=>Sii::t('sii','Thanks for your question')));

                    unset($_POST);

                    if (request()->getIsAjaxRequest()){
                    //this handler is for ajax call; use case product catalog question tab
                        header('Content-type: application/json');
                        echo CJSON::encode(array(
                            'status'=>'success',
                            'flash'=>$this->sflashWidget(get_class($model),true),
                        ));
                    }
                    else
                        $this->redirect($model->viewUrl);

                    Yii::app()->end();

                }
                else {
                    logError('QuestionForm validation error',$form->getErrors(),false);
                    throw new CException(Sii::t('sii','Validation error'));
                }

            } catch(Exception $e) {
                user()->setFlash(get_class($form),array('message'=>Helper::htmlErrors($form->getErrors()),
                               'type'=>'error',
                               'title'=>Sii::t('sii','Ask Question')));
                if (request()->getIsAjaxRequest()){
                //this handler is for ajax call; use case product catalog question tab
                    header('Content-type: application/json');
                    echo CJSON::encode(array(
                        'status'=>'failure',
                        'flash'=>$this->sflashWidget(get_class($form),true),
                    ));
                }
                else {
                    $form->askUrl = '/'.$this->uniqueId.'/'.$this->getAction()->id;
                    $form->formView = $this->getModule()->getView('questionform');
                    $this->render($this->getModule()->getView('question'),array('model'=>$form,'product'=>$form->product));
                }
                Yii::app()->end();
            }

        }
        
        throwError403(Sii::t('sii','Unauthorized Access'));

    }
    
    public function actionAnswer()
    {
        if (!user()->hasRole(Role::MERCHANT))
             throwError403(Sii::t('sii','Unauthorized Access'));
        
        if(isset($_POST['AnswerForm'])) {

            $form = new AnswerForm($_POST['AnswerForm']['id']);
            $form->answer=$_POST['AnswerForm']['answer'];

            try {    

                if($form->validate()) {

                    $model = $this->loadModel($form->id,$this->modelType);
                    
                    $model->answer = $form->answer;

                    $model = $this->module->getServiceManager($model,'answer_by')->answer(user()->getId(),$model);

                    user()->setFlash(get_class($model),array(
                        'message'=>Sii::t('sii','Answer is posted successfully'),
                        'type'=>'success',
                        'title'=>Sii::t('sii','Thanks for your answer')));
                    unset($_POST);

                    $this->redirect($model->merchantViewUrl);

                    Yii::app()->end();

                }
                else {
                    logError('AnswerForm validation error',$form->getErrors(),false);
                    throw new CException(Sii::t('sii','Validation error'));
                }

            } catch(Exception $e) {
                user()->setFlash(get_class($form),array('message'=>$e->getMessage(),
                               'type'=>'error',
                               'title'=>Sii::t('sii','Answer Question')));
                $form->answerUrl = '/'.$this->uniqueId.'/'.$this->getAction()->id;
                $form->formView = $this->getModule()->getView('answerform');
                $this->render($this->getModule()->getView('answer'),array('model'=>$form));
                Yii::app()->end();
            }


        }
        
        //by default perform task answer
        $this->modelFilter = 'pendingAnswered';        
        $this->_processAction($this->getAction()->getId());        
        
    }      
    
    protected function _processAction($action)
    {
        $model=new Question($action);
        $model->unsetAttributes();  // clear any default values
        if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
            header('Content-type: application/json');
            echo $this->search($model);
            Yii::app()->end();
        }
        $this->_process($action,$model);
    }    
    /**
     * @override
     */
    protected function search($model)
    {
        if(isset($_GET[$this->modelType]))
            $model->attributes=$_GET[$this->modelType];

        return CJSON::encode($this->renderPartial($this->searchView,
                                     array('dataProvider'=>$this->_getDataProvider($model),
                                           'searchModel'=>$model,
                                           'checkboxInvisible'=>$model->getScenario()=='answer'?true:null),
                                     true));
    }    
    
    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate'){
            $criteria->compare('status',Process::QUESTION_OFFLINE);
            $criteria->compare('type',Question::TYPE_PUBLIC);
        }
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::QUESTION_ONLINE);

        $criteria->compare('question',$model->question,true);
        $criteria->compare('answer',$model->answer,true);

        return $criteria;
    }        
    
}