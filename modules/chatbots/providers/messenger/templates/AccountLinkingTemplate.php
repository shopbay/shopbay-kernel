<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerTemplate');
Yii::import('common.modules.chatbots.providers.messenger.buttons.AccountLinkButton');
/**
 * Description of AccountLinkingTemplate
 *
 * @author kwlok
 */
class AccountLinkingTemplate extends MessengerTemplate
{
    /**
     * Constructor.
     * @param string $recipient
     * @param string $text
     * @param string $url account link url
     */
    public function __construct($recipient,$text,$url)
    {
        $button = new AccountLinkButton($url);
        $payload = [
            'template_type' => 'button',
            'text'=> $text,
            'buttons'=>[
                $button->data,
            ],
        ];
        parent::__construct($recipient,$payload);
    }    

}