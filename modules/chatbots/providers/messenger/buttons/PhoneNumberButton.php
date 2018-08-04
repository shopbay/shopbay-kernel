<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of PhoneNumberButton
 *
 * @author kwlok
 */
class PhoneNumberButton extends MessengerButton
{
    protected $type = 'phone_number';//button type
}