<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SFavicon
 *
 * @author kwlok
 */
class SFavicon extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.sfavicon.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'sfavicon';    
    /**
     * Enable the favicon
     * @var boolean Default to true
     */
    public $enable = true;    
    /**
     * The favicon url
     * @var string 
     */
    public $url;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->url))
            throw new CException('Favicon url must be set');
        
        if ($this->enable){
            $favicon = $this->url;
            $script = <<<EOJS
$(window).load(function () {
    $('head').append('<link href="$favicon" rel="shortcut icon" type="image/x-icon" />');
});
EOJS;
            Helper::registerJs($script,__CLASS__,CClientScript::POS_HEAD);    
        }
    }
}
