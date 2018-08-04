<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of WebUrlButton
 *
 * @author kwlok
 */
class WebUrlButton extends MessengerButton
{
    protected $type = 'web_url';//button type
    /**
     * Constructor.
     * @param string $title
     * @param string $url
     */
    public function __construct($title,$url)
    {
        $this->title = $title;
        $this->payload = $url;//url is set for payload
        parent::__construct($title, $url);
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->payload,
        ];    
    }
}