<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerMessage');
/**
 * Description of TextMessage
 *
 * @author kwlok
 */
class TextMessage extends MessengerMessage
{
    public static $textLengthLimit = 320;
    public static $metadataLengthLimit = 1000;
    /**
     * Message text
     * @var string
     */
    public $text;
    /**
     * Message meta data
     * @var string
     */
    public $metadata = 'USER_DEFINED_METADATA';
    /**
     * Constructor.
     * @param string $recipient
     */
    public function __construct($recipient,$text,$metadata=null)
    {
        parent::__construct($recipient);
        $this->text = $text;
        $this->metadata = $metadata;
        $this->checkLengthLimit('text', self::$textLengthLimit);
        $this->checkLengthLimit('metadata', self::$metadataLengthLimit);
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
        ];
    }

}
