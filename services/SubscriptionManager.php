<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.WorkflowManager");
Yii::import("common.services.exceptions.*");
/**
 * Description of SubscriptionManager
 *
 * @author kwlok
 */
class SubscriptionManager extends ServiceManager 
{
    /**
     * Activate Subscription
     * Two triggering scenario:
     * (1) When first time went active
     * (2) When transit from pastdue to active
     * 
     * @return CModel $model
     * @throws CException
     */
    public function activate($model)
    {
        $this->validateSubscription($model, 'activable');
        
        if ($model->isPastdue)
            return $this->reactivate($model, WorkflowManager::DECISION_YES);
        
        $model = $this->transitionModel($model, 'activate');
        
        logInfo(__METHOD__.' ok', $model->attributes);
        
        return $model;        
    } 
    /**
     * Reactivate Subscription
     * One triggering scenario:
     * (1) When model is current past due but retry charging is successful
     * 
     * @return CModel $model
     * @throws CException
     */
    public function reactivate($model,$decision)
    {
        $this->validateSubscription($model, 'getIsPastdue');
        
        $model = $this->transitionModel($model, 'reactivate',$decision);
        
        logInfo(__METHOD__.' ok', $model->attributes);
        
        return $model;        
    }         
    /**
     * Deactivate Subscription
     * Two scenarios:
     * [1] When subscription is active (normally for plan upgrades)
     * [2] When subscription is expired (normally for plan re-subscribe / renewal)
     * 
     * @return CModel $model
     * @throws CException
     */
    public function deactivate($model)
    {
        $this->validateSubscription($model, 'deactivable');
                
        $model = $this->transitionModel($model, 'deactivate');
        
        logInfo(__METHOD__.' ok', $model->attributes);
        
        return $model;        
    }     
    /**
     * Expire Subscription
     * 
     * @return CModel $model
     * @throws CException
     */
    public function expire($model)
    {
        $this->validateSubscription($model, 'expirable');
                
        $model = $this->transitionModel($model, 'expire');
        
        logInfo(__METHOD__.' ok', $model->attributes);
        
        return $model;        
    }     
    /**
     * Pastdue Subscription
     *
     * @return CModel $model
     * @throws CException
     */
    public function pastdue($model,$daysPastDue=null)
    {
        if (isset($daysPastDue) && $model->isDunningPeriodOver){
            $model = $this->suspend($model,$daysPastDue);
        }
        else {   
            $this->validateSubscription($model, 'pastdueable');
            $model = $this->transitionModel($model, 'pastdue',WorkflowManager::DECISION_YES,true);//trigger notification
            logInfo(__METHOD__.' ok, with days past due = '.$daysPastDue, $model->attributes);
        }
        
        return $model;  
    }
    /**
     * Suspend Subscription
     *
     * @return CModel $model
     * @throws CException
     */
    public function suspend($model,$daysPastDue)
    {
        $this->validateSubscription($model, 'isSuspendable');

        if (isset($daysPastDue) && $model->isDunningPeriodOver) {
            //note that $daysPastDue here is for information
            $model = $this->transitionModel($model, 'suspend',WorkflowManager::DECISION_YES,true);//trigger notification
            logInfo(__METHOD__.' suspended ok, dunning period over for days pastdue '.$daysPastDue, $model->attributes);
            return $model;
        }
        else {
            logInfo(__METHOD__.' condition ok, no need suspension, but send pastdue notification, $daysPastDue = '.$daysPastDue, $model->attributes);
            //but notify customer about past due subscription again
            $this->execute($model, [
                self::NOTIFICATION=>self::EMPTY_PARAMS,
            ],self::NO_VALIDATION);
            return false;
        }
    }

