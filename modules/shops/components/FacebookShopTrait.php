<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.components.ShopPage');
/**
 * Description of FacebookShopTrait
 *
 * @author kwlok
 */
trait FacebookShopTrait 
{
    private $_facebookMode = false;
    private $_facebookParams;//indicate if cart is on facebook; Default to "null"
    /**
     * Set facebook page mode
     * Mainly used to set http to secure connection as per facebook requirement for facebook page tab
     */
    public function setFacebookMode($bool=true)
    {
        $this->_facebookMode = $bool;
    }     
    /**
     * Get facebook page mode
     */
    public function getFacebookMode()
    {
        return $this->_facebookMode;
    }      
    /**
     * Get facebook uri params
     * @return type
     */
    public function getFacebookUriParams()
    {
        return [
            ShopPage::FACEBOOK_PARAM=>time(),
        ];
    }    
    /**
     * Check if on facebook
     * @param boolean $queryParams
     */
    public function setCartOnFacebook($queryParams=[])
    {
        if (isset($queryParams[ShopPage::FACEBOOK_PARAM]))
            $this->_facebookParams = $queryParams[ShopPage::FACEBOOK_PARAM];
    }
    
    public function getCartOnFacebook()
    {
        return $this->_facebookParams!=false;
    }    
    /**
     * Check if owner is on Facebook page view mode
     * Either $this->_facebookMode is TRUE or there is a GET param named ShopPage::FACEBOOK_PARAM
     * @return boolean
     */
    public function onFacebook()
    {
        return $this->getFacebookMode() || isset($_GET[ShopPage::FACEBOOK_PARAM]) || $this->getCartOnFacebook();
    }        
}
