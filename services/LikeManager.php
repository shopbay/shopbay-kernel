<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of LikeManager
 *
 * @author kwlok
 */
class LikeManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }   
    /**
     * Toggle a model (Like or dislike).
     * This action supports either way, it depends on the passing in value of variable 'model->status'
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function toggle($user,$model)
    {
        $this->validate($user, $model, false);
        if($model->save()){
            if ($model->likable())
                $model->updateCounter(-1);
            else
                $model->updateCounter(1);
            $model->recordActivity($model->likable()?LikeForm::ACTION_DISLIKE:LikeForm::ACTION_LIKE);
            logTrace(__METHOD__.' ok',$model->getAttributes());
        }
        else {
            logError(__METHOD__.' error',$model->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        return $model;
    }
    /**
     * Likes a model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function like($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        $model->status=Process::YES;
        return $this->execute($model, array(
                        'save'=>self::EMPTY_PARAMS,
                        'updateCounter'=>1,
                        'recordActivity'=>Activity::EVENT_LIKE,
                    ));
    }
    /**
     * Dislike a model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function undo($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);

        $model->status=Process::NO;
        return $this->execute($model, array(
                        'save'=>self::EMPTY_PARAMS,
                        'updateCounter'=>-1,
                        'recordActivity'=>Activity::EVENT_DISLIKE,
                    ));
    }
    
}
