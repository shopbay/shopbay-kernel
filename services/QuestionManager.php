<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of QuestionManager
 *
 * @author kwlok
 */
class QuestionManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();        
    }   
    /**
     * Ask a question
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to ask
     * @return CModel $model
     * @throws CException
     */
    public function ask($user,$model)
    {
        $this->validate($user, $model, false);
        $model->question_by = $user;
        if (!$model->isCommunityQuestion()) {
            $model->status = Process::ASK;
            return $this->execute($model, array(
                        'insertQuestion'=>self::EMPTY_PARAMS,
                        self::WORKFLOW=>array(
                            'condition'=>Sii::t('sii','You have asked this question.'),
                            'action'=>WorkflowManager::ACTION_ASK,
                            'decision'=>WorkflowManager::DECISION_NULL,
                            'saveTransition'=>true,
                            'transitionBy'=>$user,
                        ),
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_ASK,
                            'icon_url'=>$model->getActivityIconUrl('reference'),
                        )
                    ));                   
        }
        else {
            $model->status = Process::QUESTION_OFFLINE;//initial status is OFFLINE for community question
            return $this->execute($model, array(
                'insertQuestion'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_ASK,
            ));            
        }
    }        
    /**
     * Answer a question
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to answer
     * @return CModel $model
     * @throws CException
     */
    public function answer($user,$model)
    {
        $this->validate($user, $model, false);
        $model->answer_by = $user;
        $model->answer_time = time();

        return $this->execute($model, array(
                                'updateAnswer'=>self::EMPTY_PARAMS,
                                self::WORKFLOW=>array(
                                    'condition'=>Sii::t('sii','Shop owner has answered this question.'),
                                    'action'=>WorkflowManager::ACTION_ANSWER,
                                    'decision'=>WorkflowManager::DECISION_NULL,
                                    'saveTransition'=>true,
                                    'transitionBy'=>$user,
                                ),
                                'recordActivity'=>array(
                                    'event'=>Activity::EVENT_ANSWER,
                                    'account'=>$model->answer_by,
                                    'description'=>$model->htmlnl2br($model->answer),
                                    'obj_url'=>$model->merchantViewUrl,
                                    'icon_url'=>$model->getActivityIconUrl('answerer'),
                                )
                            ));                   
    }    
    /**
     * Unpdate answer 
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to answer
     * @return CModel $model
     * @throws CException
     */
    public function updateAnswer($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);//validation include purify content
        $model->answer_by = $user;
        $model->answer_time = time();//todo should have answer update time separately?
                    
        return $this->execute($model, array(
                        'updateAnswer'=>self::EMPTY_PARAMS,
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_UPDATE,
                            'description'=>$model->htmlnl2br($model->answer),
                            'account'=>$model->answer_by,
                            'obj_url'=>$model->merchantViewUrl,
                            'icon_url'=>$model->getActivityIconUrl('answerer'),
                        ),
                    ),'updateAnswer');         
    }       
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                'recordActivity'=>array(
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ),
                'delete'=>self::EMPTY_PARAMS,
                self::ELASTICSEARCH=>'deleteSearchIndex',//refer to SearchableBehavior
            ),'delete');
    }    
    /**
     * Unpdate question 
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to answer
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);//validation include purify content
                    
        return $this->execute($model, array(
                        'updateQuestion'=>self::EMPTY_PARAMS,
                        'recordActivity'=>Activity::EVENT_UPDATE
                    ));         
    }       
    
}
