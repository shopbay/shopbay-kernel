<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * SLoader widget class file.
 *
 * @author kwlok
 */
class SLoader extends SWidget
{
    /**
     * Fixed type means that the position of loader will be fixed at center of screen, 
     * but framed within a box
     */
    const FIXED      = 'fixed';
    /**
     * Fullscreen type means that the position of loader will be fixed at center of screen, 
     * but framed within the entire screen
     */
    const FULLSCREEN = 'fullscreen';
    /**
     * Relative type means that the position of loader will be floated according to position of parent div.
     * Parent div should use "position:relative"
     */
    const RELATIVE   = 'relative';
    /**
     * Absolute type means that the position of loader will be fixed at center of the parent div, 
     * but framed within the parent div
     */
    const ABSOLUTE   = 'absolute';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.sloader.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'sloader';    
    /**
     * Loading text; Default to font-awesome "fa-cog" with "fa-spin" if not set
     * @var string 
     */
    public $text;    
    /**
     * Loader type; Default to "FULLSCREEN"
     * 
     * @var string 
     */
    public $type = self::FULLSCREEN;    
    /**
     * Loader diplay mode; Default to "none" = hidden
     * 
     * @var string 
     */
    public $display = 'none';    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->text))
            $this->text = '<i class="fa fa-circle-o-notch fa-spin"></i>';
        $this->render('index');
    }
    
    protected function getContainerPosition()
    {
        switch ($this->type) {
            case self::RELATIVE:
                return 'relative';
            case self::FIXED:
                return 'inherit';
            case self::FULLSCREEN:
                return 'fixed';
            case self::ABSOLUTE:
                return 'absolute;background:transparent;';
            default:
                return 'undefined';
        }
    }
}