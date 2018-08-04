<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.controllers.PreviewControllerTrait');
/**
 * Description of ShopPageControllerTrait
 *
 * @author kwlok
 */
trait ShopPageControllerTrait 
{
    use PreviewControllerTrait;
    /**
     * Behaviors required to run as a storefront controller
     */
    public function storefrontBehaviors()
    {
        return [
            'shoppagebehavior' => [
                'class'=>'common.modules.shops.behaviors.ShopPageBehavior',
            ],            
            'shopthemebehavior' => [
                'class'=>'common.modules.shops.behaviors.ShopThemeBehavior',
            ],            
            'searchbehavior' => [
                'class'=>'common.modules.search.behaviors.SearchControllerBehavior',
                'targets'=>['SearchProduct'],
                'filter'=>['callback'=>'getSearchFilter'],
                'loadSearchbar'=>false,
                'placeholder'=>Sii::t('sii','Search products'),
                'onsearch'=>['callback'=>'getOnSearchScript'],
                'paginationRoute'=>['callback'=>'getSearchPageRoute'],
                'searchInput'=>'catalogsearch_q',
            ],
            'cartdatabehavior' => [
                'class'=>'common.modules.carts.behaviors.CartDataBehavior',
                'cartUrl'=>['callback'=>'getShoppingCartUrl'],
            ],        
            'shopassetsbehavior' => [
                'class'=>'common.modules.shops.behaviors.ShopAssetsBehavior',
            ],            
        ];
    }
    /**
     * A callback method to get shopping cart url
     * @see SearchControllerBehavior
     */
    public function getShoppingCartUrl()
    {
        return user()->getCartUrl();
    }        
    /**
     * A callback method to get search filter
     * @see SearchControllerBehavior
     */
    public function getSearchFilter()
    {
        return [
            'status'=>SearchFilter::ACTIVE,
            'shop_id'=>user()->getShop(),
        ];
    }
    /**
     * A default callback method to get onsearch script
     * @todo Review if still need this as all search are self-contained inside widget e.g. ssearch etc
     * @see SearchControllerBehavior
     */
    public function getOnSearchScript()
    {
        if ($this->onFacebook())
            return 'shopcatalogsearch("newwindow")';//param to indicate open new window
        else
            return 'shopcatalogsearch()';//Note: inside handles mobile search scenario
    }        
    /**
     * Callback to get search pagination route (for pagination use)
     */
    public function getSearchPageRoute()
    {
        $page = $this->createViewPage($this->getCurrentPage(), $this->getCurrentShop());
        return $page->getUrl($this->getCurrentPage(),['goto'=>'']);//add query param ?goto='' a ugly hack for pagination
    }    
    /**
     * Portal index page handler
     * 
     * @param mixed $model
     * @param type $modalView
     * @return type
     */    
    public function renderIndexPage($model,$modalView=null)
    {
        $page = $this->loadPageObject($model,$this->getCurrentPage());
        $this->renderShopPage($page, $modalView);
    }
    /**
     * Portal html page 
     * 
     * @param mixed $model
     * @param string $content
     * @param string $cssClass page css class
     * @return type
     */    
    public function renderHtmlPage($model,$content,$cssClass=null)
    {
        $this->setCurrentPage(ShopPage::HTML);
        $page = $this->loadPageObject($model,ShopPage::HTML);
        $page->htmlPage = CHtml::tag('div', ['class'=>isset($cssClass)?$cssClass:'html-page'], $content);
        $this->renderShopPage($page);        
    }    
    /**
     * Portal html page referenced by view file
     * use case: error 404 file, or any random html content
     * @return type
     */    
    public function renderHtmlPageByFile($model,$viewFile,$viewData)
    {
        $this->setThemeLayout($model->getTheme());
        $this->renderHtmlPage($model, $this->renderPartial($this->getThemeView($viewFile),$viewData,true));
    }      
    /**
     * The ultimate storefront index page entry 
     * @param ShopViewPage $page
     * @param mixed $modalView
     * @return type
     */    
    public function renderShopPage(ShopViewPage $page,$modalView=null)
    {
        $this->_renderInternal($page, $modalView);
    }        
    /**
     * The internal storefront index page handler
     * @param ShopViewPage $page
     * @param string $themeName
     * @param mixed $modalView
     * @return type
     */    
    protected function _renderInternal(ShopViewPage $page,$modalView=null)
    {
        if (Yii::getPathOfAlias('shopthemes')==null)//set shop themes path alias again
            $this->setShopAssetsPathAlias();
        $theme = $this->loadTheme($page->currentTheme,$page->currentStyle);        
        user()->setCartUrl($page->getUrl(ShopPage::CART));
        $this->render($this->getThemeView('index'),['page'=>$page,'theme'=>$theme,'modalView'=>$modalView]);
        Yii::app()->end();
    }        
    /**
     * Render modal page according to model type 
     * @param type $page
     * @param type $model
     * @return type
     */
    protected function renderModalPage($page,$model)
    {
        if ($page==ProductPage::MODAL)
            return $this->createViewPage($page, $model,'ProductPage',true)->getPage();
        elseif ($page==CampaignPage::MODAL)
            return $this->createViewPage($page, $model,'CampaignPage',true)->getPage();
        else
            return null;
    }
    /**
     * A gateway method to create view page
     * @param type $page
     * @param type $model
     * @param type $modelClass
     * @param type $trackVisit
     */
    public function createViewPage($page,$model,$modelClass='ShopPage',$trackVisit=false)
    {
        $preview = $this->checkPreview();
        $pageObj = new $modelClass($page, $model, $this, $preview ? false : $trackVisit);//follow controller preview status; If preview, do not track visit
        $pageObj->https = isset($_GET['https']) ? $_GET['https'] : request()->isSecureConnection;
        $pageObj->currentTheme = $pageObj->shopModel->getTheme();//Default to current selected shop theme
        $pageObj->currentStyle = $pageObj->shopModel->getThemeStyle();//Default to current selected shop theme style
        if ($preview){//attempt to change theme
            $pageObj->switchPreviewTheme($this->queryParams['theme'],$this->queryParams['style'],$this->queryParams['preview']);
        }
        return $pageObj;
    }  
    /**
     * A generic error page display using current shop theme
     */
    public function renderErrorPage($message)
    {
        $errorPage = new ShopPage(ShopPage::HTML, $this->getCurrentShop(), $this, false);
        $errorPage->currentTheme = $this->getCurrentShop()->getTheme();
        $errorPage->currentStyle = $this->getCurrentShop()->getThemeStyle();
        $errorPage->htmlPage = $this->renderPartial($this->getThemeView('404'),['message'=>$message],true);
        $this->_renderInternal($errorPage);
        Yii::app()->end();
    }
    /**
     * Validate if model  is valid for display
     * 
     * @param type $model
     * @return CModel|null
     */
    protected function isValidModel($model) 
    {
        if ($model instanceof Shop)
            return $this->isValidShop($model) ? $model : null;
        elseif ($model instanceof Product || $model instanceof Campaign)
            return $this->isValidShop($model->shop) ? $model : null;
        elseif ($model instanceof Page)
            return $this->isValidShop($model->owner) ? $model : null;
        else 
            return null;
    }
    /**
     * Validate if shop is valid for shop rendering
     * Only online shop with subscription is considered valid, except when run in preview mode
     * 
     * @param type $shop
     * @return boolean
     */
    protected function isValidShop($shop)
    {
        if (!$shop instanceof Shop)
            return false;
        
        if ($this->onPreview())
            return true;//for Preview, always true regardless of shop is online or has subscription
        elseif ($shop->hasSubscription && $shop->online())
            return true;
        else
            return false;
    }    
    /**
     * Check if shop is on custom domain
     * @return boolean
     */
    protected function onCustomDomain()
    {
        return $_SERVER['HTTP_HOST']!=Yii::app()->urlManager->hostDomain;
    }   
    
    protected function getProductModel($slug,$active=true)
    {
        $finder = Product::model()->withSlug($slug);
        if ($active)
            $finder = $finder->active();
        $model = $finder->find(); 
        return $this->isValidModel($model);
    }
    
    protected function getPageModel($shop,$slug,$active=true)
    {
        $finder = Page::model()->locateOwner($shop)->withSlug($slug);
        if ($active)
            $finder = $finder->active();
        $model = $finder->find(); 
        return $this->isValidModel($model);
    }    
    
    protected function getCampaignModel($campaignKey)
    {
        $model = $this->loadModel(base64_decode($campaignKey),get_class(CampaignBga::model()),true);
        return $this->isValidModel($model);
    }    
}
