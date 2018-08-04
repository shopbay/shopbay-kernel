<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SOffCanvasMenu
 *
 * @author kwlok
 */
class SOffCanvasMenu extends SWidget
{
    CONST LEFT    = 'left';
    CONST RIGHT   = 'right';
    
    CONST OVERLAY = 'overlay';
    CONST PUSH    = 'push';
    CONST FULL    = 'full';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.soffcanvasmenu.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'soffcanvasmenu';
    /**
     * The page element id (cannot change)
     * @var string 
     */
    protected $pageId = 'canvas_page_content';
    /**
     * The page content; Leave blank if only want the off canvas to sit on top of the page
     * @var string 
     */
    public $pageContent;
    /**
     * @var array Menu elements
     * @see OffCanvasMenu
     */
    public $menus = [];
    /**
     * @var boolean If to auto include menu openers
     * @see OffCanvasMenu
     */
    public $autoMenuOpeners = true;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $temp = [];//temp menu holder
        foreach($this->menus as $menu){
            $temp[] = $this->prepareMenu($menu);
        }
        $this->menus = $temp;//transform menu data to menu object 
        $this->render('index');
    }    
    /**
     * Examine menu data, and populate default value if not set
     * @see OffCanvasMenu
     * @param array
     */
    public function prepareMenu($data)
    {
        if (!is_array($data))
            throw new CException(__CLASS__." Menu must be in an array to contain data");

        return new OffCanvasMenu($data);
    }    
    /**
     * Get the open element with proper onclick event binding.
     * @return string
     */
    public function renderMenuOpeners()
    {
        $openers = CHtml::openTag('ul');
        if (!empty($this->menus)){
            foreach ($this->menus as $menu) {
                $openers .= CHtml::tag('li', [], $menu->openElement($this->pageId));
            }
        }
        $openers .= CHtml::closeTag('ul');
        return $openers;
    }
}

class OffCanvasMenu 
{
    /**
     * The menu element id (cannot change)
     * @var string 
     */
    public $id = 'soffcanvas_menu';
    /**
     * Off canvas menu open method (how menu is opened)
     * @var string 
     */
    public $openMethod = SOffCanvasMenu::PUSH;
    /**
     * The open side; Default to 'left'
     * @var string 
     */
    public $openSide = 'left'; 
    /**
     * Define any element to open the off canvas
     * The element must contain a {onclick} script placeholder for the onclick "open" event
     * The element must contain a {text} placeholder for the open text
     * @var string 
     */
    public $openElement = '<span onclick="{onclick}">{text}</span>';
    /**
     * The open element text
     * @var string 
     */
    public $openText = 'Open';
    /**
     * The menu open width
     * @var string 
     */
    public $openWidth = '250px';
    /**
     * The menu heading
     * @var string 
     */
    public $heading;
    /**
     * The menu content
     * @var string 
     */
    public $content;    
    /**
     * Constructor
     * @param type $config
     */
    public function __construct($config=[]) 
    {
        foreach ($config as $field => $value) {
            if (property_exists($this,$field))
                $this->$field = $value;
        }
        //Validation
        foreach (['openMethod','openSide','content'] as $field) {
            if (strlen($this->$field)==0)
                throw new CException(__CLASS__." $field cannot be blank");
        }
    }
    /**
     * Auto get the menu open script according to type
     * @return string
     */
    public function openScript($pageId)
    {
        switch ($this->openMethod) {
            case SOffCanvasMenu::OVERLAY:
                return "openoffcanvasmenu_overlay('$this->id','$this->openWidth')";
            case SOffCanvasMenu::FULL:
                return "openoffcanvasmenu_full('$this->id')";
            case SOffCanvasMenu::PUSH://default is push
            default:
                return "openoffcanvasmenu_push('$this->id','$pageId','$this->openWidth')";
        }
    }
    /**
     * Auto get the menu close script according to type
     * @return string
     */
    public function closeScript($pageId)
    {
        switch ($this->openMethod) {
            case SOffCanvasMenu::OVERLAY:
                return "closeoffcanvasmenu_overlay('$this->id')";
            case SOffCanvasMenu::FULL:
                return "closeoffcanvasmenu_full('$this->id')";
            case SOffCanvasMenu::PUSH://default is push
            default:
                return "closeoffcanvasmenu_push('$this->id','$pageId')";
        }
    }  
    /**
     * Get the open element with proper onclick event binding.
     * @return string
     */
    public function openElement($pageId)
    {
        if (isset($this->openElement)){
            $element = str_replace('{onclick}', $this->openScript($pageId), $this->openElement);
            return str_replace('{text}', $this->openText, $element);
        }
        else
            return null;
    }    
}