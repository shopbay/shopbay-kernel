<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.MessengerMenuItem');
/**
 * Description of PostbackMenuItem
 *
 * @author kwlok
 */
class PostbackMenuItem extends MessengerMenuItem
{
    public static $payloadLengthLimit = 1000;
    /**
     * For postback buttons, this data will be sent back to you via webhook
     * payload has a 1000 character limit
     */
    protected $payload;
    /**
     * Constructor.
     * @param string $title
     * @param string $payload
     */
    public function __construct($title,$payload)
    {
        parent::__construct(MessengerMenuItem::TYPE_POSTBACK, $title);
        $this->payload = $payload;
        $this->checkLengthLimit('payload', self::$payloadLengthLimit);
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return array_merge(parent::getData(),[
            'payload' => $this->payload,
        ]);    
    }
}
