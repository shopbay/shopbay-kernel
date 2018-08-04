<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.models.MessengerModelTrait');
/**
 * Description of MessengerMenuItem
 *
 * @author kwlok
 */
abstract class MessengerMenuItem extends CComponent
{
    use MessengerModelTrait;
    public static $titleLengthLimit = 30;

    CONST TYPE_URL = 'web_url';
    CONST TYPE_POSTBACK = 'postback';
    /**
     * Value is web_url or postback 
     */
    protected $type;
    /**
     * Button title has a 30 character limit
     */
    protected $title;
    /**
     * Constructor.
     * @param string $type
     * @param string $title
     */
    public function __construct($type,$title)
    {
        $this->type = $type;
        $this->title = $title;
        $this->checkLengthLimit('title', self::$titleLengthLimit);
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
        ];    
    }

}
