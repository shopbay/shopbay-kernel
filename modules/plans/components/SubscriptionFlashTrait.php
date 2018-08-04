<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SubscriptionFlashTrait
 *
 * @author kwlok
 */
trait SubscriptionFlashTrait 
{
    /**
     * Set the flash message when subscription is to be changed
     * @param type $subscription
     * @param type $flashId
     */
    public function setChangePlanFlash($subscription,$flashId)
    {
        if ($subscription instanceof Subscription){
            $message = Sii::t('sii','Please note that a new subscription will be issued for the new plan chosen, and existing subscription "{id}" will be cancelled.',['{id}'=>CHtml::link($subscription->subscription_no,$subscription->viewUrl)]);
            user()->setFlash($flashId,[
                'message'=>$message,
                'type'=>'notice',
                'title'=>Sii::t('sii','Change Plan for {shop}',['{shop}'=>$subscription->shop->parseName(user()->getLocale())]),
            ]);
        }
    }
    /**
     * Set the flash message when subscription is pastdue
     * @param type $controller
     * @param type $subscription
     * @param type $showPaymentButton
     */
    public function setPastdueFlash($controller,$subscription,$showPaymentButton=true)
    {
        $message = Sii::t('sii','Please note that you have to make payment by the dunning date {date}, else your shop will be suspended and you will not be able to continue to use our services.',['{date}'=>$subscription->dunningDate]);
        if ($showPaymentButton)
            $message .= '<div style="margin:10px 0px 5px;">'.CHtml::link(Sii::t('sii','Make Payment Now'),$subscription->pastduePaymentUrl,['style'=>'color:black']).'</div>';
        
        $title = Sii::t('sii','Subscription "{id}" for shop "{shop}" is overdue. Please make payment.',['{id}'=>$subscription->subscription_no,'{shop}'=>$subscription->shop->parseName(user()->getLocale())]);
        $controller->addGlobalFlash('error',$title,$message);
    } 
    /**
     * Set the flash message when subscription is in pending
     * @param type $subscription
     * @param type $flashId
     */
    public function setPendingFlash($subscription,$flashId)
    {
        $message = Sii::t('sii','If you continue seeing this page when your subscription has started, please contact us at {email}.',array('{email}'=>Config::getSystemSetting('email_contact')));
        user()->setFlash($flashId,[
            'message'=>$message,
            'type'=>'notice',
            'title'=>Sii::t('sii','We are processing your subscription "{id}"',['{id}'=>$subscription->subscription_no]),
        ]);
    }
    /**
     * Set the flash message when subscription is suspended
     * @param type $subscription
     * @param type $flashId
     */
    public function setSuspendFlash($subscription,$flashId)
    {
        user()->setFlash($flashId,[
            'message'=>Sii::t('sii','Your subscription "{id}" and shop "{shop}" are in suspension',['{id}'=>$subscription->subscription_no,'{shop}'=>$subscription->shop->parseName(user()->getLocale())]),
            'type'=>'notice',
            'title'=>Sii::t('sii','Important Note'),
        ]);
    }
    /**
     * Set the flash message when subscription is to be canceled
     * @param type $subscription
     * @param type $flashId
     */
    public function setCancelFlash($subscription,$flashId)
    {
        $message = Sii::t('sii','Please note that once you cancel your subscription, you will no longer be able to access your shop and run online business.');
        user()->setFlash($flashId,[
            'message'=>$message,
            'type'=>'notice',
            'title'=>Sii::t('sii','Caution'),
        ]);
    }
    
}
