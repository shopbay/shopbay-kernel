<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SHttpSession
 *
 * @author kwlok
 */
class SHttpSession extends CHttpSession 
{
    /**
     * @var boolean True to support cross-subdomain authentication
     */
    public $enableCookieSharing = false;
    /**
     * Init
     */
    public function init()
    {
        if ($this->enableCookieSharing){
            $this->setCookieParams(cookieSharingSettings(true));
        }
        parent::init();
    }
}
