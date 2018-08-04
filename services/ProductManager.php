<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of ProductManager
 *
 * @author kwlok
 */
class ProductManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Create category model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createCategory($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'insertChilds'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ),$model->getScenario());
    }
    /**
     * Update category model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function updateCategory($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'updateChilds'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ),$model->getScenario());
    } 
    /**
     * Delete category model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function deleteCategory($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                'recordActivity'=>array(
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ),
                'detachMediaAssociation'=>self::EMPTY_PARAMS,
                'deleteChilds'=>self::EMPTY_PARAMS,
                'delete'=>self::EMPTY_PARAMS,
            ),'delete');
    }    
    /**
     * Create product model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createProduct($user,$model)
    {
        $this->validate($user, $model, false);
        $model->id = null;//let auto increment handle this
        $model->account_id = $user;
        $model->status = Process::PRODUCT_OFFLINE;
        $executables = [
            'insert'=>self::EMPTY_PARAMS,
            'insertSiblings'=>self::EMPTY_PARAMS,
        ];
        if ($this->runMode=='api'){
            //If image is array, it means an array of image urls (this is ensured by validation above via api)
            //If image is not integer, it means its a image url (this is ensured by validation above via api)
            //Have to create an external image with this url first, before running savePrimaryImage
            if (is_array($model->image) || !Helper::isInteger($model->image)){
                $executables = array_merge($executables,[
                    'saveImagesByUrl'=>$user,
                ]);
            }
        }
        $executables = array_merge($executables,[
            'savePrimaryImage'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
            self::ELASTICSEARCH=>'saveSearchIndex',//refer to SearchableBehavior
        ]);
        return $this->execute($model, $executables, 'create');
    }
    /**
     * Update product model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function updateProduct($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        $executables = [
            'update'=>self::EMPTY_PARAMS,
            'updateSiblings'=>self::EMPTY_PARAMS,
        ];
        if ($this->runMode=='api') {
            //If image is array, it means an array of image urls (this is ensured by validation above via api)
            //If image is not integer, it means its a image url (this is ensured by validation above via api)
            //Have to create an external image with this url first, before running savePrimaryImage
            if (is_array($model->image) || !Helper::isInteger($model->image)){
                $executables = array_merge($executables,[
                    'saveImagesByUrl'=>$user,
                ]);
            }
        }
        $executables = array_merge($executables,[
            'savePrimaryImage'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
            self::ELASTICSEARCH=>'saveSearchIndex',//refer to SearchableBehavior
        ]);        
        return $this->execute($model, $executables);
    }
    /**
     * Delete product model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function deleteProduct($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        $executables = [
            'recordActivity'=>[
                'event'=>Activity::EVENT_DELETE,
                'account'=>$user,
            ],
        ];
        //comment off if clause below if the deleteChildCallback is uncommented at Product class
        //--begin
        if (!$model->onSoftDelete){
            $executables = array_merge($executables,[
                'detachMediaAssociation'=>self::EMPTY_PARAMS,
                'deleteSiblings'=>self::EMPTY_PARAMS,
                'deleteInventories'=>self::EMPTY_PARAMS,
            ]);
        }
        //--end
        $executables = array_merge($executables,[
            'delete'=>self::EMPTY_PARAMS,
            'detachMediaAssociation'=>self::EMPTY_PARAMS,
            self::ELASTICSEARCH=>'deleteSearchIndex',//refer to SearchableBehavior
        ]);        
        return $this->execute($model, $executables,'delete');
    }
    /**
     * Create product attribute model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createProductAttribute($user,$model)
    {
        $this->validate($user, $model, false);
        return $this->execute($model, array(
                'insert'=>self::EMPTY_PARAMS,
                'insertChilds'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update product attribute model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function updateProductAttribute($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                'update'=>self::EMPTY_PARAMS,
                'updateChilds'=>self::EMPTY_PARAMS,
                'recordActivity'=>Activity::EVENT_UPDATE,
        ));
    } 
    /**
     * Delete product attribute model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function deleteProductAttribute($user,$model,$checkAccess=true)
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
}
