<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.modules.activities.models.Activity");
Yii::import("common.services.exceptions.*");
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Description of ChatbotManager
 *
 * @author kwlok
 */
class ChatbotManager extends ServiceManager 
{
    /**
     * Create model
     * 
     * @param string $provider chatbot provider
     * @param CModel $owner CModel model to own the chatbot
     * @return Chatbot
     * @throws CException
     */
    public function create($provider,$owner)
    {
        if (!$owner instanceof CModel)
            throw new ServiceValidationException(Sii::t('sii','Invalid model'));
        
        $ownerId = $owner->id;
        $ownerType = get_class($owner);
        if (($chatbot = Chatbot::model()->forOwner($ownerType,$ownerId,$provider)->find()) == null ){
            $chatbot = new Chatbot();
            $chatbot->client_id = md5($ownerId.'.'.$provider);//include provider as part of key
            $chatbot->provider = $provider;
            $chatbot->owner_id = $ownerId;
            $chatbot->owner_type = $ownerType;
            $chatbot->status = Process::CHATBOT_OFFLINE;

            $this->validate($owner->account_id/*dummy, not used*/, $chatbot, false);
            return $this->execute($chatbot, array(
                'insert'=>self::EMPTY_PARAMS,
                //'recordActivity'=>Activity::EVENT_CREATE,//not to create activity as chatbot creation is automatic (and not done by user)
            ),$chatbot->getScenario());
        }
        else {
            logInfo(__METHOD__.' chatbot already exists.',[$provider,$ownerType,$ownerId]);
            return $chatbot;
        }
    }    
    /**
     * Update chatbot settings
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel chatbot model to update
     * @param array $settingValues The settings values
     * @return CModel $model
     * @throws CException
     */
    public function updateSettings($user,$model,$settingValues=[])
    {
        $this->verifyChatbot($user, $model);
        
        return $this->execute($model, [
            'saveSettings'=>$settingValues,
            'recordActivity'=>[
                'description'=>Sii::t('sii','{chatbot} Settings',['{chatbot}'=>ucfirst($model->provider)]),
                'event'=>Activity::EVENT_UPDATE,
            ],
        ]);
    }  
    /**
     * Send Greeting Text (Apply to Facebook Messenger only)
     * @param type $user
     * @param Chatbot $chatbot
     * @param type $greetingText
     * @return type
     * @throws ServiceValidationException
     * @throws CException
     */
    public function sendGreetingText($user,$chatbot,$greetingText)
    {
        $this->verifyChatbot($user, $chatbot);

        $messenger = $this->getMessenger($chatbot);

        if ($messenger->sendGreetingText($greetingText)){
            return $this->execute($chatbot, [
                'saveSettings'=>[
                    'greetingText'=>$greetingText,
                    'greetingTextLastSent'=>time(),
                ],
                'recordActivity'=>[
                    'event'=>Activity::EVENT_UPDATE,
                    'account'=>$user,
                    'description'=>Sii::t('sii','Send Greeting Text'),
                ],
            ]);
        }
        else 
            throw new CException(Sii::t('sii','Failed to send greeting text'));        
    }
    /**
     * Send Get Started Button (Apply to Facebook Messenger only)
     * @param type $user
     * @param Chatbot $chatbot
     * @return type
     * @throws ServiceValidationException
     * @throws CException
     */
    public function sendGetStartedButton($user,$chatbot)
    {
        $this->verifyChatbot($user, $chatbot);

        $messenger = $this->getMessenger($chatbot);

        $getStartedPayload = new MessengerPayload(MessengerPayload::GET_STARTED);
        $payload = new PayloadMenuItem($getStartedPayload->toString());
                
        if ($messenger->sendGetStartedButton($payload)){
            return $this->execute($chatbot, [
                'saveSettings'=>[
                    'getStartedButtonLastSent'=>time(),
                ],
                'recordActivity'=>[
                    'event'=>Activity::EVENT_UPDATE,
                    'account'=>$user,
                    'description'=>Sii::t('sii','Send Get Started Button'),
                ],
            ]);
        }
        else 
            throw new CException(Sii::t('sii','Failed to send get started button'));        
    }    
    /**
     * Send Persistent Menu (Apply to Facebook Messenger only)
     * @param type $user
     * @param Chatbot $chatbot
     * @return type
     * @throws ServiceValidationException
     * @throws CException
     */
    public function sendPersistentMenu($user,$chatbot)
    {
        $this->verifyChatbot($user, $chatbot);

        $messenger = $this->getMessenger($chatbot);

        $messengerShop = new MessengerShop($chatbot->owner->id);
                
        if ($messenger->sendPersistenMenu($messengerShop->getPesistentMenuItems())){
            return $this->execute($chatbot, [
                'saveSettings'=>[
                    'persistentMenuLastSent'=>time(),
                ],
                'recordActivity'=>[
                    'event'=>Activity::EVENT_UPDATE,
                    'account'=>$user,
                    'description'=>Sii::t('sii','Send Persistent Menu'),
                ],
            ]);
        }
        else 
            throw new CException(Sii::t('sii','Failed to send persistent menu'));        
    }       
    /**
     * Verify chatbot validity
     * @param type $user
     * @param Chatbot $chatbot
     * @throws ServiceValidationException
     */
    protected function verifyChatbot($user,$chatbot)
    {
        if (!$chatbot instanceof Chatbot)
            throw new ServiceValidationException(Sii::t('sii','Invalid service model'));
        
        if (!$chatbot->updatable())
            throw new ServiceValidationException(Sii::t('sii','Chatbot is not updatable.'));

        if (!$chatbot->isVerified)
            throw new ServiceValidationException(Sii::t('sii','Chatbot is not verified.'));

        $this->validate($user, $chatbot, true);
    }
    /**
     * Get Facebook messenger
     * @param type $chatbot
     */
    protected function getMessenger($chatbot)
    {
        return new MessengerBot($chatbot->owner->getClientAttribte('fbPageAccessToken'));
    }
}
