<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.TextMessage');
Yii::import('common.modules.chatbots.providers.messenger.templates.QuickReply');
/**
 * Description of QuickReplies
 *
 * @author kwlok
 */
class QuickReplies extends TextMessage
{
    /**
     * Quick replies to be included in message 
     * @var array
     */
    protected $quickReplies = [];
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,$text,$quickReplies)
    {
        if (is_array($quickReplies)){
            foreach ($quickReplies as $quickReply) {
                $this->addQuickReply($quickReply);
            }
        }
        else {
            $this->addQuickReply($quickReplies);
        }
        parent::__construct($recipient,$text);
    }    
    /**
     * Define message structure according to type
     * @return array
     */
    public function getMessage() 
    {
        return [
            'text' => $this->text,
            'metadata' => $this->metadata,
            'quick_replies' => $this->quickReplies,
        ];
    }
    /**
     * Add quick reply
     * @param QuickReply $reply
     * @throws CException
     */
    protected function addQuickReply($reply)
    {
        if (!$reply instanceof QuickReply)
            throw new CException('Invalid quick reply');
        $this->quickReplies[] = $reply->data;
    }    
}
