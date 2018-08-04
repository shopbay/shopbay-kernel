<?php
/**
 * This gives the payment method plugins supported 
 * 
 * TODO parameter 'enable' should be at shop level instead of global (across all shops)
 */
return [
    PaymentMethod::OFFLINE_PAYMENT => [
        'name'=>'offlinePayment',
        'displayName'=>'Offline Payment Method',
        'enable'=>true,
        'form'=>[
            'name'=>'OfflinePaymentForm',
            'class'=>'common.modules.payments.plugins.offlinePayment.models.OfflinePaymentForm',
        ],
        'gateway'=>[
            'name'=>'OfflinePaymentGateway',
            'class'=>'common.modules.payments.plugins.offlinePayment.components.OfflinePaymentGateway',
        ],
        'methods'=>[
            PaymentMethod::ATM_CASH_BANK_IN => 'ATM/Cash Bank-in',
            PaymentMethod::CASH_ON_DELIVERY => 'Cash On Delivery',
            PaymentMethod::OTHERS => 'Others',
            //other methods... e.g.
        ],
    ],            
    PaymentMethod::PAYPAL_EXPRESS_CHECKOUT => [
        'name'=>'paypalExpressCheckout',
        'displayName'=>'PayPal Express Checkout',
        'enable'=>true,
        'form'=>[
            'name'=>'PaypalExpressCheckoutForm',
            'class'=>'common.modules.payments.plugins.paypalExpressCheckout.models.PaypalExpressCheckoutForm',
        ],
        'gateway'=>[
            'name'=>'PaypalExpressCheckoutGateway',
            'class'=>'common.modules.payments.plugins.paypalExpressCheckout.components.PaypalExpressCheckoutGateway',
        ],
    ],
    PaymentMethod::BRAINTREE_CREDITCARD => [
        'name'=>'braintreeCreditCard',
        'displayName'=>'Braintree Credit Card',
        'enable'=>true,
        'form'=>[
            'name'=>'BraintreeCreditCardForm',
            'class'=>'common.modules.payments.plugins.braintreeCreditCard.models.BraintreeCreditCardForm',
        ],
        'gateway'=>[
            'name'=>'BraintreeCreditCardGateway',
            'class'=>'common.modules.payments.plugins.braintreeCreditCard.components.BraintreeCreditCardGateway',
        ],
    ],
    PaymentMethod::BRAINTREE_PAYPAL => [
        'name'=>'braintreePayPal',
        'displayName'=>'Braintree PayPal',
        'enable'=>false,
        'form'=>[
            'name'=>'BraintreePayPalForm',
            'class'=>'common.modules.payments.plugins.braintreePayPal.models.BraintreePayPalForm',
        ],
        'gateway'=>[
            'name'=>'BraintreePayPalGateway',
            'class'=>'common.modules.payments.plugins.braintreePayPal.components.BraintreePayPalGateway',
        ],
    ],
];
