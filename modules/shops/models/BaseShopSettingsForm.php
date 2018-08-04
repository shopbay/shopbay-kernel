<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.JsonSettingsForm');
/**
 * Description of BaseShopSettingsForm
 *
 * @author kwlok
 */
class BaseShopSettingsForm extends JsonSettingsForm 
{
    /*
     * setting attribute
     */
    public $shop_id;
    /**
     * @return The owner model class
     */
    public function getOwnerClass()
    {
        return 'Shop';
    }
    /**
     * @return The owner model class
     */
    public function getOwnerSettingClass()
    {
        return 'ShopSetting';
    }
    /**
     * @return The owner model attribute
     */
    public function getOwnerAttribute()
    {
        return 'shop_id';
    }
}
