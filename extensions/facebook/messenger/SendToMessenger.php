<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.facebook.messenger.MessageUs');
/**
 * Description of SendToMessenger
 * The "Send to Messenger" plugin is used to trigger an authentication event to your webhook. 
 * You can pass in data to know which user and transaction was tied to the authentication event, and link the user on your back-end.
 * 
 * @author kwlok
 */
class SendToMessenger extends MessageUs
{
    /**
     * Custom state param
     * It has a limit of 150 characters. (set by Facebook)
     * data-ref should be encoded and encrypted
     * @var string
     */
    public $dataRef;
    /**
     * The callback script when e.event is "clicked"
     * <pre>
     * //Example, use e.ref to reference "data-ref"
     * console.log('data-ref: '+e.ref);
     * if (e.is_after_optin)
     *     console.log('event happened after the confirmation pop-up is confirmed.');
     * </pre>
     * @var string 
     * @see getEventSubscriptionScript()
     */
    public $clickedCallback = 'console.log("send_to_messenger clicked! ",e.ref)';
    /**
     * The callback script when e.event is "rendered"
     * <pre>
     * //Example
     * console.log('send_to_messenger rendered');
     * </pre>
     * @var string 
     * @see getEventSubscriptionScript()
     */
    public $renderedCallback = 'console.log("send_to_messenger rendered")';
    /**
     * The callback script when e.event is "not_you"
     * <pre>
     * //Example
     * console.log('send_to_messenger not_you');
     * </pre>
     * @var string 
     * @see getEventSubscriptionScript()
     */
    public $notyouCallback = 'console.log("send_to_messenger not_you")';
    /**
     * Do validation before rendering
     */
    protected function validate() 
    {
        if (isset($this->dataRef) && strlen($this->dataRef)>150){
            throw new CException('data-ref has a limit of 150 characters.');
        }
    }
    /**
     * @return array get plugin params
     */
    protected function getPluginParams()
    {
         return [
            'class'=>'fb-send-to-messenger',
            'messenger_app_id'=>$this->appId,
            'page_id'=>$this->pageId,
            'color'=>$this->color,
            'size'=>$this->size,
            'data-ref'=>$this->dataRef,
        ];       
    }
    /**
     * Adds event subscription script for callback
     * Example scripts:
     * <pre>
     * //use e.ref to reference "data-ref"
     * $script = <<<EOJS
     *   if (e.event=='rendered')
     *     console.log('send_to_messenger rendered');
     *   if (e.event=='not_you')
     *     console.log('send_to_messenger not_you');
     *   if (e.event=='clicked'){
     *     console.log('data-ref: '+e.ref);
     *     if (e.is_after_optin)
     *         console.log('event happened after the confirmation pop-up is confirmed.');
     *   }
     * EOJS;
     * </pre>
     * @see https://developers.facebook.com/docs/messenger-platform/plugin-reference/send-to-messenger?locale=en_US
     */
    protected function getEventSubscriptionScript()
    {
        $script = <<<EOJS
FB.Event.subscribe('send_to_messenger', function(e) {
    if (e.event=='rendered'){
        $this->renderedCallback
    }
    if (e.event=='not_you'){
        $this->notyouCallback
    }
    if (e.event=='clicked'){
        $this->clickedCallback
    }
});                    
EOJS;
        return $script;
    }    
    /**
     * Setup script
     * @param
     */
    protected function registerSetupScript()
    {
        if($this->appId !== null) {
            $js = <<<EOJS
window.fbAsyncInit = function() {
  FB.init({
    appId: "$this->appId",
    xfbml: true,
    version: "v2.6"
  });
  $this->eventSubscriptionScript          
};
(function(d, s, id){
   var js, fjs = d.getElementsByTagName(s)[0];
   if (d.getElementById(id)) { return; }
   js = d.createElement(s); js.id = id;
   js.src = "//connect.facebook.net/en_US/sdk.js";
   fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
EOJS;
            Helper::registerJs($js,get_class($this),CClientScript::POS_BEGIN);         
        }        
    }    
}
