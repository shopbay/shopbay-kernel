<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.MessengerMenuItem');
/**
 * Description of UrlMenuItem
 *
 * @author kwlok
 */
class UrlMenuItem extends MessengerMenuItem
{
    /**
     * For web_url buttons, this URL is opened in a mobile browser when the button is tapped
     */
    protected $url;
    /**
     * Constructor.
     * @param string $title
     * @param string $url
     */
    public function __construct($title,$url)
    {
        parent::__construct(MessengerMenuItem::TYPE_URL, $title);
        $this->url = $url;
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return array_merge(parent::getData(),[
            'url' => $this->url,
        ]);    
    }
}
