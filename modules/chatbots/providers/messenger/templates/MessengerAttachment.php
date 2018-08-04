<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerMessage');
/**
 * Description of MessengerAttachment
 *
 * @author kwlok
 */
class MessengerAttachment extends MessengerMessage
{
    /**
     * Media type
     * @var string
     */
    public $type;
    /**
     * Media url
     * @var string
     */
    public $url;
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,$type,$url)
    {
        parent::__construct($recipient);
        $this->type = $type;
        $this->url = $url;
    }    
    /**
     * Define message structure according to type
     * @return array
     */
    public function getMessage() 
    {
        return [
            'attachment' => [
                'type' => $this->type,
                'payload' => [
                   'url' => $this->url,
                ],
            ],
        ];
    }

}
