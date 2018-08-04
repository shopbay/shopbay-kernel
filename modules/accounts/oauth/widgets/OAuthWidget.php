<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.oauth.OAuth');
/**
 * Description of OAuthWidget
 *
 * @author kwlok
 */
class OAuthWidget extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.modules.accounts.oauth.widgets.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'zocial';    
    /**
     * @var boolean indicate if to show only network provider name/icon without link
     */
    public $disableLink = false;
    /**
     * @var string $route id of module and controller (eg. module/controller) for which to generate oauth urls
     */
    public $route = false;
    /**
     * @var boolean $iconOnly the flag that displays social buttons as icons
     */
    public $iconOnly = false;
    /**
     * @var integer $popupWidth the width of the popup window
     */
    public $popupWidth = 480;
    /**
     * @var integer $popupHeight the height of the popup window
     */
    public $popupHeight = 480;
    /**
     * @var array of network provider to be displayed; If null, it will load from OAuth::getConfig()
     */
    public $providers;
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        parent::init();
        if(!$this->route)
            $this->route = $this->controller->module ? $this->controller->module->id . '/' . $this->controller->id : $this->controller->id;
        $this->registerScripts();
    }    

    public function run()
    {
        echo CHtml::openTag('div', array('id' => 'oauthWidget'.$this->id,'class' => 'oauthWidget'));
        if (!isset($this->providers)){
            $config = OAuth::getConfig();
            $this->providers = $config['providers'];
            foreach($this->providers as $provider => $settings){
                if($settings['enabled'])
                    $this->render('index', array('provider' => $provider));
            }
        }
        else {
            foreach($this->providers as $provider){
                $this->render('index', array('provider' => $provider));
            }
        }
        echo CHtml::closeTag('div');
    }

    protected function registerScripts()
    {
        // Start the JS string
        $js = <<<EOJS
$(function() {
    $('.oauthWidget a').click(function() {
        var signinWin;
        var screenX     = window.screenX !== undefined ? window.screenX : window.screenLeft,
            screenY     = window.screenY !== undefined ? window.screenY : window.screenTop,
            outerWidth  = window.outerWidth !== undefined ? window.outerWidth : document.body.clientWidth,
            outerHeight = window.outerHeight !== undefined ? window.outerHeight : (document.body.clientHeight - 22),
            width       = $this->popupWidth,
            height      = $this->popupHeight,
            left        = parseInt(screenX + ((outerWidth - width) / 2), 10),
            top         = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
            options    = (
            'width=' + width +
            ',height=' + height +
            ',left=' + left +
            ',top=' + top
            );
        signinWin=window.open(this.href,'Login',options);
        if (window.focus) {signinWin.focus()}
        return false;
    });
});
EOJS;
        Yii::app()->clientScript->registerScript(__CLASS__.$this->id, $js, CClientScript::POS_END);
    }
    
    protected function getButtonText($provider,$prefix=true)
    {
        $text = Sii::t('sii',$provider);
        if ($prefix)
            $text = (Yii::app()->user->isGuest ? Sii::t('sii','Sign in with') : Sii::t('sii','Connect with')).' '.$text;
        return $text;
    }
}
