<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SeoSettingsForm
 * NOTE: Model associated with this trait must be a child class of JsonSettingsForm
 * As this trait assumes some attributes 'ownerClass', 'ownerAttribute' etc of JsonSettingsForm are presented
 * 
 * @author kwlok
 */
trait SeoSettingsTrait
{
    /**
     * Local attributes
     */
    public $generateSitemap = 0;//default 1=yes (0=No)
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['generateSitemap', 'boolean'],
        ]);
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'generateSitemap' => Sii::t('sii','Generate sitemap'),
        ]);
    }
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_seo';       
    }  
   
}
