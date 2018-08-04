<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridFixtureBlock
 * A fixture block load html based on predefined widget (external source file) and content cannot be changed
 * Fixtures includes:
 * [1] Direct view file rendering: 
 * Format = 'v:<view_file>'
 * Note: ShopPage object $page  will be passed into the view file
 * 
 * [2] A inbuilt widget with getter method at ShopWidgets
 * 
 * @author kwlok
 */
class SGridFixtureBlock extends SGridColumn
{
    public static $viewWidget = 'v:';//direct view rendering method
    
    public $type = SGridLayout::FIXTURE_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 4;//default width
    /**
     * The fixtures reference; When set, the whole html content is always follow widget design (and content) and not editable 
     * @var array
     */
    public $fixtures = [];
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        $this->renderBlock();//render block content first
        return parent::render();
    }   
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'fixtures'=>$this->fixtures,
            ]);
            $this->content = $widget->blockContent;
        }
        else {
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._fixtureblock',['element'=>$this],true);
        }
    }     
    /**
     * Render fixtures
     * @return string
     */
    public function renderFixtures()
    {
        $html = '';
        foreach ($this->fixtures as $fixture) {
            Yii::import('common.modules.themes.widgets.themegridlayout.ThemeGridLayout');
            if ($this->isViewWidget($fixture)){
                $viewFile = substr($fixture, 2);//discard first 2 chars "v:"
                //logTrace(__METHOD__.' $viewWidget',$viewFile);
                $html .= $this->layout->getWidget(ThemeGridLayout::$widgetView.$viewFile);
            }
            else
                $html .= $this->layout->getWidget(ThemeGridLayout::$widgetProperty.$fixture);
        }
        return $html;
    }      
    /**
     * Check if config value is pointing to a view widget 
     * @param string $value
     */
    protected function isViewWidget($value)
    {
        if (is_scalar($value)){
            return substr($value, 0, 2)==static::$viewWidget;
        }
        return false;
    }
    
}
