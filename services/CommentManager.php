<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of CommentManager
 *
 * @author kwlok
 */
class CommentManager extends ServiceManager 
{
    /**
     * Reference of comment; 
     * Default to 'target', refer to Comment model class to see how comment target is retrieved
     * @var type 
     */
    protected $reference = 'target';
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
        $this->validate($user, $model, false);//validation include purify content
        $model->comment_by = $user;
        return $this->execute($model, array(
                        'insertComment'=>self::EMPTY_PARAMS,
                        'updateCounter'=>1,
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_CREATE,
                            'description'=>$model->htmlnl2br($model->content),
                            'account'=>$model->comment_by,
                            'icon_url'=>$model->getActivityIconUrl($this->reference),
                        ),
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
        $this->validate($user, $model, $checkAccess);//validation include purify content
                    
        return $this->execute($model, array(
                        'updateComment'=>self::EMPTY_PARAMS,
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_UPDATE,
                            'description'=>$model->htmlnl2br($model->content),
                            'account'=>$model->comment_by,
                            'icon_url'=>$model->getActivityIconUrl($this->reference),
                        ),
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
                            'description'=>$model->content,
                            'account'=>$model->comment_by,
                            'icon_url'=>$model->getActivityIconUrl($this->reference),
                        ),
                        'updateCounter'=>-1,
                        'delete'=>self::EMPTY_PARAMS,
                    ),'delete');                   
    }
    
}
