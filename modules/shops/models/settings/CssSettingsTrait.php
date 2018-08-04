<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CssSettingsTrait
 * NOTE: Model associated with this trait must be a child class of JsonSettingsForm
 * As this trait assumes some attributes 'ownerClass', 'ownerAttribute' etc of JsonSettingsForm are presented
 *
 * @author kwlok
 */
trait CssSettingsTrait 
{
    /**
     * Local attributes
     */
    public $css;//default empty
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['css', 'safe'],
        ]);
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'css' => Sii::t('sii','CSS Script'),
        ]);
    }
    /**
     * Form display name
     * @return type
     */
    public function displayName()
    {
        return Sii::t('sii','{owner} CSS',['{owner}'=>$this->owner->displayName()]);
    }
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.design._form_css';       
    }       
    
    public function getDisclaimer()
    {
        $message = Sii::t('sii','Please design with care. {app} does not provide support for any UI issues caused by custom css script.',['{app}'=>app()->name]);
        $message .= ' '.Sii::t('sii','As a platform {app} often upgrades system including themes, we do not provide support either for any issues caused by upgrade.',['{app}'=>app()->name]);
        $message .= ' '.Sii::t('sii','We suggest you do UI preview to check your custom css script is working whenever you receive themes upgrade messages from us.');
        return $message;
    }
}
