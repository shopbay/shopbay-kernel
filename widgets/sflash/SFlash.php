<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * SFlash widget class file.
 *
 * @author kwlok
 */
class SFlash extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.sflash.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'sflash';    
    /**
     * Flash key
     * @var mixed Single flash key or array of keys
     */
    public $key;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (isset($this->key))
            if (is_string($this->key) || is_array($this->key))
                $this->render('index');
    }
    /**
     * Get Flash Icon
     * @param type $type
     * @return string
     */
    public function getFlashIcon($type,$icon=null)
    {
        if (isset($icon))
            return $icon;
        else {
            switch ($type) {
                case 'success':
                    return '<i class="fa fa-check"></i>';
                case 'notice':
                    return '<i class="fa fa-warning"></i>';
                case 'error':
                    return '<i class="fa fa-exclamation-circle"></i>';
                case 'advice':
                    return '<i class="fa fa-life-saver"></i>';
                default:
                    return '<i class="fa fa-info-circle"></i>';
            }
        }
    }
    /**
     * Flash theme (for customization of color theme)
     * When set, a customized css file has to be provided
     * @see inside views\_flash.php
     */
    public function getFlashTheme()
    {
        return 'theme';//a placeholder method to set reminder of this feature
    }
}
