<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of PostbackButton
 *
 * @author kwlok
 */
class PostbackButton extends MessengerButton
{
    protected $type = 'postback';//button type
}
