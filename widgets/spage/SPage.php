<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
Yii::import("common.widgets.spagelayout.SPageLayout");
Yii::import("common.widgets.spagesection.SPageSection");
Yii::import("common.widgets.spagemenu.SPageMenu");
Yii::import("common.widgets.sflash.SFlash");
Yii::import("common.widgets.sloader.SLoader");
/*
 * SPage widget class file.
 */
class SPage extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spage.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spage';
    /**
     * Enable page layout
     * @var array
     */
    public $layout = true;
    /**
     * Page layout id
     * @var array
     */
    public $layoutId;
    /**
     * Page side bars
     * @var array
     */
    public $sidebars;
    /**
     * Breadcrumbs
     * @var array
     */
    public $breadcrumbs = array();
    /**
     * Page menu
     * @var array
     */
    public $menu = array();
    /**
     * Flash key stored in session
     * @var mixed Flash key stored in String or Array 
     */
    public $flash;
    /**
     * Loader configuration; Default to "true".
     * 
     * @see SLoader
     * @var mixed If true, embed SLoader default config; If false, SLoader will not be embedded
     *            If array, it will be the the SLoader config params
     */
    public $loader = true;
    /**
     * Heading 
     * Expect either a string or an array
     * 
     * For array, expect containing two keys: 
     * array(
     *  'name' => 'Heading',
     *  'image' => '{image file}',
     *  'superscript' => 'superscript example' (optional)
     *  'subscript' => 'subscript example' (optional)
     *  'tag' => {mixed - refer to below} (optional)
     * )
     * 
     * Heading tag
     * Expect either a string or an array
     * 
     * For array, expect containing two keys: text and color
     * array(
     *  'text' => 'Tag',
     *  'color' => 'red' (optional)
     * )
     * 
     * @var string or array
     */
    public $heading;
    /**
     * Heading description
     * @var string
     */
    public $description;
    /**
     * Indicate there is a line break between heading and body; Default to true
     * @var boolean
     */
    public $linebreak = true;
    /**
     * Indicate there is a line break between body and section; Default to false
     * @var boolean
     */
    public $sectionLinebreak = false;
    /**
     * Page body
     * @var string
     */
    public $body;
    /**
     * The data array of sections to render. 
     * @var string
     */
    public $sections = array();    
    /**
     * Indicate if to include a dummy CSRF form to produce csrf token ; Default to false
     * @var boolean
     */
    public $csrfToken = false;    
    /**
     * Additional css class to existing pre-defined class "page"; Default to null
     * @var string
     */
    public $cssClass; 
    /**
     * Breadcrumbs home link; Default to url('welcome')
     * @var string
     */
    public $homeLink;    
    /**
     * If to hide home link
     * @var boolean
     */
    public $hideHomeLink = false;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->registerFontAwesome();
        $this->registerMaterialIcons();
        
        $this->_validateHeading();

        $this->render('index');
    }
    /**
     * Render loader
     * @return type
     */
    protected function renderLoader() 
    {
        if (is_array($this->loader))
            $this->widget('SLoader',$this->loader);
        else if ($this->loader)
            $this->widget('SLoader');
        else
            logTrace(__METHOD__.' skip embedding sloader');
    }    
    /**
     * Generate dummy form to produce csrf token 
     * Dummy url = url(csrf), but form name is fixed to 'csrf-form' for query used.
     * For example, used in tasks.js to extract csrf token value
     */
    protected function renderCSRFToken()
    {
        if ($this->csrfToken){
            echo CHtml::openTag('div',array('style'=>'display:none;')); 
            echo CHtml::form(url('csrf'),'post',array('id'=>'csrf-form')); 
            echo CHtml::endForm(); 
            echo CHtml::closeTag('div'); 
        }
        else
            logTrace(__METHOD__.' skip embedding csrf form');
    }    
    /**
     * Return heading tag text
     * @return type
     */
    protected function getHeadingTag() 
    {
        if (is_array($this->heading['tag'])){
            return $this->heading['tag']['text'];
        }            
        else {
            return $this->heading['tag'];
        }
    }
    /**
     * Return heading tag css style
     * @return type
     */
    protected function getHeadingTagStyle() 
    {
        if (is_array($this->heading['tag'])){
            if (array_key_exists('color', $this->heading['tag']))
                return 'style="background:'.$this->heading['tag']['color'].'"';
        }            
        return '';
    }
    
    protected function getPageLayoutColumns()
    {
        $columnsHtmlOptions = new CMap();

        if (isset($this->sidebars)) {

            $siderbarsContent = new CMap();
            $columnsWidth = new CMap();
            $columnsCssClass = new CMap();

            foreach ($this->sidebars as $sidebar => $value) {
                
                if ((isset($value['visible'])&&$value['visible'])||!isset($value['visible'])){
                    
                    $options = array();
                    if (isset($value['content']))
                        $siderbarsContent->add($sidebar,$value['content']);
                    if (isset($value['width'])){
                        $options = array_merge($options,array('cssClass'=>'width-p-'.rtrim($value['width'],'%')));
                        $columnsWidth->add($sidebar,$value['width']);
                    }
                    if (isset($value['cssClass'])){
                        $options = array_merge($options,array('cssClass'=>$value['cssClass']));
                        $columnsCssClass->add($sidebar,$value['cssClass']);
                    }
                    $columnsHtmlOptions->add($sidebar,$options);
                    
                }
            }

            $columns = array_merge($siderbarsContent->toArray(),array(SPageLayout::COLUMN_CENTER=>$this->render('common.widgets.spage.views._page', [], true)));
            if ($columnsWidth->count()>0){
                $columnsHtmlOptions->add(SPageLayout::COLUMN_CENTER,array('cssClass'=>'width-p-'.$this->_getColumnCenterWidth($columnsWidth->toArray())));
            }
            if ($columnsCssClass->count()>0)
                $columnsHtmlOptions->add(SPageLayout::COLUMN_CENTER,array('cssClass'=>$this->_getColumnCenterCssClass($columnsCssClass->toArray())));
        }
        else {
            $columns = array(SPageLayout::COLUMN_MAIN=>$this->render('common.widgets.spage.views._page', [], true));
        }
        
        return array('id'=>$this->layoutId,'columns'=>$columns,'columnsHtmlOptions'=>$columnsHtmlOptions->toArray());
    }

    private function _validateHeading() 
    {       
        if (is_array($this->heading)){
            if (!array_key_exists('name', $this->heading))
                    throw new CException(Sii::t('sii','SPage heading must have name'));            
            
            if (array_key_exists('tag', $this->heading)){
                if (is_array($this->heading['tag'])){
                    if (!array_key_exists('text', $this->heading['tag']))
                        throw new CException(Sii::t('sii','SPage heading tag must have text'));            
                }            
            }
        }
        
    }
    
    private function _getColumnCenterWidth($sidebarWidth=array())
    {
        $width = 100;//initial 100%
        $margin = 1;//offset 1% margin for border width
        $leftColumn = isset($sidebarWidth[SPageLayout::COLUMN_LEFT]) && substr($sidebarWidth[SPageLayout::COLUMN_LEFT], -1)=='%';
        $rightColumn = isset($sidebarWidth[SPageLayout::COLUMN_RIGHT]) && substr($sidebarWidth[SPageLayout::COLUMN_RIGHT], -1)=='%';

        if ($leftColumn && $rightColumn){
            $width -= substr($sidebarWidth[SPageLayout::COLUMN_LEFT], 0, -1);
            $width -= substr($sidebarWidth[SPageLayout::COLUMN_RIGHT], 0, -1);
        }
        elseif ($leftColumn){
            $width -= substr($sidebarWidth[SPageLayout::COLUMN_LEFT], 0, -1);
        }
        elseif ($rightColumn){
            $width -= substr($sidebarWidth[SPageLayout::COLUMN_RIGHT], 0, -1);
        }
        logTrace(__METHOD__.' '.$width);
        return $width - $margin;//return value in %
    }
    /**
     * NOTE: currently cannot support when both left and right sidebar columns are presented
     * @param type $columnsCssClass
     * @return type
     */
    private function _getColumnCenterCssClass($columnsCssClass=array())
    {
        if (isset($columnsCssClass[SPageLayout::COLUMN_LEFT]) && isset($columnsCssClass[SPageLayout::COLUMN_RIGHT]))
            return SPageLayout::getCenterWidthPercent ($columnsCssClass[SPageLayout::COLUMN_LEFT],$columnsCssClass[SPageLayout::COLUMN_RIGHT]);
        if (isset($columnsCssClass[SPageLayout::COLUMN_LEFT]))
            return SPageLayout::getCenterWidthPercent ($columnsCssClass[SPageLayout::COLUMN_LEFT]);
        if (isset($columnsCssClass[SPageLayout::COLUMN_RIGHT]))
            return SPageLayout::getCenterWidthPercent ($columnsCssClass[SPageLayout::COLUMN_RIGHT]);
    }

    protected function getBreadcrumbsHomeLink()
    {
        if (isset($this->homeLink))
            return $this->homeLink;
        else
            return url('welcome');
    }
    
    protected function getBreadcrumbsHomeLinkIcon()
    {
        if ($this->hideHomeLink)
            return false;
        else
            return l('<i class="fa fa-home"></i>', $this->getBreadcrumbsHomeLink());
    }    
}

