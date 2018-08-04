<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of MessageManager
 *
 * @author kwlok
 */
class MessageManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Compose a message
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function compose($user,$model)
    {
        $this->validate($user, $model, false);
        $model->sender = $user;
        $model->send_time = time();
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_COMPOSE,
                'account'=>$user,
            )
        ));
    }
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to delete
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
                        'icon_url'=>Yii::app()->controller->getImage('mail_close.png'),
                    ),
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    }    
}
