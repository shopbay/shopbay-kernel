<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of HelpView
 *
 * @author kwlok
 */
class HelpView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $help = 'Type "menu" to get main menu. "login" for more personalized service, "logout" to return as guest, "support" to speak to our live agent'."\n";
        $help .= "\n".'Type "shortcut" to get you the shortcut keywords for menu access.';
        $help .= "\n\nUse a few words or phrases, for example, “search _keywords_” etc to find stuff you are interested.";
        $this->sendTextMessage($this->context->sender,$help);
    }
}
