<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.payments.components.PaymentBaseGateway');
/**
 * Description of OfflinePaymentGateway
 *
 * @author kwlok
 */
class OfflinePaymentGateway extends PaymentBaseGateway 
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
        return self::UNPAID;
    }             
       
}
