<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentBaseGateway');
/**
 * Description of PaypalExpressCheckoutGateway
 *
 * @author kwlok
 */
class PaypalExpressCheckoutGateway extends PaymentBaseGateway 
{
    public $paypalApiConfig = array(
                        'class'=>'common.modules.payments.plugins.paypalExpressCheckout.components.PaypalExpressCheckout',
                        'returnUrl' => 'cart/paypalexpressreview', //regardless of url management component. this is mapped at main.php
                        'cancelUrl' => 'cart', //regardless of url management component
                    );
    public $paypalApi;//paypal api processor
    
    public function init() 
    {
        parent::init(); 
        $this->paypalApi = Yii::createComponent($this->paypalApiConfig);
        $this->paypalApi->init();
    }
    /**
     * Process payment
     * @param PaymentForm model
     */
    public function process($payment)
    {
        try {
            $paypalResult = $this->paypalApi->confirmPayment($payment->getShop(),$payment->extraPaymentData);
            logInfo(__METHOD__.' Paypal payment successful');
            $payment->trace_no = json_encode($this->paypalApi->getSystemTrace($paypalResult));
            if (Yii::app()->serviceManager->getPaymentManager()->pay($payment)!=Process::OK)
                //could not throw exception as paypal payment is already successfuly. 
                //log as Warning message for troubleshooting.
                logWarning(__METHOD__.' Could not create payment record',$payment->getAttributes());
            
            return self::PAID;

        } catch (Exception $e)  {
            logError(__METHOD__.' Paypal payment error: '.$e->getMessage());
            throw new CException($e->getMessage());
        }
        
    }
    
}

