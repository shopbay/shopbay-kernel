<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopNavigation");
/**
 * Description of ShopConfigBehavior
 *
 * @author kwlok
 */
class ShopConfigBehavior extends CActiveRecordBehavior 
{
    /**
     * Check if social meida share is allowed; Default is 'false'
     */
    public function isSocialMediaShareAllowed()
    {
        return $this->getBooleanValue(ShopSetting::$marketing, 'socialMediaShare', false);
    }    
    /**
     * Get custom shop favicon url
     */
    public function getFaviconUrl()
    {
        return $this->getOwner()->settings!=null ? $this->getOwner()->settings->getImageOriginalUrl() : null;//default
    }
    /**
     * Get custom shop favicon
     */
    public function getFavicon()
    {
        return $this->getOwner()->settings!=null ? $this->getOwner()->settings->getValue(ShopSetting::$brand,'favicon') : null;//default
    }
    /**
     * Get shop custom domain
     */
    public function getCustomDomain()
    {
        return $this->getOwner()->settings!=null ? $this->getOwner()->settings->getValue(ShopSetting::$brand,'customDomain') : null;//default
    }    
    /**
     * Get shop own domain
     */
    public function getMyDomain()
    {
        return $this->getOwner()->settings!=null ? $this->getOwner()->settings->getValue(ShopSetting::$brand,'myDomain') : null;//default
    }    
    /**
     * Get email sender name
     */
    public function getEmailSenderName()
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$notifications,'emailSenderName');
        }
        else {
            return Config::getSystemSetting('email_contact');//default
        }
    }    
    /**
     * Get low inventory threshold
     */
    public function getLowInventoryThreshold()
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$notifications,'lowInventoryThreshold',true);
        }
        else {
            return ShoppSetting::$defaultNotificationsLowInventory;//default
        }
    }    
    /**
     * Check if low inventory notification is enabled; Default is 'false'
     */
    public function isLowInventoryEnabled()
    {
        return $this->getBooleanValue(ShopSetting::$notifications, 'lowInventory');
    }
    /**
     * Get navigation menu
     */
    public function getMainMenu()
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$navigation,'mainMenu',true);
        }
        else {
            return $this->getOwner()->getDefaultMainMenu();//default
        }
    }   
    /**
     * Get navigation default menu
     */
    public function getDefaultMainMenu($array=false)
    {
        $nav = new ShopNavigation('DUMMY',$this->getOwner(),null);//set controller to null (no use)
        return $nav->getDefaultMainMenu($array);
    }     
    /**
     * Get shopping cart items limit in cart
     */
    public function getCartItemsLimit()
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$checkout,'cartItemsLimit',true);
        }
        else {
            return ShopSetting::$defaultCheckoutCartItemsLimit;//default
        }
    }    
    /**
     * Get checkout quantity limit in cart
     */
    public function getCheckoutQuantityLimit()
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$checkout,'checkoutQtyLimit',true);
        }
        else {
            return ShopSetting::$defaultCheckoutQtyLimit;//default
        }
    }    
    /**
     * Check if view product in modal view; Default is 'false'
     */
    public function displayProductOverlay()
    {
        return $this->getBooleanValue(ShopSetting::$checkout, 'productOverlayView');
    }    
    /**
     * Check if view more products is allowed; Default is 'true'
     */
    public function isRegisterToViewMore()
    {
        return $this->getBooleanValue(ShopSetting::$checkout, 'registerToViewMore');
    }    
    /**
     * Check if guest checkout is allowed; Default is 'true'
     */
    public function isGuestCheckoutAllowed()
    {
        return $this->getBooleanValue(ShopSetting::$checkout, 'guestCheckout');
    }    
    /**
     * Check if item return is allowed; Default is 'true'
     */
    public function getIsReturnAllowed()
    {
        return $this->getBooleanValue(ShopSetting::$checkout, 'allowReturn');
    }
    /**
     * @return string Get the custom css
     */
    public function getCustomCss($theme=null,$style=null)
    {
        if (!isset($theme) && !isset($style)){
            //return the current active theme custom css
            return $this->getOwner()->themeModel!=null ? $this->getOwner()->themeModel->getParam('css') : null;
        }
        else {
            $themeModel = ShopTheme::model()->locateShop($this->getOwner()->id)->locateTheme($theme,$style)->find();
            return $themeModel!=null ? $themeModel->getParam('css') : null;
        }
    }    
    /**
     * Check if to generate sitemap; Default is 'false'
     */
    public function hasSitemap()
    {
        return $this->getBooleanValue(ShopSetting::$seo, 'generateSitemap', false);
    }   
    /**
     * Return shop theme
     * @return type
     */
    public function getTheme()
    {
        return $this->getOwner()->themeModel!=null ? $this->getOwner()->themeModel->theme : $this->getOwner()->getDefaultTheme();
    }
    /**
     * Return shop theme style
     * @return string
     */
    public function getThemeStyle()
    {
        return $this->getOwner()->themeModel!=null ? $this->getOwner()->themeModel->style : $this->getOwner()->getDefaultThemeStyle();
    } 
    /**
     * Get the shop order number setting
     */
    public function getOrdersSetting($field)
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue(ShopSetting::$orders,$field,true);
        }
        else
            return ShopSetting::getOrdersDefaultSetting($field);
    }     
    /**
     * Check if to skip orders item processing for this shop
     * @see ShopSetting::$itemProcessSkip
     */
    public function skipOrdersItemProcessing()
    {
        return $this->getOwner()->getOrdersSetting('processEachItems')==ShopSetting::$itemProcessSkip;
    }      
    /**
     * Check if it is one step orders item processing for this shop
     * @see ShopSetting::$itemProcess1Step
     */
    public function oneStepOrdersItemProcessing()
    {
        return $this->getOwner()->getOrdersSetting('processEachItems')==ShopSetting::$itemProcess1Step;
    }      
    /**
     * Check if it is one step orders item processing for this shop
     * @see ShopSetting::$itemProcess3Step
     */
    public function threeStepsOrdersItemProcessing()
    {
        return $this->getOwner()->getOrdersSetting('processEachItems')==ShopSetting::$itemProcess3Step;
    }     
    
    protected function getBooleanValue($attribute,$field,$default=true)
    {
        if ($this->getOwner()->settings!=null){
            return $this->getOwner()->settings->getValue($attribute,$field,$default)==1;
        }
        else
            return $default;
    }
}
