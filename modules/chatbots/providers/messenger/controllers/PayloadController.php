<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.payloads.ChatbotPayload');
/**
 * PayloadController processes payload and render the corresponding view
 *
 * @author kwlok
 */
class PayloadController extends MessengerBot
{
    /**
     * Payload view mapping (for swapping)
     * @var array
     */
    public static $viewMapping = [
        'ShopHomePageView'=>'GetStartedView',//always set shop home page to GetStartedView
    ];
    /**
     * Process a payload 
     * @param ChatbotContext $context
     * @param string $encodedPayload The encoded payload 
     */
    public function processData($context,$encodedPayload=null)
    {
        logInfo(__METHOD__.' received payload: '.$encodedPayload);
        logInfo(__METHOD__.' received context: '.$context->toString());
        $payload = ChatbotPayload::decode($encodedPayload);
        
        if (strpos($payload->type, ChatbotPayload::LIVE_CHAT)!==false){
            $this->processLivechat($context, LiveChatPayload::START);
            return;//stop here
        }
        
        $view = $this->getPayloadView($payload->type);
        if ($view!=null){
            $viewObj = new $view($this->token);
            $viewObj->setContext($context);
            $viewObj->render($payload);
        }
        else {
            //unknown payload type; sending typing on and off to show response but no result
            logWarning(__METHOD__." unknown payload $payload->type",$payload->toString());
            $this->sendTypingOn($context->sender);
            $this->sendTypingOff($context->sender);
        }
    }
    /**
     * Expect payload type is tokenized by separated by "_"
     * @param type $type payload type
     * @return string
     */
    protected function getPayloadView($type) 
    {
        $page = explode('_', $type);
        if (is_array($page)){
            $view = ucfirst($page[0]);
            if (isset($page[1]))
                $view .= ucfirst($page[1]);
            if (isset($page[2]))
                $view .= ucfirst($page[2]);
            $view .= 'View';
        }
        else {
            $view = ucfirst($page).'View';
        }
        
        //check if to swap view
        if (isset(self::$viewMapping[$view])){
            $view = self::$viewMapping[$view];
        }
        
        //check if view file exists
        if (file_exists($this->getViewFile($view)))
            return $view;
        else{
            logWarning(__METHOD__." $view file not found!");
            return null;
        }
    }
    /**
     * Return the view file path
     * @return string
     */
    protected function getViewFile($view) 
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
    }
}
