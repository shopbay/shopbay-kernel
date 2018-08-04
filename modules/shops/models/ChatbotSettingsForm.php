<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
Yii::import('common.modules.shops.models.settings.MessengerSettingsTrait');
/**
 * Description of ChatbotSettingsForm
 *
 * @author kwlok
 */
class ChatbotSettingsForm extends BaseShopSettingsForm
{
    use MessengerSettingsTrait;
    /**
     * Init settings
     * Each provider must have a trait with a method maming convention 'init<provider>Settings'
     */
    public function loadInitValues()
    {
        foreach (Chatbot::getProviders() as $provider) {
            $this->{'init'.ucfirst($provider).'Settings'}();
        }
    }
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),$this->fbRules());
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),$this->fbAttributeLabels());
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array_merge(parent::attributeToolTips(),$this->fbAttributeToolTips());
    }  
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),$this->fbAttributeDisplayValues());
    }   
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_chatbot';       
    }         
    /**
     * OVERRIDDEN
     * This render the setting form
     * @param type $controller The controller used to render the form
     */
    public function renderActiveForm($controller)
    {
        $this->loadInitValues();
        $this->renderForm($controller);
    }    
    /**
     * Render each chatbot integration config
     * @return type
     */
    public function getSectionsData() 
    {
        $sections = new CList();
        //section 1: Facebook Messenger
        $sections->add(array('id'=>'facebook_messenger',
                             'name'=>Sii::t('sii','Facebook Messenger'),
                             'heading'=>true,'top'=>true,
                             'viewFile'=>'_form_chatbot_messenger','viewData'=>array('model'=>$this)));
        return $sections->toArray();
    }  
}
