<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.AccountManagerTrait");
Yii::import("common.modules.plans.models.*");
Yii::import("common.modules.accounts.users.IdentityCustomer");
Yii::import("customers.models.*");
/**
 * Description of CustomerManager
 *
 * @author kwlok
 */
class CustomerManager extends ServiceManager 
{
    use AccountManagerTrait;
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
        return $this->execute($model, [
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_CREATE,
                'account'=>$user,
                'description'=>$model->alias_name==null?Sii::t('sii','New Customer'):$model->alias_name,
            ],
        ],'create');//set create scenario
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
        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_UPDATE,
                'account'=>$user,
                'description'=>$model->alias_name==null?Sii::t('sii','Update {object}',['{object}'=>$model->displayName()]):$model->alias_name,
            ],
        ]);
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
        return $this->execute($model, [
                'recordActivity'=>[
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                    'description'=>$model->alias_name==null?Sii::t('sii','Delete {object}',['{object}'=>$model->displayName()]):$model->alias_name,
                ],
                'delete'=>self::EMPTY_PARAMS,
            ],'delete');
    }
    /**
     * Save (create / update) record based on order
     * 
     * For customer record the total spent does not imply that order is paid;
     * Need to further check order payment status to infer
     * 
     * @param Order $order
     * @param type $paid
     * @return boolean Return true if success
     * @throws CException
     */
    public function saveRecord($order)
    {
        if (!($order instanceof Order))
            throw new CException(Sii::t('sii','Invalid order'));
        
        $merchantId = $order->shop->account_id;
        $customerId = $order->address->email;
        $subscription = Subscription::model()->mine($merchantId)->active()->notExpired()->find();
        if (Subscription::hasService($subscription,Feature::$trackCustomerBehaviors)){
            $customer = Customer::model()->retrieveRecord($merchantId,$customerId)->find();
            $customerData = new CustomerData();
            if ($customer===null){
                $customer = new Customer();
                $customer->account_id = $merchantId;//set merchant account
                $customer->customer_id = $customerId;//set customer account
                $customerData->addShopData($order->shop_id,new CustomerShopData($order->grand_total,1,$order->id));
            }
            else {
                $customerData = $customer->getCustomerData();
                $found = false;
                foreach ($customerData->shop_data as $shop => $shopData) {
                    if ($order->shop_id==$shop){
                        $customerData->shop_data[$shop]['total_spent'] += $order->grand_total;
                        $customerData->shop_data[$shop]['total_orders'] += 1;//plus one order
                        $customerData->shop_data[$shop]['last_order_id'] = $order->id;
                        $found = true;
                    }
                }
                if (!$found)
                    $customerData->addShopData($order->shop_id,new CustomerShopData($order->grand_total,1,$order->id));
            }

            $customerData->last_order_id = $order->id;
            $customerData->last_shop_id = $order->shop_id;
            $customer->data = json_encode($customerData->toArray());//set customer data
            $customer->alias_name = $order->address->recipient;//set customer name
            $customer->setAddressData(new CustomerAddressData($order->address->address1,$order->address->address2,$order->address->postcode,$order->address->city,$order->address->state,$order->address->country,$order->address->mobile));
            if ($customer->save())
                logTrace(__METHOD__.' ok; customer record',$customer->attributes);
            else
                logError(__METHOD__.' Failed to save customer record',$customer->errors);
        }
        else
            logInfo(__METHOD__.' Skip! Merchant does not subscribe to '.Feature::$trackCustomerBehaviors);
        return true;
    }
    /**
     * Register customer account
     * Use case: Customer will have to register account by providing email and password (minimally)
     * System will sign up an local account but pending last thing to be accomplished by users:
     * [1] Activation (also verifying email)
     * 
     * @param CFormModel $form RegistrationForm
     * @return CModel $model
     * @throws CException
     */
    public function register($form)
    {
        if (!$form instanceof RegistrationForm)
            throw new CException(Sii::t('sii','Invalid form'));
        
        //next do validation
        if(!$form->validate()){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }

        // Create customer account
        $model = new CustomerAccount('create');
        $model->shop_id = $form->shop_id;
        $model->email = $form->email;
        $model->password = CPasswordHelper::hashPassword($form->password);
        $model->reg_ip = request()->getUserHostAddress();
        $model->setActivationToken($model->email);
        $model->status = Process::SIGNUP;

        $this->validate(Account::GUEST, $model, false);

        $account = $this->execute($model, [
            'register'=>self::EMPTY_PARAMS,
            'transferOrders'=>self::EMPTY_PARAMS,//TODO This is automatic; User may be prompted to accept transfer or discard orders made as guest account
            self::NOTIFICATION=>self::EMPTY_PARAMS,
        ]);

        // Update customer profile data
        $profileUpdate = false;
        if ($form->hasFirstName()){
            $account->profile->first_name = $form->first_name;
            $profileUpdate = true;
        }
        if ($form->hasLastName()){
            $account->profile->last_name = $form->last_name;
            $profileUpdate = true;
        }
        if ($form->hasAliasName()){
            $account->profile->alias_name = $form->alias_name;
            $profileUpdate = true;
        }
        if ($form->address->hasAddress()){
            $addressData = new CustomerAddressData($form->address->address1,$form->address->address2,$form->address->postcode,$form->address->city,$form->address->state,$form->address->country,$form->mobile);
            $account->profile->setAddressData($addressData);
            $profileUpdate = true;
        }
        if ($profileUpdate){
            $account->profile->update();
            logTrace(__METHOD__.' Customer profile information saved!',$account->profile->attributes);
        }
        
        logInfo(__METHOD__.' ok');
        
        return $account;
    }      
    /**
     * Resend activation email
     * 
     * @param integer $shop The shop id
     * @param string $email 
     * @return CModel $model
     * @throws CException
     */
    public function resendActivationEmail($shop,$email)
    {
        Yii::import("shop.models.RegistrationForm");
        $form = new RegistrationForm($shop);//for validation use
        $form->email = trim($email);
        if(!$form->validate(['email'])){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Invalid email address'));
        }
        
        $model = CustomerAccount::model()->findByAttributes(['shop_id'=>$shop,'email'=>$form->email]);
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));

        if ($model->pendingActivation()){
            $model->setActivationToken($model->email);
            return $this->execute($model, [
                'update'=>self::EMPTY_PARAMS,
                self::NOTIFICATION=>self::EMPTY_PARAMS,
            ]);
        }
        else
            throw new CException(Sii::t('sii','Invalid Account'));
    }      
    /**
     * Activate customer account
     * 
     * @param integer $shop the shop
     * @param string $token activation token
     * @throws CException
     */
    public function activate($shop,$token)
    {
        $acct = $this->validateActivationToken($token,['shop_id'=>$shop]);
        $cid = IdentityCustomer::createCid($shop, $acct->email);
        return $this->activateAccountInternal($acct,null,new IdentityCustomer($cid,$acct->password));
    }          
    /**
     * @see AccountManagerTrait
     */    
    protected function assignRole($role,$account) 
    {
        Account::assignSubAccountRole($role, Account::TYPE_CUSTOMER, $account->id);
    }
    /**
     * @see AccountManagerTrait
     */
    protected function findAccount()
    {
        return CustomerAccount::model()->mine()->find();
    }
    /**
     * A wrapper method to find account
     * @return type
     */
    protected function findAccountByEmail($email,$emailCondition=[])
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['email'=>$email]);
        if (!empty($emailCondition)){
            $criteria->addColumnCondition($emailCondition);
        }
        return CustomerAccount::model()->find($criteria);
    }
    /**
     * A wrapper method to find account
     * @return type
     */
    protected function findAccountByToken($token,$extraCriteria)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['activate_str'=>base64_decode($token)]);
        if (!empty($extraCriteria)){
            $criteria->addColumnCondition($extraCriteria);
        }
        return CustomerAccount::model()->find($criteria);
    }
    
}
