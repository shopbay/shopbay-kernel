<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageViewInterface
 *
 * @author kwlok
 */
interface PageViewInterface 
{
    /**
     * Get page owner
     * @return string
     */
    public function getPageOwner();
    /**
     * Get current page theme
     * @return CActiveRecord
     */
    public function getPageTheme();
    /**
     * Home page url 
     */
    public function getHomePage();
    /**
     * Indicate if page is embedded at third party page, e.g. facebook page
     * @return boolean
     */
    public function onOffsite();
    /**
     * Extra query uri required for offsite embedding
     * @return array
     */
    public function getOffsiteUriParams();
}
