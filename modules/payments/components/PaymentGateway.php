<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentBaseGateway');
/**
 * Description of PaymentGateway
 *
 * @author kwlok
 */
class PaymentGateway extends PaymentBaseGateway 
{
    public function init() 
    {
        parent::init(); 
    }
    /**
     * Process payment
     * @param PaymentForm model
     */
    public function process($payment)
    {
        $this->validate($payment);
        $gateway = Yii::app()->getModule('payments')->getPlugin($payment->method,'gateway');
        return $gateway->process($payment);
    }      
    /**
     * Validate payment
     * @param PaymentForm model
     */
    public function validate($payment)
    {
        if (!($payment instanceof PaymentForm))
            throw new CException(Sii::t('sii','Invalid PaymentForm object'));

        if ($payment->status == Process::PAID || $payment->status == Process::COMPLETED)
            throw new CException(Sii::t('sii','Payment already made'));

        if ($payment->status != Process::UNPAID)
            throw new CException(Sii::t('sii','Payable must be in UNPAID status'));

        if ($payment->amount <= 0)
            throw new CException(Sii::t('sii','Payment amount must be greater than zero'));
    }
    
}