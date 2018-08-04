<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ChatbotWebhookTrait
 *
 * @author kwlok
 */
trait ChatbotWebhookTrait  
{
    /**
     * Enable wehboook url. Default to "false"
     * @var boolean 
     */
    public $enableWebhook = false;    
    /**
     * The chatbot webhook hosting domain; Default to shopbay-chatbot domain
     * @see init()
     * @var type 
     */
    public $webhookDomain;    
    /**
     * Get the webhook url
     * @param string $clientId The client model id
     * @param string $provider The chatbot provider
     */
    public function getWebhookUrl($clientId,$provider)
    {
        return Yii::app()->urlManager->createDomainUrl($this->webhookDomain,'/'.$provider.'/webhook/'.$clientId,true);
    }
}
