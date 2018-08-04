<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Delivery Confirmation Event Processor
 * @see DeliveryEvent
 * 
 * @author kwlok
 */
class DeliveryController extends MessengerBot 
{
    /**
     * Process an event 
     * @param MessegnerEvent $event
     */
    public function process($event)
    {
        $timestamp = $this->formatTimestamp($event->watermark);
        // Iterate over each delivered message id
        foreach ($event->messageIds as $messageId) {
            logInfo(__METHOD__." Received $event->className confirmation for message ID $messageId");
        }            
        logInfo(__METHOD__." All message before $timestamp were delivered.");
    }
}