<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SPageFilter
 *
 * @author kwlok
 */
class SPageFilter extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spagefilter.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spagefilter';
    /**
     * The page filter class form to be loaded
     * @var type 
     */
    public $formModel;
    /**
     * The quick menu (shortcut) displayed at the top of filter page
     * @var type 
     */
    public $quickMenu;
    /**
     * True to auto insert filter page as expandable menu for mobile mode
     * Since the actual filter page display as sidebar will be hidden under mobile mode
     * @var boolean 
     */
    public $enableMobile = true;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->formModel))
            throw new CException(__CLASS__.' form model is not found');
        if (!isset($this->formModel->actionUrl))
            throw new CException(__CLASS__.' form url is not found');
        
        $this->render('index',['model'=>$this->formModel]);
        
        if ($this->enableMobile){
            cs()->registerScript(__CLASS__,'preparemobilepagefilter()');
        }
    }

    public function getCssClass()
    {
        return $this->assetName;//use asset name as css class
    }
            
}
