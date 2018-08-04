<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BraintreeApi
 *
 * @author kwlok
 */
class BraintreeApi 
{
    protected $merchantAccountId;//Note that merchant account is tied to a specific currency
    /**
     * Construct Braintree API
     * Includes Braintree's API files and sets up Braintree configuration
     * 
     * @param type $environment sandbox or production
     * @param type $merchantId
     * @param type $publicKey
     * @param type $privateKey
     * @param type $merchantAccountId optional; If not set Braintree will use default merchant account
     */
    public function __construct($environment,$merchantId,$publicKey,$privateKey,$merchantAccountId=null) 
    {
        $path = Yii::getPathOfAlias('common.vendors.braintree');
        require_once($path.'/lib/Braintree.php');
        Braintree\Configuration::environment($environment);
        Braintree\Configuration::merchantId($merchantId);
        Braintree\Configuration::publicKey($publicKey);
        Braintree\Configuration::privateKey($privateKey);
        $this->merchantAccountId = $merchantAccountId;
    }
    /**
     * Create client token
     * @return type
     */
    public function getClientToken()
    {
        return Braintree\ClientToken::generate();
    }
    /**
     * Create sale transaction
     * @param array $options 
     */
    public function createSaleTransaction($options)
    {
        //Auto assign merchantAccountId if not set in $options
        if (!isset($options['merchantAccountId']))
            $options['merchantAccountId'] = $this->merchantAccountId;

        try {
            $response = Braintree\Transaction::sale($options);
            if ($response->success) {
                logInfo(__METHOD__.' ok; transaction id = '.$response->transaction->id);
                return ['success'=>true,'response' => $response];
            } else if ($response->transaction) {
                logError(__METHOD__,$response);
                return ['success'=>false,'response' => $response];
            } else {
                logError(__METHOD__,$response);
                return ['success'=>false,'response' => $response];
            }        
            
        } catch (Braintree\Exception $ex) {
            logError(__METHOD__.' error = "'.$ex->getMessage(),$ex->getTraceAsString().'"');
            return ['success'=>false,'response' => (object)['message'=>$ex->getMessage()]];
        }
    }
    /**
     * Create customer
     * @param array $options 
     */    
    public function createCustomer($options)
    {
        $response = Braintree\Customer::create($options);
        if ($response->success) {
            logInfo(__METHOD__.' token',$response->customer->paymentMethods[0]->token);
        }
        return $this->handleResponse($response, 'customer');
    }
    /**
     * Find customer
     * @param string $id 
     */    
    public function findCustomer($id)
    {
        try {
            $response = Braintree\Customer::find($id);
            logTrace(__METHOD__.' found!',$response);
            return $response;//instance of Braintree\Customer
        } catch (Braintree\Exception $e) {
            logWarning(__METHOD__,$e->getTraceAsString());
            return false;
        }
    }    
    /**
     * Find merchant account
     * @param string $id 
     */    
    public function findMerchantAccount($id=null)
    {
        try {
            if (!isset($id))
                $id = $this->merchantAccountId;
            
            $response = Braintree\MerchantAccount::find($id);
            logTrace(__METHOD__.' found!',$response);
            return $response;//instance of Braintree\MerchantAccount
        } catch (Braintree\Exception $e) {
            logError(__METHOD__,$e->getTraceAsString());
            return false;
        }
    }     
    /**
     * Create subscription
     * @param type $paymentMethodToken
     * @param type $planId
     * @return type
     */
    public function createSubscription($paymentMethodToken,$planId,$moreParams=[])
    {
        $params = [
            'paymentMethodToken' => $paymentMethodToken,
            'planId' => $planId,
            'merchantAccountId' => $this->merchantAccountId,//set through constructor
        ];
        $response = Braintree\Subscription::create(array_merge($params, $moreParams));    
        return $this->handleResponse($response, 'subscription');
    }
    /**
     * Update subscription
     * @param string $subscriptionId
     * @param string $paymentMethodToken
     * @param array $moreParams
     * @return type
     */
    public function updateSubscription($subscriptionId,$paymentMethodToken,$moreParams=[])
    {
        try {
            $params = [
                'paymentMethodToken' => $paymentMethodToken,
            ];
            $response = Braintree\Subscription::update($subscriptionId,array_merge($params, $moreParams));    
            return $this->handleResponse($response, 'subscription');
        } catch (Braintree\Exception $e) {
            logError(__METHOD__.' error',$e->getTraceAsString());
            return false;
        }
    }    
    /**
     * Cancel subscription
     * @param type $paymentMethodToken
     * @param type $planId
     * @return type
     */
    public function cancelSubscription($subscriptionId)
    {
        try {
            $response = Braintree\Subscription::cancel($subscriptionId);
            return $this->handleResponse($response, 'subscription');
        } catch (Braintree\Exception $e) {
            logError(__METHOD__.' error',$e->getTraceAsString());
            return false;
        }
    }  
    /**
     * Find subscription
     * @param string $id 
     */    
    public function findSubscription($id)
    {
        try {
            $response = Braintree\Subscription::find($id);
            logTrace(__METHOD__.' found!',$response);
            return $response;//instance of Braintree\Subscription
        } catch (Braintree\Exception $e) {
            logError(__METHOD__,$e->getTraceAsString());
            return false;
        }
    }        
    /**
     * Find payment method
     * @param string $token 
     */    
    public function findPaymentMethod($token)
    {
        try {
            $response = Braintree\PaymentMethod::find($token);
            logTrace(__METHOD__.' found!',$response);
            return $response;//instance of Braintree\CreditCard
        } catch (Braintree\Exception $e) {
            logError(__METHOD__,$e->getTraceAsString());
            return false;
        }
    }     
    /**
     * Create payment method
     * @param string $customerId 
     * @param string $nonce 
     */    
    public function createPaymentMethod($customerId,$nonce,$default=false)
    {
        try {
            $response = Braintree\PaymentMethod::create([
                'customerId' => $customerId,
                'paymentMethodNonce' => $nonce,
                'options' => [
                    'makeDefault' => $default,
                    'failOnDuplicatePaymentMethod' => true,
                    'verifyCard' => true,
                ]
            ]);
            return $this->handleResponse($response, 'paymentMethod','maskedNumber');
        } catch (Braintree\Exception $e) {
            logWarning(__METHOD__.' error',$e->getTraceAsString());
            return false;
        }
    }      
    /**
     * Update payment method
     * @param string $token 
     * @param string $nonce 
     */    
    public function updatePaymentMethod($token,$nonce)
    {
        try {
            $response = Braintree\PaymentMethod::update($token,[
                'paymentMethodNonce' => $nonce,
                'options' => [
                    'makeDefault' => true,
                    'verifyCard' => true
                ]
            ]);
            return $this->handleResponse($response, 'paymentMethod','maskedNumber');
        } catch (Braintree\Exception $e) {
            logWarning(__METHOD__.' error',$e->getTraceAsString());
            return false;
        }
    }      
    /**
     * Retry charge 
     * @param type $subscriptonNo
     * @param type $amount
     * @return type
     */
    public function retryCharge($subscriptonNo,$amount)
    {
        try {
            $response = Braintree\Subscription::retryCharge($subscriptonNo,$amount);
            if ($response->success) {
                $result = Braintree\Transaction::submitForSettlement(
                    $response->transaction->id
                );
                return $this->handleResponse($result, 'transaction');
            }  
            else{
                logError(__METHOD__.' error!',$response->errors,false);
                throw new Braintree\Exception('Fail to retry charge for '.$subscriptonNo);
            }
        } catch (Braintree\Exception $e) {
            logError(__METHOD__,$e->getTraceAsString());
            return false;
        }        
    }    
    /**
     * Generic method to handle response return by Braintree
     * @param type $response
     * @param type $object
     * @return type
     */
    protected function handleResponse($response,$object,$objectField='id')
    {
        logTrace(__METHOD__.' response object',$response);
        if ($response->success) {
            logInfo(__METHOD__.' ok; '.$object.' '.$objectField.' = '.$response->{$object}->{$objectField});
            return ['success'=>true,'response' => $response];
        } else {
            foreach($response->errors->deepAll() AS $error) {
                logError(__METHOD__.' '.$error->code . ": " . $error->message);
            }
            return ['success'=>false,'response' => $response];
        }        
    }
}
