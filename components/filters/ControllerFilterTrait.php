<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.filters.*");
Yii::import("common.modules.plans.components.SubscriptionFilter");
/**
 * Class ControllerFilterTrait
 * This adds all the filters to the controller
 */
trait ControllerFilterTrait
{
    /**
     * The filter method for 'subscription' access filter.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filterSubscription($filterChain)
    {
        $filter = new SubscriptionFilter();
        $filter->filter($filterChain);
    }
    /**
     * The filter method for 'flash' access filter.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filterFlash($filterChain)
    {
        $filter = new FlashFilter();
        $filter->filter($filterChain);
    }
    /**
     * The filter method for 'welcome' access filter.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filterWelcome($filterChain)
    {
        $filter = new WelcomeFilter();
        $filter->filter($filterChain);
    }    
}