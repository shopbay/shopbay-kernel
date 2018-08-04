<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SLinkPager
 *
 * @author kwlok
 */
class SLinkPager extends CLinkPager 
{
    public $https = false;
    /**
     * @inheritcdoc
     */
    protected function createPageUrl($page)
    {
       $url = $this->getPages()->createPageUrl($this->getController(),$page);
       if ($this->https)
           return str_replace('http://', 'https://', $url);
       else
           return $url;
    }    
}
