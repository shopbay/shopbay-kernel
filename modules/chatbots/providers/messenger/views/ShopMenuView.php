<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopMenuView
 *
 * @author kwlok
 */
class ShopMenuView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        $bubbleName = Sii::t('sii','Main Menu');
        $bubbleTitle = Sii::t('sii','Choose a menu');
        $buttons = [];
        
        $menus = HelpPayload::getMainMenu();
        foreach ($menus as $menu => $config) {
            $helpPayload = HelpPayload::generatePayload($menu);
            $buttons[] = new PostbackButton($config['name'], $helpPayload->toString());
            //Put into $bubbles
            if (count($buttons)==Bubble::$buttonsLimit){
                $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
                $buttons = [];//reset buttons
            }
        }
        //oustanding buttons
        if (!empty($buttons))
            $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
        }
    }
}
