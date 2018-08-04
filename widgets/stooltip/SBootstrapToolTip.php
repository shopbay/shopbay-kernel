<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * SToolTip widget class file.
 * 
 * @author kwlok
 */
class SBootstrapToolTip extends SWidget
{
    const TOP    = 'top';
    const BOTTOM = 'bottom';
    const LEFT   = 'left';
    const RIGHT  = 'right';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.stooltip.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'stooltip';    
    /**
     * Tooltip symbol
     * @var string 
     */
    public $symbol;    
    /**
     * Tooltip content
     */
    public $content;    
    /**
     * Tooltip placement
     */
    public $placement = self::RIGHT;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->symbol))
            $this->symbol = '<i class="fa fa-info-circle"></i>';

        if (!isset($this->content))
            throw new CException(Sii::t('sii','Tooltip has no content'));
        
        $this->render('bootstrap_tooltip');
    }

}