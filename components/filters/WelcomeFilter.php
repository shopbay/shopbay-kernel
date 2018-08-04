<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WelcomeFilter
 *
 * @author kwlok
 */
class WelcomeFilter extends CFilter
{
    /**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        //allows captcha to be displayed
        if (strpos(request()->getRequestUri(),'/captcha')!=false)
            return true;
        //when user requires password change, and the url is not 'welcome/passwordReset' itself <<- avoid TOO_MANY_REDIRECTS error
        if (!user()->isGuest && user()->account->passwordChangeRequired() && strpos(request()->getRequestUri(),'/passwordReset')==0){
            $filterChain->controller->redirect(url('accounts/welcome/passwordReset'));
            Yii::app()->end();
        }
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
