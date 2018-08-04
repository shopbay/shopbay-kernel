<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.ChatbotWebhookTrait');
/**
 * Description of ChatbotsModule
 *
 * @author kwlok
 */
class ChatbotsModule extends SModule 
{
    use ChatbotWebhookTrait;

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'chatbots.behaviors.*',
            'chatbots.components.*',
            'chatbots.models.*',
        ]);
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.ChatbotManager',
                'model'=>'Chatbot',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }    
}
