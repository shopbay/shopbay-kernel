<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.buttons.MessengerButton');
/**
 * Description of AccountUnlinkButton
 *
 * @author kwlok
 */
class AccountUnlinkButton extends MessengerButton
{
    protected $type = 'account_unlink';//button type
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(__CLASS__, null);
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'type' => $this->type,
        ];    
    }
}