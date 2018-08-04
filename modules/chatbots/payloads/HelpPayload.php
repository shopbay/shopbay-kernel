<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of HelpPayload
 *
 * @author kwlok
 */
class HelpPayload extends ChatbotPayload 
{
    /**
     * Get the full commands configuration
     * @return array
     */
    public static function getConfig() 
    {
        return [
            'help'=>[
                'name'=>Sii::t('sii','Help'),
                'description'=>Sii::t('sii','show the shortcuts.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ChatbotPayload',
                    'type'=>ChatbotPayload::HELP,
                ],
            ],
            'menu'=>[
                'name'=>Sii::t('sii','Main menu'),
                'description'=>Sii::t('sii','show the main menu'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::MENU,
                ],
            ],
            'shortcut'=>[
                'name'=>Sii::t('sii','Shortcut keywords'),
                'description'=>Sii::t('sii','show the shortcut command key'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::SHORTCUT,
                ],
            ],
            ShopPage::trimPageId(ShopPage::HOME)=>[
                'name'=>Sii::t('sii','Home'),
                'description'=>Sii::t('sii','this starts over the bot conversation.'),
                'alias'=>['start over','reset'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::HOME,//will be re-routed to GetStartedView
                ],
            ],
            ShopPage::trimPageId(ShopPage::ABOUT)=>[
                'name'=>Sii::t('sii','About us'),
                'description'=>Sii::t('sii','tells you more about our shop.'),
                'alias'=>['aboutus'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::CUSTOM_PAGE,
                    'params'=>[
                        'page'=>ShopPage::ABOUT,
                    ],
                ],
            ],
            'support'=>[
                'name'=>Sii::t('sii','Talk to support'),
                'description'=>Sii::t('sii','live chat with our support for any help.'),
                'alias'=>['livechat'],
                'payload'=>[
                    'class'=>'LiveChatPayload',
                    'type'=>LiveChatPayload::START,
                ],
            ],
            ShopPage::trimPageId(ShopPage::PRODUCTS)=>[
                'name'=>Sii::t('sii','Products'),
                'description'=>Sii::t('sii','shows our products in full.'),
                'alias'=>['products'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::PRODUCTS,
                ],
            ],
            ShopPage::trimPageId(ShopPage::PROMOTIONS)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::PROMOTIONS),
                'description'=>Sii::t('sii','shows the latest promotions we are offering.'),
                'alias'=>['promotion','offers'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::PROMOTIONS,
                ],
            ],
            'best sellers'=>[
                'name'=>Sii::t('sii','Best sellers'),
                'description'=>Sii::t('sii','shows the best seller products in our shop.'),
                'alias'=>['bestsellers'],
                'payload'=>[
                    'class'=>'TrendPayload',
                    'type'=>TrendPayload::MOST_PURCHASED,
                ],
            ],
            ShopPage::trimPageId(ShopPage::TRENDS)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::TRENDS),
                'description'=>Sii::t('sii','shows the current trends in our shop.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::TRENDS,
                ],
            ],
            ShopPage::trimPageId(ShopPage::NEWS)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::NEWS),
                'description'=>Sii::t('sii','shows the latest happenings or events.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::NEWS,
                ],
            ],
            ShopPage::trimPageId(ShopPage::PAYMENT)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::PAYMENT),
                'description'=>Sii::t('sii','shows the payment method we accept.'),
                'alias'=>['payments'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::PAYMENT,
                ],
            ],
            ShopPage::trimPageId(ShopPage::SHIPPING)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::SHIPPING),
                'description'=>Sii::t('sii','tells how we will deliver products.'),
                'alias'=>['shippings'],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPage::SHIPPING,
                ],
            ],
            ShopPage::trimPageId(ShopPage::RETURNS)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::RETURNS),
                'description'=>Sii::t('sii','displays our returns policy.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::CUSTOM_PAGE,
                    'params'=>[
                        'page'=>ShopPage::RETURNS,
                    ],
                ],
            ],
            ShopPage::trimPageId(ShopPage::REFUND)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::REFUND),
                'description'=>Sii::t('sii','displays our refund policy.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::CUSTOM_PAGE,
                    'params'=>[
                        'page'=>ShopPage::REFUND,
                    ],
                ],
            ],
            ShopPage::trimPageId(ShopPage::TOS)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::TOS),
                'description'=>Sii::t('sii','displays our terms of service.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::CUSTOM_PAGE,
                    'params'=>[
                        'page'=>ShopPage::TOS,
                    ],
                ],
            ],
            ShopPage::trimPageId(ShopPage::PRIVACY)=>[
                'name'=>ShopPayload::getShopPageTitle(ShopPage::PRIVACY),
                'description'=>Sii::t('sii','displays our privacy policy.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=>ShopPayload::CUSTOM_PAGE,
                    'params'=>[
                        'page'=>ShopPage::PRIVACY,
                    ],
                ],
            ],
            'login'=>[
                'name'=>Sii::t('sii','Login'),
                'description'=>Sii::t('sii','for member only, get a more personalized service.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'MessengerPayload',
                    'type'=>MessengerPayload::ACCOUNT_LINK,
                ],
            ],
            'logout'=>[
                'name'=>Sii::t('sii','Logout'),
                'description'=>Sii::t('sii','return as guest.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'MessengerPayload',
                    'type'=>MessengerPayload::ACCOUNT_UNLINK,
                ],
            ],
            'subscription'=>[
                'name'=>Sii::t('sii','Subscription'),
                'description'=>Sii::t('sii','manage subscriptions.'),
                'alias'=>[],
                'payload'=>[
                    'class'=>'ShopPayload',
                    'type'=> ShopPayload::SUBSCRIPTION,
                ],
            ],
        ];     
    }
    /**
     * Generate the payload according to command
     * @param type $command
     * @param type $app
     * @param type $sender
     * @return $payloadClass
     */
    public static function generatePayload($command)
    {
        $config = self::getConfig();
        //check if match command alias
        foreach ($config as $mainCommand => $setting) {
            if (isset($setting['alias']) && in_array($command,$setting['alias'])){
                $command = $mainCommand;
                break;
            }
        }
        
        if (isset($config[$command]['payload'])){
           $payloadClass = $config[$command]['payload']['class'];
           $payloadType = $config[$command]['payload']['type'];
           $params = isset($config[$command]['payload']['params']) ? $config[$command]['payload']['params']: [];
           return new $payloadClass($payloadType,$params);
        }
        else
            return new ChatbotPayload('dummy');//dummy payload; expect nothing to happen
    }
    /**
     * Check if command exists
     * @param type $command
     * @return type
     */
    public static function hasCommand($command)
    {
        return in_array($command, self::getCommands());
    }
    /**
     * Get the full supported menu commands
     * @return type
     */
    public static function getCommands()
    {
        $commands = [];
        foreach (self::getConfig() as $command => $config) {
            $commands[] = $command;
            if (isset($config['alias']) && !empty($config['alias'])){
                foreach ($config['alias'] as $alias) {
                    $commands[] = $alias;
                }
            }
        }
        logTrace(__METHOD__,$commands);
        return $commands;
    }
    /**
     * Get the main menu commands for displaying
     * @see ShopMenuView
     * @return type
     */
    public static function getMainMenu()
    {
        $commands = self::getConfig();
        unset($commands['help']);
        unset($commands['menu']);
        unset($commands['shortcut']);
        return $commands;
    }    
}
