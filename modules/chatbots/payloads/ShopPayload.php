<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
Yii::import("common.modules.shops.components.ShopPage");
/**
 * Description of ShopPayload
 *
 * @author kwlok
 */
class ShopPayload extends ChatbotPayload 
{
    //Payload is named in convention to PayloadView name 
    CONST MENU             = 'menu';//a custom shop sitemap suitable for facebook messenger
    CONST SHORTCUT         = 'shortcut';//shortcut keywords to reach each menu
    CONST PRODUCTS         = 'products_page';//shortcut to list all products
    CONST CATEGORIES       = 'categories';//show all categories in the shop
    CONST CATEGORIES_SUB   = 'categories_sub';//show all sub categories in the shop
    CONST BRANDS           = 'brands';//show all brands in the shop
    CONST NEWS_ARTICLE     = 'news_article';//show a news article content, similar function as ShopPage::ARTICLE_PAGE; Here have it to follow naming convention
    CONST SUBSCRIPTION     = 'subscription';//shop subscription option page
    CONST SUBSCRIBE        = 'subscribe';//shop notification subscribe page
    CONST UNSUBSCRIBE      = 'unsubscribe';//shop notification unsubscribe page
    CONST PRODUCT_UPDATES  = 'product_updates';//shop product updates for a given period (e.g. last 24 hours)
    CONST CUSTOM_PAGE      = 'custom_page';//shop custom page
    //other payloads please refer to ShopPage CONST
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return ChatbotPayload::SHOP;
    }    
    /**
     * @return the payload title
     */
    public function getTitle()
    {
        $page = substr($this->type, strlen($this->typePrefix));
        return self::getShopPageTitle($page);
    }
    /**
     * Shop page title suitable for display in messenger
     * @param type $page
     * @return type
     */
    public static function getShopPageTitle($page)
    {
        return ucfirst(strtolower(ShopPage::getTitle($page)));        
    }
}
