<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
Yii::import("common.widgets.sloader.SLoader");
/**
 * Description of SModal
 *
 * @author kwlok
 */
class SModal extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.smodal.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'smodal';    
    /**
     * string the cssStyle of modal dialog
     */
    public $cssStyle;    
    /**
     * Modal container id (a wrapper of whole modal)
     * @var string
     */
    public $container;    
    /**
     * Modal content
     * @var string
     */
    public $content;    
    /**
     * Modal close button display
     * @var boolean
     */
    public $closeButton = true;    
    /**
     * Modal close sript
     * @var string
     */
    public $closeScript;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->container))
            throw new CException(Sii::t('sii','Modal container not defined'));
        $this->registerFontAwesome();
        $this->render('index');
    }
    /**
     * Get css style format
     * @return string
     */
    public function getCssStyle()
    {
        if (isset($this->cssStyle))
            return $this->cssStyle.';';
        else
            return '';
    }
    public function getCloseScript()
    {
        if (isset($this->closeScript))
            return 'javascript:'.$this->closeScript;
        else
            return 'javascript:closesmodal(\'#'.$this->container.'\');';
    }
}