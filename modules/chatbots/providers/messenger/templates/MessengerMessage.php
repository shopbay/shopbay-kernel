<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.models.MessengerModelTrait');
/**
 * Description of MessengerMessage
 *
 * @author kwlok
 */
abstract class MessengerMessage extends CComponent
{
    use MessengerModelTrait;
    /**
     * Recipient
     * @var string
     */
    protected $recipient;
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient)
    {
        $this->recipient = $recipient;
    }
    /**
     * Define message structure according to type
     * @return array
     */
    abstract function getMessage();
    /**
     * Get data 
     * @return array
     */
    public function getData()
    {
        return [
            'recipient' =>  [
                'id' => $this->recipient
            ],
            'message' => $this->getMessage()
        ];
    }    
}
