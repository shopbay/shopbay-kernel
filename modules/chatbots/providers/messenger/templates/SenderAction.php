<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerMessage');
/**
 * Description of SenderAction
 *
 * @author kwlok
 */
abstract class SenderAction extends MessengerMessage
{
    protected $action;
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,$action)
    {
        $this->action = $action;
        parent::__construct($recipient);
    }
    /**
     * Define message structure according to type
     * @return array
     */
    public function getMessage() 
    {
        return $this->action;
    }
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
            'sender_action' => $this->getMessage(),
        ];
    } 
}
