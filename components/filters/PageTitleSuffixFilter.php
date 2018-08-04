<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageTitleSuffixFilter
 *
 * @author kwlok
 */
class PageTitleSuffixFilter extends CFilter
{
    /**
     * Hide page title suffix under shop scoped
     * @var type 
     */
    public $hideOnShopScope = false;//to implement at child class
    /**
     * Use shop name as page title suffix under shop scoped
     * @var type 
     */
    public $useShopName = false;//to implement at child class
    /**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        $filterChain->controller->pageTitleSuffix = Yii::app()->name;
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
