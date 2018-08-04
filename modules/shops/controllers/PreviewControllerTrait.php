<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PreviewControllerTrait
 *
 * @author kwlok
 */
trait PreviewControllerTrait
{
    /**
     * Check if controller is running in preview
     * @var boolean
     */
    public $inPreviewMode = false;
    /**
     * Incoming query params, including potential 'preview' params 
     * @see ShopViewTrait for source
     * @var array
     */
    public $queryParams = [];
    /**
     * Check all query params and determine if preview is on
     */
    protected function parseQueryParams()
    {
        $uri = Helper::parseUri();
        $this->queryParams = isset($uri['query']) ? $uri['query'] : [];
        if (isset($this->queryParams['preview']) && isset($this->queryParams['theme']) && isset($this->queryParams['style']))
            $this->inPreviewMode = true;
    }        
    /**
     * Check if in preview mode; Return true or false
     * @return boolean
     */
    protected function checkPreview()
    {
        $this->parseQueryParams();
        return $this->inPreviewMode;
    }

    public function appendQueryParams($url)
    {
        return $url.'?'.http_build_query($this->queryParams);
    }
    
}
