<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.MessengerThread');
/**
 * Description of PersistentMenu
 *
 * @author kwlok
 */
class PersistentMenu extends MessengerThread
{
    public static $menusLimit = 5;
    /**
     * Constructor.
     * @param string $menus
     */
    public function __construct($menus)
    {
        parent::__construct(MessengerThread::EXISTING_THREAD,$menus);
    }
}
