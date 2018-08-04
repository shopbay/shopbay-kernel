<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.ThreadSetting');
/**
 * Description of MessengerThread
 *
 * @author kwlok
 */
abstract class MessengerThread extends ThreadSetting
{
    CONST NEW_THREAD = 'new_thread';
    CONST EXISTING_THREAD = 'existing_thread';
    /**
     * Thread state
     * @var string
     */
    protected $state;
    /**
     * Call to actions (Menu)
     * @var array
     */
    protected $menus = [];
    /**
     * Constructor.
     * @param string $state
     * @param string $menus
     */
    public function __construct($state,$menus)
    {
        parent::__construct(ThreadSetting::CALL_TO_ACTIONS);
        $this->state = $state;
        if (is_array($menus)){
            foreach ($menus as $menu) {
                $this->addMenu($menu);
            }
        }
        else {
            $this->addMenu($menus);
        }
    }
    /**
     * Add call to action (menu item)
     * @param MessengerMenuItem $menu
     * @throws CException
     */
    protected function addMenu($menu)
    {
        if (!$menu instanceof MessengerMenuItem)
            throw new CException('Invalid call to action');
        $this->menus[] = $menu->data;
    }    
    /**
     * Get data 
     * @return array
     */
    public function getData()
    {
        return array_merge(parent::getData(),[
            'thread_state' => $this->state,
            'call_to_actions' => $this->menus,
        ]);
    }    
}
