<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.components.ShopPageTheme');
/**
 * Description of ShopThemeBehavior
 *
 * @author kwlok
 */
class ShopThemeBehavior extends CBehavior 
{
    private $_t;
    /**
     * Load theme and its settings
     * @param string $theme Theme name
     * @param string $style Theme style
     * @return Theme
     */
    public function loadTheme($theme,$style)
    {
        if (!isset($this->_t)){
            $model = Theme::model()->locateTheme($theme)->find();
            if ($model==null){
                $this->getOwner()->renderErrorPage(Sii::t('sii','Theme is not available.'));
                return;
            }
            $this->_t = $model;
            $this->setThemeLayout($theme);
            $this->registerThemeAssets($theme,$style,$model->styles);
            logTrace(__METHOD__,$this->_t->attributes);
        }
        return $this->_t;
    }
    /**
     * Init theme layout
     */
    public function setThemeLayout($theme)
    {
        Yii::app()->themeManager->themeClass = 'common.modules.shops.components.ShopPageTheme';
        $pageThemeObj = new ShopPageTheme($theme);
        Yii::app()->theme = $pageThemeObj;        
        Yii::app()->themeManager->basePath = $pageThemeObj->basePath;
        $this->getOwner()->layout = $this->getThemeView($theme, 'layouts');
        //logTrace(__METHOD__.' layout = '.$this->getOwner()->layout,$theme);
        //logTrace(__METHOD__.' basePath = '.Yii::app()->themeManager->basePath);        
        //logTrace(__METHOD__.' theme name = '.Yii::app()->theme->name);        
        //logTrace(__METHOD__.' theme layoutFile  = '.Yii::app()->theme->getLayoutFile($this->getOwner(),'shoplayout'));
        //logTrace(__METHOD__.' theme viewFile = '.Yii::app()->theme->getViewFile($this->getOwner(),'index'));        
        //logTrace(__METHOD__.' theme viewPath = '.Yii::app()->theme->viewPath);
    }    
    /**
     * Return theme view file; 
     * Search under active theme first, if not found, search into base theme, if still not found, go to default view file
     * @param type $viewName
     * @param type $folder
     * @param type $theme Target specific theme
     * @return string 
     */
    public function getThemeView($viewName,$folder='storefront',$theme=null)
    {        
        if (isset($theme))
            $this->setThemeLayout($theme);
        
        $themeViewFile = Yii::app()->theme->basePath.DIRECTORY_SEPARATOR.Yii::app()->theme->name.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$viewName.'.php';
        $defaultViewFile = Yii::app()->theme->basePath.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'storefront'.DIRECTORY_SEPARATOR.$viewName.'.php';
        //logTrace(__METHOD__.' $defaultViewFile = '.$defaultViewFile);
        if (file_exists($themeViewFile))
            return 'shopthemes.'.Yii::app()->theme->name.'.views.'.$folder.'.'.$viewName;
        elseif (file_exists($defaultViewFile))
            return 'shopthemes.default.views.'.$folder.'.'.$viewName;//default theme view file
        else 
            return 'application.modules.shops.views.'.$folder.'.'.$viewName;//if not found, fall back to controller view file, if any
    }    
    /**
     * Register theme assets 
     * @param string $theme Selected theme name
     * @param string $style Selected theme style
     * @param array $stylesConfig Selected theme styles config
     */
    public function registerThemeAssets($theme,$style,$stylesConfig=[])
    {
        //[1] Load system level theme assets
        $this->getOwner()->registerCommonFiles();
        $this->getOwner()->registerFormCssFile();
        Yii::app()->clientScript->registerCoreScript('jquery');
        $this->getOwner()->registerJui();
        $this->getOwner()->registerScriptFile('shops.assets.js','storefront.js');
        $this->getOwner()->registerChosen();
        $this->getOwner()->registerRating();  
        $this->getOwner()->registerInfiniteScroll();
        $this->getOwner()->registerPagerCssFile();
        $this->getOwner()->registerSearchScript(true);
        $this->getOwner()->registerBootstrapAssets();
        $this->getOwner()->registerMaterialIcons();
        $this->getOwner()->registerCartScript(true);
        $this->getOwner()->registerElevatezoom();//todo Picture viewer can be implemented as app or addon
        //$this->getOwner()->registerFancybox();//todo Picture viewer can be implemented as app or addon
        
        //[2] Load shop level theme assets
        //@see themes.models.ThemeStyle for $obj
        foreach ($stylesConfig as $id => $obj) {
            if ($id==Tii::STYLE_COMMON || $id==$style) {//load common and current theme style assets
                foreach ($obj->css as $css) {
                    $this->getOwner()->registerCssFile($obj->cssPathAlias,$css);
                }
                foreach ($obj->js as $js) {
                    $this->getOwner()->registerScriptFile($obj->scriptPathAlias,$js);
                }
            }
        }
    }
    /**
     * Return theme page data wrapper
     * @param type $shopModel
     * @param type $page
     * @param type $pagination Indicate if page has pagination
     * @return type
     */
    public function getThemePageDataWrapper($shopModel,$page,$pagination=false)
    {
        $pageobj = $this->getOwner()->loadPageObject($shopModel,$page);
        if ($pageobj->id==ShopPage::SEARCH){//regardless of theme
            $pageobj->setSearchByAjax(true);
            if ($pagination)
                return $pageobj->data['results'];
            else
                return $pageobj->data;
        }
        else if ($pageobj->id==ShopPage::PROMOTIONS && $pagination){//regardless of theme
            //need a div wrapper for jquery.ias container (pager) to work
            return CHtml::tag('div',['class'=>'promotion-wrapper'],$pageobj->getPage());
        }
        else {
            $wrapper = CHtml::tag('div',['id'=>$page,'class'=>'page'],$pageobj->getPage());
            if ($pagination)
                return $wrapper;//for yii ajaxupdate pagination
            else 
                return ['page'=>$wrapper];
        }
    }  
    /**
     * Load shop custom css at last to take precedence 
     */
    public function includeCustomCss(ShopViewPage $page)
    {
        if ($page->shopModel->getCustomCss($page->currentTheme,$page->currentStyle)!=null){
            if (userOnScope('shop')){
                $assetUrl= $page->constructUrl('custom/css');//@see ShopUrlManager for url parsing
            }
            else
                $assetUrl = Yii::app()->urlManager->createHostUrl('/shops/storefront/css');
            
            Yii::app()->clientScript->registerCssFile($assetUrl);
        }
    }
    //------------
    // BELOW SNIPPETS ARE TO BE USED BY MARKETPLACE
    // NOT APPLICABLE FOR SHOP OWN BRAND WEBSITE
    //--------------//
    /**
     * Get layout header file; Support different header link depends on scenario
     * Use case is for market place shop or 'free' shop but come with Shopbay footer.
     * @return string
     */
    public function getLayoutHeader()
    {
        return $this->getOwner()->renderPartial($this->getThemeView('_site_header'),[
                'shop'=>user()->getShop(),
                'showAppLogo'=>!userOnScope('shop'),
                'offSite'=>$this->getOwner()->onFacebook(),
            ],true);
    }
    /**
     * Get layout footer file
     * This is the extra footer on top of shop footer
     * Use case is for market place shop or 'free' shop but come with Shopbay footer.
     * @return string
     */
    public function getLayoutFooter()
    {
        if (userOnScope('shop') || $this->getOwner()->onFacebook())
            return '';//empty footer
        else
            return $this->getOwner()->renderPartial($this->getThemeView('_site_footer'),[],true);
    }    

}
