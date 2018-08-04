<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Message Read Event Processor
 * @see MessageReadEvent
 * 
 * @author kwlok
 */
class MessageReadController extends MessengerBot 
{
    /**
     * Process an event 
     * @param MessageReadEvent $event
     */
    public function process($event)
    {
        $timestamp = $this->formatTimestamp($event->watermark);
        logInfo(__METHOD__." Received $event->className for watermark $timestamp and sequence number $event->sequenceNumber");
    }
}