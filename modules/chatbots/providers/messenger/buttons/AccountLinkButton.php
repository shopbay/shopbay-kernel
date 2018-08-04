<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of AccountLinkButton
 *
 * @author kwlok
 */
class AccountLinkButton extends MessengerButton
{
    protected $type = 'account_link';//button type
    /**
     * Constructor.
     * @param string $url account link url
     */
    public function __construct($url)
    {
        $this->payload = $url;//url is set for payload
        parent::__construct(__CLASS__, $url);
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'type' => $this->type,
            'url' => $this->payload,
        ];    
    }
}