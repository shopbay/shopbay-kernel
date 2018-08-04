<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.components.ShopPage');
Yii::import('common.modules.shops.components.ShopNavigation');
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
Yii::import('common.modules.shops.models.settings.NavigationSettingsTrait');
/**
 * Description of NavigationSettingsForm
 * Menu item data formmat:
 * [
 *  [ 
 *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
 *  ],
 *  [ 
 *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
 *  ]
 * ]
 * @author kwlok
 */
class NavigationSettingsForm extends BaseShopSettingsForm 
{
    use NavigationSettingsTrait;
    /**
     * Set to 'ShopPage'
     * @inheritdoc
     */
    public function getPageObject()
    {
        return 'ShopPage';
    }
    /**
     * Specify which navigation types to include
     * @return type
     */
    public function getIncludeNavigationTypes()
    {
        return [
            ShopNavigation::$typeCategory,
            ShopNavigation::$typeCustomPage,
            ShopNavigation::$typeLink,
        ];
    }      
   
}
