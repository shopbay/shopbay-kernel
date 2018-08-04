<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.ShopDomainForm');
Yii::import('common.modules.shops.models.settings.BrandSettingsTrait');
/**
 * Description of BrandSettingsForm
 *
 * @author kwlok
 */
class BrandSettingsForm extends ShopDomainForm 
{
    use BrandSettingsTrait;
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),$this->brandRules());
    }    
}
