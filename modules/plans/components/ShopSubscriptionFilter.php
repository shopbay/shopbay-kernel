<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.plans.components.SubscriptionFilter');
/**
 * Description of ShopSubscriptionFilter
 *
 * @author kwlok
 */
class ShopSubscriptionFilter extends SubscriptionFilter
{
    protected $filterClass = 'ShopSubscriptionFilter';
    protected $shop;//the shop to be examined for subscription
    /**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        logTrace(__METHOD__.' Start checking...');

        if (user()->hasNoShopBefore){
            user()->setFlash('checkout',[
                'message'=>Sii::t('sii','Choose a plan for your first shop.'),
                'type'=>'success',
                'title'=>Sii::t('sii','Create My First Shop'),
            ]);
            logInfo(__METHOD__.' User has no shop before.');
            $filterChain->controller->redirect(url('plans/subscription/checkout'));
        } 
        
        $this->verifySessionShop($filterChain);
        
        $this->redirectUrl = $this->getDefaultRedirectUrl();
        
        //[1] Special case: shop creation no need subscription checks below
        if ($this->shop=='create'){
            $this->access = false;
            $this->validateSubscriptionServices($filterChain);
            logTrace(__METHOD__." Shop creation, skip shop level subscription checks.");
            return $this->verifyAccess($filterChain);
        }
        
        //[2] Special case: If there exists valid SKIP PASTDUE token no need subscription check
        if (!Helper::strpos_arr($_SERVER['REQUEST_URI'],['shop/view/'.$this->shop.'/?skipPastdue']) &&
            SActiveSession::get(SActiveSession::SHOP_SKIPPASTDUE)!=null && SActiveSession::get(SActiveSession::SHOP_SKIPPASTDUE) > time()){
            if (user()->hasPastdueSubscription($this->shop)){// and only when subscription is on pastdue
                logInfo(__METHOD__." Shop $this->shop is overdue for payment but granted SKIP token");
                return true;
            }
        }
        
        //[3] Other different subscription scenario checks        
        if (Role::requiresSubscriptionCheck(user()->currentRole)){
            /**
             * @todo Need performance tuning. Below are do a lot of subscription checks (repeatable?)
             * @todo Suggest to load all subscription licenses at one go when login to make page loading faster 
             */
            if (user()->hasPendingSubscription($this->shop)){
                //[3.1] If user has subscribed before, but is pending confirmation
                $this->access = false;
                $this->redirectUrl = url('plans/subscription/pending/shop/'.$this->shop);
            }
            elseif (!user()->hasOnlineSubscription($this->shop) && user()->hasCancelledSubscription($this->shop)){
                //[3.2] If user shop subscription has been cancelled. Extra protection to prevent if webhook is not happening and shop is not yet deleted.
                $this->access = false;
                $this->redirectUrl = url('shops');
                user()->setFlash('Shop',[
                    'message'=>null,
                    'type'=>'error',
                    'title'=>Sii::t('sii','Shop "{shop}" is already cancelled.',['{shop}'=>$this->shop]),
                ]);
            }
            elseif (user()->hasPastdueSubscription($this->shop)){
                if (Helper::strpos_arr($_SERVER['REQUEST_URI'],['shop/view/'.$this->shop.'/?skipPastdue'])){
                    //[3.3] User request to skip payment, grant access for 24 hours
                    SActiveSession::set(SActiveSession::SHOP_SKIPPASTDUE, time() + 24*60*60 );//grace period 24 hours
                    //skip overdue payment page
                    $filterChain->controller->redirect(user()->getPastdueSubscription($this->shop)->shop->viewUrl);
                    Yii::app()->end();
                }
                else {
                    //[3.4] If user has pastdue subscription, will request to pay
                    $this->access = false;
                    $this->redirectUrl = url('plans/subscription/pastdue/shop/'.$this->shop);
                }
            }
            elseif (user()->hasSuspendedSubscription($this->shop)){
                //[3.5] If user has subscribed before, but is now suspended
                $this->access = false;
                $this->redirectUrl = url('plans/subscription/suspend/shop/'.$this->shop);
            }
            elseif (user()->hasExpiredSubscription($this->shop)){
                //[3.6] When subscription has expired (normally applies to Free Trial, but here also do a second check on shop subscription)
                $subscription = user()->getExpiredSubscription($this->shop);
                $message = Sii::t('sii','To continue using our service, you can choose any of our plan offerings.');
                user()->setFlash('Plan',[
                    'message'=>$message,
                    'type'=>'notice',
                    'title'=>Sii::t('sii','Your {plan} subscription "{id}" for shop "{shop}" has expired.',['{plan}'=>$subscription->planDesc,'{id}'=>$subscription->subscription_no,'{shop}'=>$subscription->shop->parseName(user()->getLocale())]),
                ]);
                $this->access = false;     
                $this->redirectUrl = url('plans/subscription/index/shop/'.$this->shop);
            }

            $this->validateSubscriptionServices($filterChain);
        }

        return $this->verifyAccess($filterChain);
    }
    /**
     * Verify session shop, if not found, redirect back to shops index page
     * @param type $filterChain
     */
    protected function verifySessionShop($filterChain) 
    {
        $this->shop = $this->findSessionShop();
        if ($this->shop==null){
            logInfo(__METHOD__.' Session shop not found; Redirect back to "/shops", request uri:',$_SERVER['REQUEST_URI']);            
            user()->setFlash('Shop',[
                'message'=>Sii::t('sii','Shop session cannot be found'),
                'type'=>'notice',
                'title'=>Sii::t('sii','Please select shop again'),
            ]);
            $filterChain->controller->redirect(url('shops'));
            Yii::app()->end();
        }
    }
    /**
     * Find session shop, If session shop not exists, try to find it from url
     * Not so smart way to find session shop from view/update url (fixed pattern); Drawback: if url change, session shop cannot be detected.
     * @todo Better way is to have shop url itself containing shop id or name, and with this guaranteed shop can be found; But need to review all the shop urls used within shops, including shop objects, like products , shippings etc. 
     * @todo Simpler method will be putting shop id on the url rather than using session
     */
    protected function findSessionShop()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (Helper::strpos_arr($uri,['shops/management/create','shop/management/create'])){
            //for shop creation, the session shop is not required.
            $shop = 'create';//dummy value, so that can pass self::verifySessionShop() check
        }
        elseif (Helper::strpos_arr($uri,['shop/view'])){
            $parts = explode('/',$uri);
            /**
             * Example $parts
             * array (
             *   0 => '',
             *   1 => 'shop',
             *   2 => 'view',
             *   3 => '70',
             * )
             */
            if (isset($parts[3])){
                $shop = $parts[3];
                logTrace(__METHOD__.' Catch shop by view url :',$shop);
                SActiveSession::set(SActiveSession::SHOP_ACTIVE,$shop);//override session shop
            }
        }
        elseif (Helper::strpos_arr($uri,['shop/management/update'])){
            $parts = explode('?',$uri);
            /**
             * Example $parts
             * array (
             *   0 => '/shop/management/update',
             *   1 => 'id=70',
             * )
             */
            if (isset($parts[1])){
                $shop = substr($parts[1],3);//remove first 3 chars "id="
                logTrace(__METHOD__.' Catch shop by update url :',$shop);
                SActiveSession::set(SActiveSession::SHOP_ACTIVE,$shop);//override session shop
            }
        }
        else {
            $shop = SActiveSession::get(SActiveSession::SHOP_ACTIVE);
        }
        return $shop;
    }
    
}
