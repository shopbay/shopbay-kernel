<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.sgridlayout.widgets.*');
Yii::import('common.modules.shops.components.ShopPage');
Yii::import('shopwidgets.shoplayout.ShopGridLayout');
/**
 * Description of PageLayoutEditor
 *
 * @author kwlok
 */
class PageLayoutEditor extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.modules.pages.widgets.pagelayouteditor.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'pagelayouteditor';
    /**
     * The current layout theme 
     * @var Theme 
     */
    public $theme;
    /**
     * The page that is being edit
     * @var ShopPage 
     */
    public $page;
    /**
     * The page locale
     * @var string 
     */
    public $locale;
    /**
     * The page layout container
     * @var SGridLayout
     */
    protected $_layout;
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        parent::init();
        $this->registerBootstrapAssets();
        $this->registerFontAwesome();
        $this->registerMediaGalleryAssets();
        $this->registerCkeditor('page');
    }    
    /**
     * Run widget
     * @throws CException
     */
    public function run($return=false)
    {
        if (!isset($this->theme))
            throw new CException(__CLASS__.' Error: Theme is not set.');
        if (!isset($this->page))
            throw new CException(__CLASS__.' Error: Page is not set.');

        if ($return)
            return $this->render('index',[],true);
        else
            $this->render('index');
    }
    /**
     * Under editor mode, use back controller layout, and not the actual shop theme layout
     * This method has to be run at last after all layout config is loaded
     * @see index.php last line of code
     */
    public function setControllerLayout()
    {
        $this->controller->layout = Yii::app()->ctrlManager->authenticatedLayout;
    }
    
    public function getLayout()
    {
        if (!isset($this->_layout)){
            if ($this->page->pageOwner instanceof Shop){
                $this->_layout = new ShopGridLayout($this->controller);
                $layoutCssClass = 'shop-layout';
            }
            else {
                return null;//@todo add more supported page owner type
            }
            
            $config = [
                'container'=>'canvas container-fluid '.$layoutCssClass.' '.$this->page->currentTheme.' '.$this->page->currentStyle,
                'theme'=>$this->theme,
                'page'=>$this->page,
                'locale'=>$this->locale,
                'modal'=>true,
                'offCanvasMenu'=>false,//todo To support off canvas menu edit
            ];
            /* load config */
            foreach ($config as $key => $value) {
                $this->_layout->$key = $value;
            }
        }
        return $this->_layout;
    }
    
    public function getWidget($class,$config=[])
    {
       return new $class($this->layout,$this->controller,$config);        
    }
    /**
     * List all the themes that have been installed for page owner
     * @return string 
     */
    public function renderThemesOptions($themes)
    {
        $data = [];
        $htmlOptions = [];
        foreach ($themes as $pageTheme) {//this is pointing to ShopTheme
            $themeModel = $pageTheme->model;
            $data[$pageTheme->id] = Sii::t('sii','{theme} / {style}',[
                '{theme}'=>$themeModel->displayLanguageValue('name',$this->locale),
                '{style}'=>$themeModel->getStyle($pageTheme->style)->getName($this->locale),
            ]);
            $htmlOptions['options'][$pageTheme->id] = ['data-url'=>$this->page->pageModel->layoutUrl.'?'.Page::previewUriParams($pageTheme->themeOwner, $pageTheme->theme, $pageTheme->style)];
        }
        return CHtml::listOptions($this->layout->pageTheme->id, $data, $htmlOptions);
    }    
    
    /**
     * List all the pages that can be edited
     * @return string 
     */
    public function renderPageOptions($currentPage)
    {
        $pages = Page::model()->locateOwner($currentPage->pageOwner)->all()->findAll();
        $data = [];
        $htmlOptions = [];
        foreach ($pages as $page) {//this is pointing to ShopTheme
            $data[$page->id] = $page->localeName($this->locale);
            $htmlOptions['options'][$page->id] = ['data-url'=>$page->layoutUrl];
        }
        return CHtml::listOptions($currentPage->pageModel->id, $data, $htmlOptions);
    }    
    
    public function getDisclaimer()
    {
        $message = Sii::t('sii','Please design with care. {app} does not provide support for any UI issues caused by customizations.',['{app}'=>app()->name]);
        $message .= ' '.Sii::t('sii','As a platform {app} often upgrades system including themes, we do not provide support either for any issues caused by upgrade.',['{app}'=>app()->name]);
        $message .= ' '.Sii::t('sii','We suggest you do page preview to check edited page layout is working fine whenever you have made changes or receive themes upgrade messages from us.');
        return $message;
    }    
    
    public function getResetNotice()
    {
        $message = Sii::t('sii','You can reset this page layout to default theme layout. However, please note that you will lose all your layout customizations for this page under this theme.');
        $message .= ' '.CHtml::link(Sii::t('sii','Click here to reset page.'), 'javascript:void(0);',  [
                            'submit'=>$this->page->pageModel->getLayoutResetUrl($this->page->currentTheme),
                            'onclick'=>'$(\'.page-loader\').show();',
                            'confirm'=>Sii::t('sii','You will lose all this page layout customizations. Are you sure you want to reset this page?')
                        ]);
        return $message;
    }    
    /**
     * Check if page is resetable
     * @return boolean
     */
    public function isPageResetable()
    {
        if ($this->page->pageOwner instanceof Shop){
            return ShopPage::isCustomPage($this->page->pageModel->layoutMapId);
        }
        else {
            return false;
        }
    }
}
