<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
/**
 * Description of MessengerSettingsTrait
 * NOTE: Model associated with this trait must be a child class of JsonSettingsForm
 * As this trait assumes some attributes 'ownerClass', 'ownerAttribute' etc of JsonSettingsForm are presented
 * 
 * @author kwlok
 */
trait MessengerSettingsTrait
{
    /**
     * Local attributes
     */
    public $fbBotClientId;
    public $fbCallbackUrl;
    public $fbVerifyToken;
    public $fbPageAccessToken;
    public $fbVerifyRequestSignature = 0;//default 1=yes (0=No)
    public $fbSecret;//the facebook app secret; Required if were to verify request signature 
    /**
     * Init settings
     * @see ChatbotSettingsForm::loadInitValues()
     */
    public function initMessengerSettings()
    {
        //Set facebook bot client id and create Chatbot record (for first time)
        if (empty($this->fbBotClientId)){
            $chatbot = Yii::app()->serviceManager->chatbotManager->create(Chatbot::MESSENGER,$this->owner);
            $this->fbBotClientId = $chatbot->client_id;
        }
        
        if (!isset($this->fbCallbackUrl)){
            $this->fbCallbackUrl = Yii::app()->getModule('chatbots')->getWebhookUrl($this->fbBotClientId,Chatbot::MESSENGER);
        }
    }
    /**
     * Declares the validation rules.
     */
    public function fbRules()
    {
        return [
            ['fbBotClientId, fbCallbackUrl, fbVerifyToken, fbPageAccessToken', 'required'],
            ['fbCallbackUrl, fbVerifyToken, fbPageAccessToken, fbSecret', 'length','max'=>1000],
            ['fbVerifyRequestSignature', 'boolean'],
            ['fbVerifyRequestSignature', 'ruleRequestSignature'],
            //only alphanumeric chars, upper/lower case, and dash 
            ['fbVerifyToken', 'match', 'pattern'=>'/^[A-Za-z0-9-]+$/', 'message'=>Sii::t('sii','Verify token accepts only alphabet letters, digits or hypen.')],
            //only alphanumeric chars, upper/lower case 
            ['fbPageAccessToken', 'match', 'pattern'=>'/^[A-Za-z0-9]+$/', 'message'=>Sii::t('sii','Page access token accepts only alphabet letters or digits.')],
            //only alphanumeric chars, upper/lower case 
            ['fbSecret', 'match', 'pattern'=>'/^[A-Za-z0-9]+$/', 'message'=>Sii::t('sii','App secret accepts only alphabet letters or digits.')],
        ];
    }
    /**
     * Validation for request signature
     * @param type $attribute
     * @param type $params
     */
    public function ruleRequestSignature($attribute,$params)
    {
        if ($this->fbVerifyRequestSignature && empty($this->fbSecret))
            $this->addError($attribute,Sii::t('sii','App secret is required to verify request signature.'));
    }
    /**
     * Declares attribute labels.
     */
    public function fbAttributeLabels()
    {
        return [
            'fbCallbackUrl' => Sii::t('sii','Callback URL'),
            'fbVerifyToken' => Sii::t('sii','Verify Token'),
            'fbPageAccessToken' => Sii::t('sii','Page Access Token'),
            'fbVerifyRequestSignature' => Sii::t('sii','Verify Request Signature'),
            'fbSecret' => Sii::t('sii','App Secret'),
        ];
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function fbAttributeToolTips()
    {
        return [
            'fbVerifyToken'=>Sii::t('sii','Create your own Verify Token (can be any string containing alphanumeric chars).'),
            'fbPageAccessToken'=>Sii::t('sii','Get your Facebook Page Access Token and insert it here.'),
            'fbVerifyRequestSignature'=>Sii::t('sii','Security: The HTTP request will contain an X-Hub-Signature header, using the app secret as the key. Select Yes if you want to validate the integrity and origin of the payload.'),
            'fbSecret'=>Sii::t('sii','To verify request signature, please get your Facebook App Secret and insert it here.'),
        ];
    }  
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function fbAttributeDisplayValues()
    {
        return [
            'fbCallbackUrl'=>CHtml::tag('div',['class'=>'data-element'],$this->fbCallbackUrl),
            'fbVerifyToken'=>CHtml::tag('div',['class'=>'data-element'],$this->fbVerifyToken),
            'fbPageAccessToken'=>CHtml::tag('div',['class'=>'data-element'],$this->fbPageAccessToken),
            'fbVerifyRequestSignature'=>CHtml::tag('div',['class'=>'data-element'],Helper::getBooleanValues($this->fbVerifyRequestSignature)),
            'fbSecret'=>CHtml::tag('div',['class'=>'data-element'],$this->fbSecret),
        ];
    }  
    
    public function getHasFbChatbot()
    {
        return $this->owner->hasChatbot(Chatbot::MESSENGER);
    }
    
    public function getFbChatbot()
    {
        return $this->owner->getChatbot(Chatbot::MESSENGER);
    }
    
    public function getFbAdvancedForm()
    {
        Yii::import('chatbots.models.ChatbotAdvancedForm');
        return new ChatbotAdvancedForm($this->fbBotClientId);
    }

    public function getFbPluginForm()
    {
        Yii::import('chatbots.models.ChatbotPluginForm');
        return new ChatbotPluginForm($this->fbBotClientId);
    }
    
    public function getFbSupportForm()
    {
        Yii::import('chatbots.models.ChatbotSupportForm');
        return new ChatbotSupportForm($this->fbBotClientId);
    }
    
}
