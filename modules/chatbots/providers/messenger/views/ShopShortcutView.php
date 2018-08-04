<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopShortcutView
 *
 * @author kwlok
 */
class ShopShortcutView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $count = 0;
        $message = '';//reset
        $menus = HelpPayload::getMainMenu();
        foreach ($menus as $menu => $config) {
            $count++;
            $commands = $menu;
            if (isset($config['alias']) && !empty($config['alias'])){
                $commands .= ', '.implode(', ', $config['alias']);
            }
            if ($count==1){
                $message .= "Type following keywords for shortcuts:\n\n";
            }
            $message .= "$commands - ".$config['description']."\n\n";
            if ($count%4==0){//display 4 shortcuts in one message
                $this->sendTextMessage($this->context->sender,$message);
                $message = '';//reset
            }
        }

        if (strlen($message)>0)//has last batch of commands to display
                $this->sendTextMessage($this->context->sender,$message);
    }
}
