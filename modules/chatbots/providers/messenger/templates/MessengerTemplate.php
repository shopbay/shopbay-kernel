<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerMessage');
/**
 * Description of MessengerTemplate
 *
 * @author kwlok
 */
abstract class MessengerTemplate extends MessengerMessage
{
    /**
     * Template payload
     * @var string
     */
    protected $payload;
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,$payload)
    {
        parent::__construct($recipient);
        $this->payload = $payload;
    }    
    /**
     * Define message structure according to type
     * @return array
     */
    public function getMessage() 
    {
        return [
            'attachment' => [
                'type' => 'template',
                'payload' => $this->payload,
            ],
        ];
    }

}
