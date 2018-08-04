<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.templates.MessengerTemplate');
Yii::import('common.modules.chatbots.providers.messenger.buttons.AccountUnlinkButton');
/**
 * Description of AccountUnlinkingTemplate
 *
 * @author kwlok
 */
class AccountUnlinkingTemplate extends MessengerTemplate
{
    /**
     * Constructor.
     * @param string $recipient
     * @param string $text
     */
    public function __construct($recipient,$text)
    {
        $button = new AccountUnlinkButton();
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