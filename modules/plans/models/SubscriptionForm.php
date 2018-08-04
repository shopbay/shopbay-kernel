<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.plugins.braintreeCreditCard.components.BraintreeApiTrait');
Yii::import('common.modules.billings.models.Billing');
/**
 * Description of SubscriptionForm
 *
 * @author kwlok
 */
class SubscriptionForm extends CFormModel
{
    use BraintreeApiTrait;
    
    const SCENARIO_FREE    = 'free';
    const SCENARIO_PAYMENT = 'payment';
    public static $packages;//array of package
    public $package;
    public $packageType;
    public $plans;//array of plan choices according to selected package
    public $plan;//selected plan id
    public $planData = [];//cover plan type, plan price and plan currency
    public $paymentData = [];
    public $braintreeData = [];
    public $trace_no;//payment trace no
    public $shop_id;//the shop that is tied to subscription
    public $payment_token;//the payment token used to pay subscription
    /**
     * Constructor.
     * @param type $packages
     * @param type $currency Set the currency charged for the subscription: e.g. SGD, MYR
     * @param type $scenario
     */
    public function __construct($packages=null,$currency=null,$scenario='')
    {
        static::$packages = $packages;
        $this->createBraintreeData($currency);
        parent::__construct($scenario);
    }    
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['package, plan', 'required'],
            ['shop_id, payment_token', 'safe'],
            ['paymentData,', 'rulePayment','on'=>self::SCENARIO_PAYMENT],
        ];
    }
    /**
     * Payment validation rule
     * Either payment nonce or payment token must exists; But not both at the same time
     * [1] Payment token is required nnly required when shop is present; But FREE_TRIAL, FREE, or , payment token does not exists yet! 
     * [2] Payment nonce is required for New customer first time buying subscription filling up credit card form, and payment token will be created after that.
     */
    public function rulePayment($attribute, $params)
    {
        logTrace(__METHOD__,$this->attributes);
        
        if (!$this->requiresPayment || in_array($this->plan,[Plan::FREE_TRIAL,Plan::FREE])){
            /**
             * For Free plan no need to validate payment data; Else it will not be sent out to PaymentGateway\
             * @see PlanManager::subscribe()
             * @see BillingManager::paySubscription()
             */
            return;
        }
        
        if ($this->paymentNonce!=null && $this->payment_token!=null)
            $this->addError($attribute,Sii::t('sii','More than one payment method found'));//this should not happen unless got system bug
        
        if ($this->paymentNonce==null && $this->payment_token==null)
            $this->addError($attribute,Sii::t('sii','"{object}" cannot be blank',['{object}'=>$this->getAttributeLabel('payment_token')]));
    }      
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'package'=>Sii::t('sii','Package'),
            'plan'=>Sii::t('sii','Pricing'),
            'shop'=>Sii::t('sii','Shop'),
            'currentPackage'=>Sii::t('sii','Current Plan'),
            'payment_token'=>Sii::t('sii','Payment Token'),
        ];
    }
    /**
     * This shows the package list for user to choose during subscription checkout
     * @return type
     */
    public function getPackageChoices()
    {
        $packages = [];
        
        foreach (self::$packages->data as $package) {
            logTrace(__METHOD__.' package',$package->name);
            $packages[$package->id] = Sii::t('sii',$package->name);
            if ($package->id==Package::FREE_TRIAL){
                $packages[$package->id] = $this->freeTrialName;
            }

            //remove subscription that user currently is subscribing to, when shop is present
            if (isset($this->shop_id) && user()->hasOnlineSubscription($this->shop_id)){
                if (user()->getOnlineSubscription($this->shop_id)->package_id==$package->id)
                    unset($packages[$package->id]);
            }            
            //remove non-business ready package 
            if (!$package->getParam(Package::$businessReady)){
                unset($packages[$package->id]);
                logTrace(__METHOD__.' businss NOT READY, removed package!',$package->id);
            }
            //remove custom package 
            if ($package->id==Package::CUSTOM){
                unset($packages[$package->id]);
                logTrace(__METHOD__.' Remove custom package!',$package->id);
            }
            
        }
        //check if user has try free trial before, if yes remove free trial as a choice
        if (user()->hasTrialBefore){
            unset($packages[Package::FREE_TRIAL]);
        }
        //check if user has try free plan before, if yes remove free plan as a choice
        if (user()->hasFreePlanBefore){
            unset($packages[Package::FREE]);
        }
        
        return $packages;
    }
    
    public function getHasPlans()
    {
        return !empty($this->plans);
    }

    public function getHasPackage()
    {
        return $this->package!=null;
    }
    
    public function loadPackage($id)
    {
        $this->package = $id;
        foreach (self::$packages->data as $pkg) {
            if ($pkg->id == $this->package){
                $this->packageType = $pkg->type;
                //prepare plans
                $plans = [];
                foreach ($pkg->plans as $index => $plan) {
                    $plans[$plan['id']] = $plan['currency'].' '.number_format($plan['price'],2);
                    if ($plan['type']==Plan::RECURRING)
                       $plans[$plan['id']] .= ' '.Plan::getRecurringsDesc($plan['recurring']);

                    if ($index==0)
                        $this->plan = $plan['id'];//default to the first plan;TODO should choose the lower prices one
                }
                $this->plans = $plans;
                break;
            }
        }
    }

    public function createPlanData($packageModel=null,$planModel=null)
    {
        $planAttributes = ['id','name','type','recurring','currency','price'];
        if ($packageModel instanceof Package && $planModel instanceof Plan){
            $this->package = $packageModel->id;
            $this->packageType = $packageModel->type;
            $this->plan = $planModel->id;
            $this->planData['package'] = Sii::t('sii',$packageModel->name);
            if ($packageModel->id==Package::FREE_TRIAL){
                $this->planData['package'] = $this->freeTrialName;
            }            
            foreach ($planAttributes as $attribute) {
                if ($attribute=='price')
                    $this->planData[$attribute] = number_format($planModel->$attribute,2);
                else
                    $this->planData[$attribute] = $planModel->$attribute;
            }
        }
        else {
            if (!isset($this->package))
                throw new CException(Sii::t('sii','Package not found'));
            if (!isset($this->plan))
                throw new CException(Sii::t('sii','Plan not found'));
        
            foreach (self::$packages->data as $pkg) {
                if ($pkg->id == $this->package){
                    $this->packageType = $pkg->type;
                    foreach ($pkg->plans as $index => $plan) {
                        if ($plan['id']==$this->plan){
                            $this->planData['package'] = $pkg['name'];
                            if ($pkg->id==Package::FREE_TRIAL){
                                $this->planData['package'] = $this->freeTrialName;
                            }                            
                            foreach ($planAttributes as $attribute) {
                                if ($attribute=='price')
                                    $this->planData[$attribute] = number_format($plan[$attribute],2);
                                else
                                    $this->planData[$attribute] = $plan[$attribute];
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
    
    public function getRequiresPayment()
    {
        if (isset($this->planData['price']))
            return $this->planData['price']>0;
        else 
            return true;//default to true
    }
    
    public function setPaymentNonce($nonce)
    {
        $this->paymentData['nonce'] = $nonce;  
    }    
    
    public function getPaymentNonce()
    {
        return isset($this->paymentData['nonce'])?$this->paymentData['nonce']:null;  
    }    
    /**
     * Payment nonce needed for payment gateway processing
     * For now (using braintree gateway), only payment nonce are required
     * Rest are for info for now
     * @param type $params
     */
    public function createPaymentNonce($params)
    {
        if (isset($this->planData['price'])&&$this->planData['price']>0){
            if ($this->payment_token==null && isset($params['payment_method_nonce'])){//new customer, has no payment token before
                $this->paymentData = ['nonce'=>$params['payment_method_nonce']];//mandatory field
                $optionalFields = ['order_amount'=>'amount','cc_type'=>'cardType','cc_lastTwo'=>'lastTwo','cc_icon_base_url'=>'iconBaseUrl'];
                foreach ($optionalFields as $externalField => $internalField) {
                    if (isset($params[$externalField]))
                        $this->paymentData[$internalField] = $params[$externalField];
                }
            }
        }  
    }    
    /**
     * Load the default payment token 
     * If shop is present, load from its existing token used to pay subscription
     * If not, load token from Billing table (token is created during the first time customer billing record is created)
     * @see BraintreeRecurringBillingGateway::process()
     * @return string
     */
    public function loadDefaultPaymentToken()
    {
        //check on shop level first
        if (isset($this->shop_id)){
            $subscription = user()->getCurrentSubscription($this->shop_id);
            $this->payment_token = $subscription->payment_token;
            logTrace(__METHOD__.' From shop',$this->shop_id);
        }
        //if not found, look into account level
        if (!isset($this->payment_token)){
            $billing = Billing::model()->mine()->find();
            if ($billing!=null){
                $this->payment_token = $billing->token;
                logTrace(__METHOD__.' From billing record id',$billing->id);
            }
        }
    } 
    /**
     * Create braintree data, set currency if not using default
     * @return \SubscriptionForm
     */
    public function createBraintreeData($currency=null)
    {
        $this->setBraintreeCurrency($currency);
        $this->braintreeData = $this->getBraintreeConfig();
        return $this;
    } 
    
    public function getDefaultPackage()
    {
        foreach (self::$packages->data as $pkg) {
            return $pkg->id;
        }
    } 

    public function getFreeTrialName()
    {
        $freeTrialModel = Plan::model()->findByPk(Plan::FREE_TRIAL);
        return Sii::t('sii','{n} Days ',['{n}'=>$freeTrialModel->duration]).Sii::t('sii',$freeTrialModel->name);
    }
    
    public function getShopName($locale)
    {
        $model = $this->getShopModel();
        return $model!=null ? $model->parseName($locale) : null;
    }

    public function getShopModel()
    {
        if (isset($this->shop_id)){
            return Shop::model()->mine()->findByPk($this->shop_id);
        }
        else { 
            return null;
        }
    }
    
    public function getCurrentPlanName()
    {
        if (isset($this->shop_id)){
            return Package::siiName(user()->getCurrentSubscription($this->shop_id)->package_id);
        }
        else { 
            return null;
        }
    }
}
