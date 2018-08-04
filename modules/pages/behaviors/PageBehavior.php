<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageBehavior
 *
 * @author kwlok
 */
class PageBehavior extends CActiveRecordBehavior 
{
    public function getBaseUrl($secure=false)
    {
        $baseRoute = $this->getOwner()->getParam('baseRoute');
        if ($baseRoute!=null){
            if ($baseRoute=='/')
                $baseRoute = '';//set to null; owner url is already ending with /
            return $this->getOwner()->getOwnerUrl($secure).$baseRoute;
        }
        else
            return $this->getOwner()->getOwnerUrl($secure).'/page';
    }
    /**
     * This is public accessible url
     * @return type
     */
    public function getUrl($secure=false)
    {
        $route = $this->getOwner()->slug=='home' ? '' : '/'.$this->getOwner()->slug;
        return $this->getBaseUrl($secure).$route;
    }
    /*
     * @return if in skipslug scenario
     */
    public function getIsSkipSlugScenario()
    {
        return $this->getOwner()->getScenario()==Page::model()->getSkipSlugScenario();
    }
}
