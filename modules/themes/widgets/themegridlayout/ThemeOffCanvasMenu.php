<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.soffcanvasmenu.SOffCanvasMenu');
/**
 * Description of ThemeOffCanvasMenu
 *
 * @author kwlok
 */
trait ThemeOffCanvasMenu 
{
    /**
     * boolean Enable off canvas menu; Set to false to disable
     */
    public $offCanvasMenu = true;
    /**
     * string the shopping cart config (quickview)
     */
    public $cartMenu = [];
    /**
     * string the shop nav menu (mobile view)
     */
    public $shopMenu = [];
    /**
     * string the shop account login menu (mobile view )
     */
    public $loginMenu = [];
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->containerViewFile = 'common.modules.themes.widgets.themegridlayout.views.offcanvas_index';
        if ($this->offCanvasMenu){
            $this->containerViewData = [
                'offCanvasMenus'=>$this->offCanvasMenus,
            ];
        }
        parent::run();
    }      
    /**
     * Array off canvas menus to be included
     * @return type
     */
    public function getOffCanvasMenus()
    {
        $menus = [];
        if (isset($this->cartMenu['content'])){
            $menus[] = [
                'id'=>'offcanvas_cart_menu',
                'openMethod'=>isset($this->cartMenu['openMethod'])? $this->cartMenu['openMethod'] : SOffCanvasMenu::OVERLAY,
                'openSide'=>isset($this->cartMenu['openSide'])? $this->cartMenu['openSide'] : SOffCanvasMenu::LEFT,
                'heading'=>Sii::t('sii','Cart'),
                'content'=>$this->cartMenu['content'],
            ];
        }
        if (isset($this->shopMenu['content'])){
            $menus[] = [
                'id'=>'offcanvas_shop_menu',
                'openMethod'=>isset($this->shopMenu['openMethod'])? $this->shopMenu['openMethod'] : SOffCanvasMenu::PUSH,
                'openSide'=>isset($this->shopMenu['openSide'])? $this->shopMenu['openSide'] : SOffCanvasMenu::LEFT,
                'heading'=>Sii::t('sii','Menu'),
                'content'=>$this->shopMenu['content'],
            ];
        }
        if (isset($this->loginMenu['content'])){
            $menus[] = [
                'id'=>'offcanvas_login_menu',
                'openMethod'=>isset($this->loginMenu['openMethod'])? $this->loginMenu['openMethod'] : SOffCanvasMenu::PUSH,
                'openSide'=>isset($this->loginMenu['openSide'])? $this->loginMenu['openSide'] : SOffCanvasMenu::LEFT,
                'heading'=>Sii::t('sii','My Account'),
                'content'=>$this->loginMenu['content'],
            ];
        }
        return $menus;
    }
}
