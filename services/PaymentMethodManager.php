<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeApiTrait');
/**
 * Description of PaymentMethodManager
 *
 * @author kwlok
 */
class PaymentMethodManager extends ServiceManager 
{
    use BraintreeApiTrait;
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
        if (!isset($model->name))
            $model->name = PaymentMethod::getName($model->method);
        $model->status =  Process::PAYMENT_METHOD_OFFLINE;

        $model->account_id = $user;

        //verify Braintree payment method, if any
        $this->verifyBraintreeMerchantAccount($model);
        
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_CREATE,
                'description'=>$model->name,
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
        $this->validate($user, $model, $checkAccess);
        
        //verify Braintree payment method, if any
        $this->verifyBraintreeMerchantAccount($model);
        
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_UPDATE,
                'description'=>$model->name,
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
                        'description'=>$model->name,
                        'account'=>$user,
                    ),
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    }

    protected function verifyBraintreeMerchantAccount($model)
    {
        //validate braintree merchant account for Credit card payment method
        if (in_array($model->method,[PaymentMethod::BRAINTREE_CREDITCARD,PaymentMethod::BRAINTREE_PAYPAL])){
            $config = [];
            foreach ($this->braintreeParams as $param) {
                $config[$param] = $model->getParamsAttributeAsString($param);
            }
            $braintree = $this->constructBraintreeApi($config);
            if ($braintree->findMerchantAccount()==false){
                $merchantAccountId = $config['merchantAccountId'];

                logError(__METHOD__." Invalid merchant account Id '$merchantAccountId': It could not be found in Braintree");
                throw new ServiceValidationException(Sii::t('sii','Merchant Account Id could not be found in Braintree'));
            }
        }
    }
}
