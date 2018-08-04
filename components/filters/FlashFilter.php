<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.plans.components.SubscriptionFlashTrait');
/**
 * Description of FlashFilter
 *
 * @author kwlok
 */
class FlashFilter extends CFilter
{
    use SubscriptionFlashTrait;    
    /**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        if (Role::requiresSubscriptionCheck(user()->currentRole)){
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri,'/plans/subscription/pastdue')==false){//skip showing same message inside pastdue page
                foreach (user()->getPastdueSubscriptions() as $subscription) {
                    $this->setPastdueFlash($filterChain->controller, $subscription);
                }
            }
        }
        //we can add more flash here
        //$filterChain->controller->addGlobalFlash('success','title','message');
        return true;
    }
    /**
     * Logic being applied after the action is executed
     * @param type $filterChain
     */
    protected function postFilter($filterChain)
    {
        //put logic here
    }    
}
