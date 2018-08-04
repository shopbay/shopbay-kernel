<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Postback Event Processor
 * @see PostbackEvent
 * 
 * @author kwlok
 */
class PostbackController extends MessengerBot 
{
    /**
     * Process an event 
     * @param PostbackEvent $event
     */
    public function process($event)
    {
        logInfo(__METHOD__." Received $event->className for user $event->sender and page $event->recipient at $event->timestampString for payload",$event->payload);
        $context = $this->createContext($event);
        $this->processPayload($context,$event->payload);
    }
}