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
class SToolTip extends SWidget
{
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
     * Auto calculate top position for left/right position
     */
    public $autoTop = true;    
    /**
     * Tooltip config
     * array(
     *    'height'=>'65px',
     *    'width'=>'200px',
     *    'position'=>'right',
     * )
     * 
     * @var array 
     */
    public $config = array();    
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
        
        $this->render('index');
    }
    const POSITION_TOP    = 'top';
    const POSITION_BOTTOM = 'bottom';
    const POSITION_LEFT   = 'left';
    const POSITION_RIGHT  = 'right';
    /**
     * Return tooltip position
     * @return type
     */
    public function getPosition() 
    {
        if (isset($this->config['position']))
            return $this->config['position'];
        else
            return self::POSITION_RIGHT;//return default position if not set
    }    
    const WIDTH_100 = 'tooltip_width100';
    const WIDTH_200 = 'tooltip_width200';
    const WIDTH_300 = 'tooltip_width300';
    /**
     * Return tooltip position
     * @return type
     */
    public function getCssClass() 
    {
        if (isset($this->config['cssClass']))
            return $this->config['cssClass'];
        else
            return self::WIDTH_300;//return default css class
    }    
    
}