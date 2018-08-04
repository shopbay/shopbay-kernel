<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of NewsManager
 *
 * @author kwlok
 */
class NewsManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
                    
        $model->account_id = $user;
        $model->status = Process::NEWS_OFFLINE;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
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
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_DELETE,
                            'account'=>$user,
                        ),
                        'delete'=>self::EMPTY_PARAMS,
                    ),'delete');
    }
    
}