    protected function validateSubscription($model,$filterMethod) 
    {
        if (!$model instanceof Subscription){
            logError(__METHOD__.' error: invalid model',get_class($model));
            throw new CException(Sii::t('sii','Invalid Model'));
        }
        if (!$model->{$filterMethod}()) {
            logError(__METHOD__.' error: model not '.$filterMethod,$model->attributes);
            throw new CException(Sii::t('sii','Invalid Status'));
        }
    }
    /**
     * Transition model as administrator
     * @param type $model
     * @return type
     */
    protected function transitionModel($model,$action,$decision=WorkflowManager::DECISION_YES,$notification=false)
    {
        $user = Account::SYSTEM;
        
        $model = $this->runModelAsAdministrator($model,$user);
        
        $this->validate($user, $model, true);
        
        $process = WorkflowManager::getProcessBeforeAction($model->tableName(), ucfirst($action));

        if ((is_array($process) && !in_array($model->status, $process))||
            ($process!=$model->status)){
            logError(__METHOD__.' invalid process for action '.$action.', model='.get_class($model).', status='.$model->status,$process);
            throw new ServiceValidationException(Sii::t('sii','Invalid transition status'));
        }
        
        $operations = [
            self::WORKFLOW=>[
                'transitionBy'=>$user,
                'condition'=>'Run as administrator: '.$user,
                'action'=>$action,
                'decision'=>$decision,
                'saveTransition'=>true,
            ],
            'recordActivity'=>[
                'event'=>$action,
                'account'=>$user,
                'icon_url'=>$model->getActivityIconUrl(),
                'description'=>$model->getActivityDescription(),
            ],
        ];
        
        return $this->execute($model, $operations, $model->getScenario(),$notification);
    }    
    /**
     * Set Subscription to charged and record transaction details
     * 
     * @param Subscription $model
     * @param array $transactionData
     *  E.g. array(
     *          'id' => 'h5kbns',
     *          'createdAt' => 'date time'
     *          'currencyIsoCode' => 'MYR',
     *          'amount' => '10.00',
     *          'cardType' => 'MasterCard',
     *          'last4' => '4444',
     *          'processorAuthorizationCode' => '6GFSXV',
     *          'processorResponseText' => 'Approved',
     *       )
     * @return CModel $model
     * @throws CException
     */
    public function charge($model,$transactionsData)
    {
        $this->validateSubscription($model, 'chargeable');
                
        //either pastdue or active subscription will have same logic
        $model->charged = Subscription::CHARGED;
        $model->transaction_data = json_encode($transactionsData);
        
        if (!$model->validate()){
            logError(__METHOD__.' validation errors',$model->getErrors());
            $this->throwValidationErrors($model->getErrors());
        }
        
        $model->save();
        
        $this->createReceipt($model, $transactionsData);
        
        logInfo(__METHOD__.' ok', $model->attributes);
        
        return $model;  
    }
    /**
     * Renew a subscription
     * @param type $subscriptionNo
     * @param type $newStartdate
     * @param type $newEndDate
     * @param array $transactionData
     * @return type
     */
    public function renew($subscriptionNo,$newStartdate,$newEndDate,$transactionsData)
    {
        //picking up the previous subscription (status will be ACTIVE)
        $previousSubscription = Subscription::model()->subscriptionNo($subscriptionNo)->status(Process::SUBSCRIPTION_ACTIVE)->find();
        if ($previousSubscription!=null){//found
                
            $transaction = Yii::app()->db->beginTransaction();
        
            try {
                //[1]update old billing period to expired
                $previousSubscription = $this->expire($previousSubscription);
                //[2]create new billing period
                $model = new Subscription('create');
                $model->subscription_no = $previousSubscription->subscription_no;
                $model->account_id = $previousSubscription->account_id;
                $model->package_id = $previousSubscription->package_id;
                $model->plan_id = $previousSubscription->plan_id;
                $model->start_date = $newStartdate;
                $model->end_date = $newEndDate;
                $model->status = Process::SUBSCRIPTION_PENDING;
                $model->charged = Subscription::CHARGED;
                $model->transaction_data = json_encode($transactionsData);
                if (!$model->validate()){
                    logError(__METHOD__.' validation errors',$model->getErrors());
                    $this->throwValidationErrors($model->getErrors());
                }
                $model->save();
                
                //[3]create receipt for renew subscription
                $this->createReceipt($model, $transactionsData);
                                
                //[4]activate subscription
                $model = $this->activate($model);

                $transaction->commit();
                
                logInfo(__METHOD__.' ok', $model->attributes);

                return $model;  

            } catch (CException $e) {
                logError(__METHOD__.' rollback: '.$e->getMessage().' >> '.$e->getTraceAsString(),[],false);
                $transaction->rollback();
                throw new ServiceOperationException($e->getMessage());
            }        
        }
        else {
            logError(__METHOD__.' previous subscription not found!',$subscriptionNo);
            return false;
        }

    }    
    /**
     * Create receipt
     * @see BraintreeApiTrait::createTraceNo()
     * @param Subscription $subscription
     * @return type
     * @throws CException
     */
    public function createReceipt($subscription,$transactionsData)
    {
        if (!$subscription instanceof Subscription)
            throw new CException(Sii::t('sii','Invalid Subscription'));
        if (!$subscription->isCharged)
            throw new CException(Sii::t('sii','Subscription has not been charged'));
        
        //Locate transaction data of the subscription
        $transaction = [];
        foreach ($transactionsData as $_t) {
            if ($_t['subscriptionId']==$subscription->subscription_no){
                $transaction = $_t;
                break;
            }
        }
        //Create and send receipt
        $receipt = $this->receiptManager->create($subscription->account_id,[
            'type'=>Receipt::TYPE_RECURRING,
            'currency'=>$transaction['currency'],
            'reference'=>Receipt::formatReferenceKey($subscription),
            'items'=>[
                [
                    'item'=>$subscription->plan->name,//item desc
                    'package_id'=>$subscription->package_id,
                    'package'=>$subscription->package->name,//support english name only
                    'plan_id'=>$subscription->plan_id,
                    'plan'=>$subscription->plan->name,//support english name only
                    'transaction_id'=>$transaction['id'],
                    'transaction_date' => $transaction['createdAt'],
                    'amount' => $transaction['amount'],
                    'currency' => $transaction['currency'],
                    'subscription_no'=>$subscription->subscription_no,
                    'service_start' => $subscription->start_date,
                    'service_end' => $subscription->end_date,
                    'last4' => $transaction['last4'],
                    'cardType' => $transaction['cardType'],
                ],//one item
            ],
        ]);

        //Search back payment record, and change it payment reference no (set to $subscription->subscription_no by payment gateway) to receipt no
        $this->receiptManager->updatePaymentReference($subscription->subscription_no,$receipt->receipt_no);

        return $receipt;
    }  
}
