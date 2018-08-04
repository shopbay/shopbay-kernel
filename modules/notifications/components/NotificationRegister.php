<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of NotificationRegister
 *
 * @author kwlok
 */
class NotificationRegister extends CComponent 
{
    /**
     * All merchant scoped notifications 
     */
    public static function getMerchantNotifications()
    {
        return [
            Notification::$orderUnpaid => [
                'title'=>Sii::t('sii','Order Unpaid'),
                'subtitle'=>Sii::t('sii','When customer places an unpaid order using offline payment method.'),
                'channels'=>[Notification::$typeEmail],
            ],
            Notification::$orderVerify => [
                'title'=>Sii::t('sii','Order Pending Payment Verification'),
                'subtitle'=>Sii::t('sii','When customer makes an offline payment but pending your verification.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemReceive => [
                'title'=>Sii::t('sii','Item Received'),
                'subtitle'=>Sii::t('sii','When customer confirms receiving the purchased item.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemReview => [
                'title'=>Sii::t('sii','Item Reviewed'),
                'subtitle'=>Sii::t('sii','When customer makes a review on the purchased item.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemReturn => [
                'title'=>Sii::t('sii','Item Return Request'),
                'subtitle'=>Sii::t('sii','When customer requests for item return.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$questionAsk => [
                'title'=>Sii::t('sii','Question Asked'),
                'subtitle'=>Sii::t('sii','When customer asks a question.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
        ];
    }
    /**
     * All customer scoped notifications 
     * Note: Messenger notifications must subscrbe in Messenger itself?
     */
    public static function getCustomerNotifications()
    {
        return [
//            Notification::$productUpdates => '',//but this is tied to a specific shop
            Notification::$questionAnswer => [
                'title'=>Sii::t('sii','Question Answered'),
                'subtitle'=>Sii::t('sii','When merchant replies your question.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$orderPay => [
                'title'=>Sii::t('sii','Order Pending Payment'),
                'subtitle'=>Sii::t('sii','Reminder to make payment for your order using offline payment method.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$orderPending => [
                'title'=>Sii::t('sii','Order Pending Payment Verification'),
                'subtitle'=>Sii::t('sii','When you make an offline payment but pending merchant verification.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$orderConfirm => [
                'title'=>Sii::t('sii','Order Confirmation'),
                'subtitle'=>Sii::t('sii','When merchant accepts and confirms your purchase order.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$orderCancel => [
                'title'=>Sii::t('sii','Order Cancellation'),
                'subtitle'=>Sii::t('sii','When merchant cancels your purchase order.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$orderReject => [
                'title'=>Sii::t('sii','Unsuccessful Order Payment Verification'),
                'subtitle'=>Sii::t('sii','When merchant rejects your purchase order due to failed payment verification.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$shippingorderProcess => [
                'title'=>Sii::t('sii','Shipping Order In Process'),
                'subtitle'=>Sii::t('sii','When merchant starts processing your a purchase order for shipping.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$shippingorderRefund => [
                'title'=>Sii::t('sii','Shipping Order Refund Notice'),
                'subtitle'=>Sii::t('sii','When merchant refunds your shipping order.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemShip => [
                'title'=>Sii::t('sii','Item Shipped'),
                'subtitle'=>Sii::t('sii','When merchant ships your purchased item.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemCancel => [
                'title'=>Sii::t('sii','Item Canceled'),
                'subtitle'=>Sii::t('sii','When merchant cancels your purchased item.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemRefund => [
                'title'=>Sii::t('sii','Item Refunded'),
                'subtitle'=>Sii::t('sii','When merchant refunds your purchased item.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemReturnApprove => [
                'title'=>Sii::t('sii','Item Return Approval'),
                'subtitle'=>Sii::t('sii','When merchant approves your item return request.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
            Notification::$itemReturnReject => [
                'title'=>Sii::t('sii','Item Return Rejection'),
                'subtitle'=>Sii::t('sii','When merchant rejects your item return request.'),
                'channels'=>[Notification::$typeMessage,Notification::$typeEmail],
            ],
        ];
    }
    /**
     * All admin scoped notifications admin 
     */
    public static function getAdminNotifications()
    {
        //@todo
    }
    /**
     * All system scoped notifications (not subscribebale)
     * e.g. those account activation, welcome message etc. Mandatory and not subscribeable
     */
    public static function getSystemNotifications()
    {
        //@todo
    }
    
}
