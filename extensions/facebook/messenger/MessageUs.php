<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MessageUs
 * The "Message Us" plugin can be used to immediately start a conversation and send the person to Messenger. 
 * On the desktop web, the user is sent to messenger.com and on mobile they are sent to the Messenger native app.
 * 
 * @author kwlok
 */
class MessageUs extends CWidget
{
    /**
     * Facebook app id
     * @var string
     */
    public $appId;
    /**
     * Facebook page id
     * @var string
     */
    public $pageId;
    /**
     * blue or white
     * @var string
     */
    public $color = 'blue';
    /**
     * standard, large or xlarge
     * @var string
     */
    public $size = 'standard';
    /**
     * Run widget
     */
    public function run()
    {
        $this->validate();
        $this->registerSetupScript();
        $this->renderPluginCode();
    } 
    /**
     * Do validation before rendering
     */
    protected function validate() 
    {
        //nothing
    }
    /**
     * Render plugin code
     */
    protected function renderPluginCode()
    {
        echo CHtml::tag('div', $this->pluginParams,'');    
    }
    /**
     * @return array get plugin params
     */
    protected function getPluginParams()
    {
         return [
            'class'=>'fb-messengermessageus',
            'messenger_app_id'=>$this->appId,
            'page_id'=>$this->pageId,
            'color'=>$this->color,
            'size'=>$this->size,
        ];       
    }
    /**
     * Setup script
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
