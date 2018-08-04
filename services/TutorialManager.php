<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of TutorialManager
 *
 * @author kwlok
 */
class TutorialManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Write model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function write($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::TUTORIAL_DRAFT;//initial status is draft
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_WRITE,
        ));
    }
    /**
     * Edit model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function edit($user,$model,$checkAccess=true)
    {
        if (!$model->updatable())
            throw new CException(Sii::t('sii','Edit not allowed.'));        
        
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_EDIT,
        ));
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
        if (!$model->deletable())
            throw new CException(Sii::t('sii','Delete not allowed.'));        
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                'recordActivity'=>array(
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ),
                'delete'=>self::EMPTY_PARAMS,
            ),'delete');
    }
    /**
     * Submit Tutorial / Tutorial Series
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function submit($user,$model)
    {
        if (!$model->submitable())
            throw new CException(Sii::t('sii','Submit not allowed.'));
        
        return $this->transition($user, $model, 'submit');
    }
    /**
     * Publish Tutorial / Tutorial Series
     * 
     * @param integer $user Session user id
     * @param CModel $model Tutorial model to publish
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function publish($user,$model,$transition)
    {
        $model->setScenario($transition->decision);
        if (!$model->validate()) {
            logError(__METHOD__.' error',$model->getErrors());
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        $model->setOfficer($user);
        $model->setAccountOwner('officerAccount');
        $this->ownerAttribute = 'id';
        //first run workflow
        $model = $this->runWorkflow(
                        $user,
                        $model, 
                        $transition, 
                        Transition::SCENARIO_C1_D, 
                        Activity::EVENT_PUBLISH, 
                        'publishable');
        //save search index
        return $this->execute($model, array(
                    self::ELASTICSEARCH=>'saveSearchIndex',//refer to SearchableBehavior
                ));
    }      
    /**
     * Create tutorial series 
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createSeries($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::TUTORIAL_SERIES_DRAFT;//initial status is draft
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_WRITE,
        ));
    }    
    /**
     * Update tutorial series
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateSeries($user,$model,$checkAccess=true)
    {
        return $this->edit($user, $model, $checkAccess);
    }    
    /**
     * Delete tutorial series
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function deleteSeries($user,$model,$checkAccess=true)
    {
        return $this->delete($user, $model, $checkAccess);
    }       
}
