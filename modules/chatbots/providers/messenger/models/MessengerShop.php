<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotShop');
Yii::import('common.modules.chatbots.payloads.ShopPayload');
Yii::import('common.modules.chatbots.payloads.HelpPayload');
Yii::import('common.modules.chatbots.providers.messenger.threads.PersistentMenu');
Yii::import('common.modules.chatbots.providers.messenger.threads.PostbackMenuItem');
Yii::import('common.modules.shops.components.ShopNavigation');
Yii::import('common.modules.shops.components.ShopPage');
Yii::import('common.modules.pages.models.Page');
/**
 * Description of MessengerShop
 *
 * @author kwlok
 */
class MessengerShop extends ChatbotShop
{
    /**
     * Construct persistent menu items (as payload)
     * @see ShopNavigation for menu format
     * [ 
     *   'id'=>'',
     *   'type'=>'',
     *   'heading'=>[<locale values>],
     *   'url'=>''
     *   'items'=>[]//submenus
     * ]
     * @return \PostbackMenuItem
     */
    public function getPesistentMenuItems($locale=null)
    {
        $count = 0;
        $menus = [];
        foreach ($this->getNavMenu() as $menu) {
            
            if (!empty($menu) && is_array($menu)){
                //only supports level 1, skips any $menu['items'] submenu
                //only supports menu id defiend in the HelpPayload
                switch ($menu['type']) {
                    case ShopNavigation::$typeCustomPage:
                        $pageId = ShopNavigation::decodeId($menu['id'],ShopNavigation::$typeCustomPage);
                        $page = Page::model()->findByPk($pageId);
                        if ($page!=null){
                            $pageTitle = $page->displayLanguageValue('title',$locale);
                            //add payload
                            $shopPayload = new ShopPayload(ShopPayload::CUSTOM_PAGE,[
                                'page'=>$page->getParam('layout'),
                                'page_name'=>$pageTitle,
                                'page_url'=>$page->getUrl(true),
                            ]);
                            $menus[] = new PostbackMenuItem($pageTitle, $shopPayload->toString());
                            $count++;
                        }
                        break;
                    case ShopNavigation::$typeCategory://not support for now
                    case ShopNavigation::$typeLink://not support for now
                    default:
                        break;
                }
                
                if ($count==PersistentMenu::$menusLimit)
                    break;//only includes up to $menusLimit
            }
        }
        
        return $menus;
    }
}
