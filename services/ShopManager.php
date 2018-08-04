<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.modules.activities.models.Activity");
Yii::import("common.services.exceptions.*");
/**
 * Description of ShopManager
 *
 * @author kwlok
 */
class ShopManager extends ServiceManager 
{   
    /**
     * Apply a shop model
     * 
     * @param integer $user Session user id
     * @param mixed $form ShopApplicationForm or Shop
     * @return CModel $model
     * @throws CException
     */
    public function apply($user,$form)
    {
        if (!($form instanceof ShopApplicationForm || $form instanceof Shop))
            throw new CException(Sii::t('sii','Invalid form'));
        
        if ($form instanceof Shop){
            $model = $form;//clone object
        }
        elseif ($form instanceof ShopApplicationForm){
            if (!$form->validate())
            	$this->throwValidationErrors($form->getErrors());
            
            $model = new Shop();
            $model->attributes = $form->getAttributes(array(
                            'slug','contact_person', 'contact_no', 'email',
                            'timezone','language','currency','weight_unit'));        
            //convert attribute name into multi-lang format
            $localeNames = new CMap();
            foreach ($model->getLanguageKeys() as $language) {
                if ($language==$form->language)
                    $localeNames->add($language,$form->name);
                else
                    $localeNames->add($language,'');
            }
            $model->name = json_encode($localeNames->toArray());
            logTrace(__METHOD__.' applied shop attributes',$model->attributes);
        }
        //common assignment
        $model->account_id = $user;
        $model->status = Process::SHOP_REQUEST;
        
        $this->validate($user, $model, false);
            
        return $this->execute($model, array(
                                'insert'=>self::EMPTY_PARAMS,
                                'updateAddress'=>self::EMPTY_PARAMS,
                                self::WORKFLOW=>array(
                                    'condition'=>'Applied by user = '.$model->account_id,
                                    'action'=>WorkflowManager::ACTION_APPLY,
                                    'decision'=>WorkflowManager::DECISION_NULL,
                                    'saveTransition'=>true,
                                    'transitionBy'=>$user,
                                ),
                                'recordActivity'=>Activity::EVENT_APPLY,
                                self::ELASTICSEARCH=>'saveSearchIndex',//refer to SearchableBehavior
                            ),
                            Shop::SCENARIO_MANUAL_SLUG); //set scenario to disable auto slug             
    }
    /**
     * Create shop
     * A default "prototype" shop will be created and auto-approved - no need approval process
     * Merchant has to further update to make it a normal shop - move it to "offline"
     * 
     * @param integer $user Session user id
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$checkRole=true)
    {        
        if ($checkRole && !AuthAssignment::model()->hasRole($user,Role::MERCHANT)){
            throw new CException(Sii::t('sii','Unauthorized Access: User is not a merchant.'));
        }

        //[1]create prototype shop
	Yii::import("shops.models.ShopPrototypeForm");
        $form = new ShopPrototypeForm($user);
        $model = new Shop();
        $model->attributes = $form->getAttributes([
            'name','status','account_id',
            'slug','contact_person', 'contact_no', 'email',
            'timezone','language','currency','weight_unit','category'
        ]);        
        //logTrace(__METHOD__.' shop prototype',$model->attributes);
        
        //[2]validate created prototype
        if (!$model->validate()) {
            logError(__METHOD__.' error',$model->getErrors());
            throw new CException(Sii::t('sii','Validation Error'));
        }
        //[3]save
        $model->save();
                
        $this->validate($user, $model, false);
        
        //[4] Create standard preset pages / objects
        $model->createPresetPages($user);
        $model->createPresetProductCategories();
        
        return $model;
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
        if (!$model->updatable($user))
            throw new ServiceValidationException(Sii::t('sii','Object is not updatable.'));

        $this->validate($user, $model, $checkAccess);
        //do a first update, and get the model latest data (esp the shop logo)
        $model = $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'updateSlugAsSubdomain'=>self::EMPTY_PARAMS,
            'updateAddress'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
            //self::ELASTICSEARCH=>'saveSearchIndex',//not required now as shop name change is not supported currently
        ),$model->prototype()?Shop::SCENARIO_MANUAL_SLUG:null);//set scenario for prototype to disable auto slug
        
        if ($model->prototype()){
            $model->refresh();//get the model latest data
            $model = $this->execute($model, array(
                self::WORKFLOW=>array(
                    'condition'=>'Changed by user = '.$model->account_id,
                    'action'=>WorkflowManager::ACTION_CHANGE,
                    'decision'=>WorkflowManager::DECISION_NULL,
                    'saveTransition'=>true,
                    'transitionBy'=>$user,
                ),
                self::ELASTICSEARCH=>'saveSearchIndex',
            ),self::NO_VALIDATION);            
        }
        return $model;
    }
    /**
     * Update shop theme
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param array $params Add on params pair to save as part of theme config
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateTheme($user,$model,$saveAsCurrent=false,$params=[],$checkAccess=true)
    {
        if (!$model instanceof ShopTheme)
            throw new ServiceValidationException(Sii::t('sii','Invalid service model'));

        if (!$model->shop->updatable($user))
            throw new ServiceValidationException(Sii::t('sii','Object is not updatable.'));

        $this->validate($user, $model->shop, $checkAccess);
        return $this->execute($model, [
            'saveTheme'=>$saveAsCurrent,
            'saveParams'=>$params,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ]);
    }    
    /**
     * Update shop settings
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param string $setting Which setting is being update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function updateSettings($user,$model,$setting,$checkAccess=true)
    {
        if (!$model instanceof ShopSetting)
            throw new ServiceValidationException(Sii::t('sii','Invalid service model'));
        
        if (!$model->shop->updatable($user))
            throw new ServiceValidationException(Sii::t('sii','Object is not updatable.'));
        
        $this->validate($user, $model->shop, $checkAccess);
        
        $operations = ['save'=>self::EMPTY_PARAMS];
        
        return $this->execute($model, array_merge($operations,[
            'recordActivity'=>Activity::EVENT_UPDATE,
        ]));
    }
    /**
     * Approve shop application
     * 
     * @param integer $user Session user id
     * @param CModel $model Shop model to approve
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function approve($user,$model,$transition)
    {
        if (!AuthAssignment::model()->hasRole($user,Role::SHOPS_MANAGER))
            throw new CException(Sii::t('sii','Unauthorized user.'));
        
        $model->setScenario($transition->decision);
        if (!$model->validate()) {
            logError(__METHOD__.' error',$model->getErrors());
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        $model->setOfficer($user);
        $model->setAccountOwner('officerAccount');
        $this->ownerAttribute = 'id';
        $result = $this->runWorkflow(
                        $user,
                        $model, 
                        $transition, 
                        Transition::SCENARIO_C1_D, 
                        Activity::EVENT_APPROVE, 
                        'pendingApproval');
        
        return $result;
    }      
    /**
     * Start first shop of merchant
     * For first shop, a default "prototype" shop will be created and auto-approved - no need approval process
     * Merchant has to further update to make it a normal shop - move it to "offline"
     * 
     * @param integer $user Session user id
     * @return CModel $model
     * @throws CException
     */
    public function startFirstShop($user)
    {
        //[1] First check if user has any shop; if yes, return error
        if (Shop::model()->count('account_id= '.$user)>0)
            throw new CException(Sii::t('sii','Unauthorized Access'));
        
        //[2]create prototype shop
        $model = $this->create($user,false);//skip role checking
        
        //[3]assign Merchant role if not found
        $this->_assignMerchantRole($model);
        
        return $model;
    }      
    /**
     * Suspend shop  
     * 
     * @param integer $user Session user id
     * @param integer $shop Shop id to suspend
     * @return CModel $shop
     * @throws CException
     */
    public function suspend($user,$shop)
    {
        if (!AuthAssignment::model()->hasRole($user,Role::SHOPS_MANAGER))
            throw new CException(Sii::t('sii','Unauthorized user.'));

        $model = Shop::model()->findByPk($shop);
        if ($model===null)
            throw new CException(Sii::t('sii','Shop not found'));

        $model->status = Process::SHOP_SUSPENDED;
        $this->validate($user, $model, false);

        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_SHOP_SUSPEND,
                'account'=>$user,
            ),
        ));
        
    }      
    /**
     * Resume shop  
     * 
     * @param integer $user Session user id
     * @param integer $shop Shop id to resume
     * @return CModel $shop
     * @throws CException
     */
    public function resume($user,$shop)
    {
        if (!AuthAssignment::model()->hasRole($user,Role::SHOPS_MANAGER))
            throw new CException(Sii::t('sii','Unauthorized user.'));
        
        $model = Shop::model()->findByPk($shop);
        if ($model===null)
            throw new CException(Sii::t('sii','Shop not found'));

        $this->validate($user, $model, false);

        return $this->execute($model, array(
            'resume'=>Process::SHOP_OFFLINE,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_SHOP_RESUME,
                'account'=>$user,
            ),
        ));
    }        
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to delete
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        if (!$model->deletable($user))
            throw new ServiceValidationException(Sii::t('sii','Object is not deletable.'));

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
     * Assign merchant role if not found
     * @param type $model
     */
    private function _assignMerchantRole($model)
    {
        if (!AuthAssignment::model()->hasRole($model->account_id,Role::MERCHANT)){
            Rights::assign(Role::MERCHANT, $model->account_id);
            logInfo(__METHOD__.' Role '.Role::MERCHANT.' assigned to user='.$model->account_id);
        }
    }
    
}
