<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of ShippingManager
 *
 * @author kwlok
 */
class ShippingManager extends ServiceManager 
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
        $model->id = null;//set to null to have auto increment
        $model->account_id = $user;
        $model->status =  Process::SHIPPING_OFFLINE;
        if ($model->type==Shipping::TYPE_FREE) {
            $model->rate = 0;
            return $this->execute($model, array(
                'insert'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_CREATE,
            ));
        }
        else if ($model->type==Shipping::TYPE_FLAT) {
            return $this->execute($model, array(
                'insert'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_CREATE,
            ));
        }
        else if ($model->type==Shipping::TYPE_TIERS) {
            return $this->execute($model, array(
                'insert'=>self::EMPTY_PARAMS,
                'insertChilds'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_CREATE,
            ));
        }
        else
            throw new CException(Sii::t('sii','Unknown shipping type'));
       
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        if ($model->type==Shipping::TYPE_TIERS) {
            return $this->execute($model, array(
                'update'=>self::EMPTY_PARAMS,
                'updateChilds'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_UPDATE,
            ));
        }
        else
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
                    'deleteChilds'=>self::EMPTY_PARAMS,
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    }
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createZone($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
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
    public function updateZone($user,$model,$checkAccess=true)
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
    public function deleteZone($user,$model,$checkAccess=true)
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
