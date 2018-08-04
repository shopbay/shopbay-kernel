<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.payloads.*');
/**
 * Description of MessengerPayload
 *
 * @author kwlok
 */
class MessengerPayload extends ChatbotPayload 
{
    /*
     * Messenger inherent payload types
     */
    CONST THREAD_SETTINGS    = 'thread_settings';
    CONST PERSISTENT_MENU    = 'persistent_menu';
    CONST GREETING           = 'greeting';
    CONST GET_STARTED_BUTTON = 'get_started_button';
    CONST GET_STARTED        = 'get_started';
    CONST TEXT               = 'text';
    CONST IMAGE              = 'image';
    CONST AUDIO              = 'audio';
    CONST VIDEO              = 'video';
    CONST FILE               = 'file';
    CONST ACCOUNT_LINK       = 'account_link';
    CONST ACCOUNT_UNLINK     = 'account_unlink';
    CONST RECEIPT            = 'receipt';
}
