<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.ThreadSetting');
Yii::import('common.modules.chatbots.providers.messenger.models.MessengerModelTrait');
/**
 * Description of GreetingText
 *
 * @author kwlok
 */
class GreetingText extends ThreadSetting
{
    use MessengerModelTrait;
    
    public static $textLengthLimit = 160;
    /**
     * Greeting text must be UTF-8 and has a 160 character limit
     * @var string
     */
    protected $text;
    /**
     * Constructor.
     * @param string $text
     */
    public function __construct($text)
    {
        parent::__construct(ThreadSetting::GREETING);
        $this->text = $text;
        $this->checkLengthLimit('text', self::$textLengthLimit);
    }
    /**
     * Get data 
     * @return array
     */
    public function getData()
    {
        return array_merge(parent::getData(),[
            'greeting' => [
                'text' => $this->text,
            ],
        ]);
    }    
}
