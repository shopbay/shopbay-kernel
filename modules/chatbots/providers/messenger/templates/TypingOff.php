<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.SenderAction');
/**
 * Description of TypingOff
 * Turn typing indicators off
 *
 * @author kwlok
 */
class TypingOff extends SenderAction
{
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient)
    {
        parent::__construct($recipient,'typing_off');
    }
}
