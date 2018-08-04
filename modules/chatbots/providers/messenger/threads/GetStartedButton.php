<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.MessengerThread');
/**
 * Description of GetStartedButton
 *
 * @author kwlok
 */
class GetStartedButton extends MessengerThread
{
    /**
     * Constructor.
     * @param string $payload
     */
    public function __construct($payload)
    {
        parent::__construct(MessengerThread::NEW_THREAD,$payload);
    }
}
