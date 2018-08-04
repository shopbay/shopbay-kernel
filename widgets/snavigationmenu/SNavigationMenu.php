<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SNavigationMenu
 *
 * @author kwlok
 */
class SNavigationMenu  extends SWidget
{
   /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.snavigationmenu.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'snavigationmenu';    
    /**
     * boolean indicate if auto load pikabu lib
     */
    public $loadPikabu = true;    
    /**
     * List of menu items
     * @see CMenu::$items
     */
    public $menu;   
    /**
     * Nav menu default css class 
     * @see CMenu::$items
     */
    public $menuCssClass = 'nav-menu';   
    /**
     * Nav menu items default css class
     * @see CMenu::$items
     */
    public $itemsCssClass = 'nav-menuitems';   
    /**
     * On opened script
     */
    public $onOpenedScript = 'opennavmenuleft';   
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        parent::init();
        //load pikabu
        if ($this->loadPikabu){
            Yii::import('common.extensions.pikabu.Pikabu');
            $pikabu = new Pikabu();
            $pikabu->publishAssets();
        }
    }
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->menu) && !is_array($this->menu))
            throw new CException(Sii::t('sii','SNavigationMenu menu items not set'));
        
        $this->render('index');
    }
    
    public function getPikabuDataTags()
    {
        if ($this->loadPikabu)
            return 'data-pikabu="'.$this->loadPikabu.'" data-on-opened="'.$this->onOpenedScript.'"';
        else
            return null;
    }
}
