<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ProductPage");
Yii::import('common.modules.shops.components.FacebookShopTrait');
/**
 * Description of ShopPageBehavior
 *
 * @author kwlok
 */
class ShopPageBehavior extends CBehavior 
{
    use FacebookShopTrait;
    
    private $_s;//current shop instance
    private $_pageObj;
    private $_bgaDataProvider;
    /**
    * @var string The view mode the owner is running in. Defaults to "live"
    */
    public $viewMode = ShopPage::LIVE;
    /**
     * Load a particular page
     * @param type $model
     * @param type $page
     * @return string
     */
    public function loadPage($model,$page)
    {
        return $this->loadPageObject($model, $page)->getPage();
    } 
    /**
     * Load a particular page object
     * 
     * @param type $model
     * @param type $page
     * @return string
     */
    public function loadPageObject($model,$page)
    {
        if (!isset($this->_pageObj)){
            if ($model instanceof Product && $page==ShopPage::PRODUCT)
                $this->_pageObj = $this->getOwner()->createViewPage($page,$model,'ProductPage',true);
            else if ($model instanceof CampaignBga && $page==ShopPage::CAMPAIGN)
                $this->_pageObj = $this->getOwner()->createViewPage($page,$model,'CampaignPage',true);
            elseif ($model instanceof Page && $page==ShopPage::CUSTOM){
                $this->_pageObj = $this->getOwner()->createViewPage($page,$model,'CustomPage',true);
                $this->setPageSettings($this->_pageObj);
            }
            else {
                $this->_pageObj = $this->getOwner()->createViewPage($page,$model,'ShopPage',true);
                $this->setPageSettings($this->_pageObj);
                if ($page==ShopPage::SEARCH && isset($_GET['goto'])){//refer to SearchControllerBehavior for pagination
                    $param = $this->_parseSearchGoto($_GET['goto']);
                    $this->_pageObj->setSearchPageNum($param->pageNum);
                    $this->_pageObj->setSearchQuery($param->query);
                }
            }
            $this->setPageMetaTags($this->_pageObj,user()->getLocale());
            user()->setShop($this->_pageObj->shopModel->id);//this internally sets SActiveSession::SHOP_VISIT
        }
        return $this->_pageObj;
    } 
    /**
     * set common page settings
     */
    protected function setPageSettings($pageobj)
    {
        if (isset($_GET['sort_by']))
            $pageobj->sortby = $_GET['sort_by'];
        
        if ($pageobj->id==ShopPage::SEARCH && isset($_GET['query'])){
            $pageobj->setSearchQuery($_GET['query']);
        }
        if ($pageobj->id==ShopPage::CATEGORY && isset($_GET['subpage'])){
            $browseValue = $_GET['subpage'];
            if (isset($_GET['subsubpage']))
                $browseValue .= CategorySub::KEY_SEPARATOR.$_GET['subsubpage'];
            $pageobj->setFilter([ShopBrowseMenu::CATEGORY=>$browseValue]);
            logTrace(__METHOD__.' page filter data', $pageobj->filter->data);
        }
        if ($pageobj->id==ShopPage::BRAND){
            $browseValue = $_GET['subpage'];
            $pageobj->setFilter([ShopBrowseMenu::BRAND=>$browseValue]);
            logTrace(__METHOD__.' page filter data', $pageobj->filter->data);
        }
    }
    /**
     * Set page meta tags for SEO
     * @param ShopViewPage $page
     */
    public function setPageMetaTags($page,$locale)
    {
        $this->getOwner()->enablePageTitleSuffix = false;
        $seoTitle = $page->getPageSeoTitle($locale);
        $shopName = $page->shopModel->localeName(user()->getLocale());
        
        if ($page->id==$this->getDefaultPage())
            $this->getOwner()->setPageTitle($shopName);//for default page, no suffix
        else
            $this->getOwner()->setPageTitle($seoTitle.' | '.$shopName);
        
        $this->getOwner()->metaDescription = $page->getPageSeoDesc($locale);
        $this->getOwner()->metaKeywords = $page->getPageSeoKeywords();
    }
    /**
     * Get default page (works on certain theme only)
     */
    public function getDefaultPage()
    {
        return ShopPage::defaultPage();
    }     
    /**
     * Set current page visited by user (works on certain theme only)
     */
    public function setCurrentPage($page=null)
    {
        SActiveSession::set(SActiveSession::SHOP_PAGE,isset($page)?$page:$this->getOwner()->getDefaultPage());
    }      
    /**
     * Get current page visited by user (works on certain theme only)
     */
    public function getCurrentPage()
    {
        $page = SActiveSession::get(SActiveSession::SHOP_PAGE);
        return isset($page)?$page:$this->getOwner()->getDefaultPage();
    }            
    /*
     * If to use secure connection for page; 
     * Default to false; This will be set to "true" if request scheme is https or request is coming from facebook shop
     */
    public function useSecurePage()
    {
        return $this->getOwner()->onFacebook() || request()->isSecureConnection;
    }  
    /**
     * Get current visited shop model
     * @return type
     */
    public function getCurrentShop()
    {
        if (!isset($this->_s))
            //cannot directly use user()->getShop() as preview from merchant app will not work
            $this->_s = $this->getOwner()->loadModel(SActiveSession::get(SActiveSession::SHOP_VISIT),'Shop');
        return $this->_s;
    }   
    /**
     * Get navigation menu
     */
    public function getNavigationMenu($shopModel,$page)
    {
        $nav = new ShopNavigation('navmenu', $shopModel, null);//controller set to null
        if ($page instanceof ShopViewPage)
            $nav->page = $page;
        else //to remove below when old themes are cleaned up!
            $nav->page = $this->loadPageObject($shopModel, $this->getCurrentPage());
        return $nav;
    }    
    /**
     * Load one particular subpage
     * @param type $model
     * @return type
     */
    public function getSubPageData($model)
    {
        if ($model instanceof Product || $model instanceof Campaign || $model instanceof Page){
            $shopModel = $model->shop;
            $pageModel = $model;
        }
        else {
            $shopModel = $model;
            $pageModel = $model;
        }        
        return ['shopModel'=>$shopModel,'subpage'=>$this->loadPage($pageModel,$this->getCurrentPage())];
    }    
    /**
     * Parse to get search page number (when search result has multiple pages)
     * @param type $goto comes in format: e.g. goto=/query/test/shop_name/Fashion-Street/page/2
     * @return type
     */
    private function _parseSearchGoto($goto)
    {
        $param = new stdClass();
        $a = substr($goto,strpos($goto, 'page/'));
        $b = explode('/', $a);
        logTrace(__METHOD__.' search_page "page/" captured: ',$b);
        //[1] scenario 1: e.g. goto=/ajax/search_results/goto//query/é©/shop_name/Fashion-Street/page/3/shop_name/Fashion-Street/page/2
        //[2] scenario 2: goto=/query/test/shop_name/Fashion-Street/page/2
        //Always capture the last one: array (
        //  0 => 'page',
        //  1 => '3',
        //  2 => 'shop_name',
        //  3 => 'Fashion-Street',
        //  4 => 'page',
        //  5 => '2')
        $param->pageNum = $b[count($b)-1];//capture last index
        $x = substr($goto,strpos($goto, 'query/'));
        $y = explode('/', $x);
        $param->query = $y[1];
        logTrace(__METHOD__.' search_page goto='.$goto,$param);
        return $param;
    }    
    /**
     * Set view mode
     * @param type $mode
     */
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    } 
    /**
     * Check if owner is on PREVIEW view mode
     * @return boolean
     */
    public function onPreview()
    {
        return $this->viewMode==ShopPage::PREVIEW;
    }
    /**
     * Check if owner is on LIVE view mode
     * @param type $viewMode
     * @return type
     */
    public function onLive()
    {
        return $this->viewMode==ShopPage::LIVE;
    } 
    /**
     * Load product view script
     * @param Product $product
     */
    public function loadProductViewScript($product,$page)
    {
        $productPage = $page->transformPage('ProductPage',$product);//transform to product page to be able to construct url
        if ($product->shop->displayProductOverlay())
            return $this->loadProductModalScript($productPage);
        else 
            return 'productview(\''.$productPage->productUrl.'\');';
    }
    /**
     * Load product modal view script
     * Refer to storefront.js modalview(). There are logic inside to turn on buttons
     * @param ProductPage $productPage 
     */
    public function loadProductModalScript($productPage)
    {
        if ($productPage->onPreview){
            return 'productmodalview(\''.$productPage->productUrl.'\',\''.$productPage->getProductUrl(true).'\',\''.$this->loadPreviewMessage().'\');';
        }
        elseif ($this->onFacebook())
            return 'productview(\''.$productPage->getProductUrl(false,$this->getFacebookUriParams()).'\');';
        else {
            return 'productmodalview(\''.$productPage->productUrl.'\',\''.$productPage->getProductUrl(true).'\');';
        }
    }
    /**
     * Load campaign modal view script
     * @param CampaignBga $campaign
     */
    public function loadCampaignViewScript($campaign,$page)
    {
        $campaignPage = $page->transformPage('CampaignPage',$campaign);//transform to campaign page to be able to construct url
        if ($page->onPreview){
            return 'promoview(\''.$campaignPage->campaignUrl.'\',\''.$this->loadPreviewMessage().'\');';
        }
        else
            return 'promoview(\''.$campaignPage->campaignUrl.'\');';
    }  
    
    public function loadPreviewMessage()
    {
        return Sii::t('sii','Action is disabled when shop is run in preview mode.');
    }    
    
    public function loadPreviewMessageScript()
    {
        return 'alert("'.$this->loadPreviewMessage().'");';
    }    

    public function loadProductCommentScript($page)
    {
        if ($page instanceof CampaignPage)
            $url = $page->getCampaignPageUrl(CampaignPage::COMMENT);
        else
            $url = $page->getProductPageUrl(ProductPage::COMMENT);
        
        if ($page->onPreview)
            return $this->loadPreviewMessageScript();
        elseif ($this->getOwner()->onFacebook())
            return 'newwindowpage("'.$url.'");';
        else {
            if (user()->isGuest)
                return $this->loadSigninScript($page,$url);
            else
                return 'comment($(this).attr(\'form\'));';
        }
    }    
    
    public function loadProductLikeScript($page,$parentJsObj)
    {
        if ($page instanceof CampaignPage)
            $url = $page->getCampaignUrl();
        else
            $url = $page->getProductUrl();
        
        if ($page->onPreview)
            return $this->loadPreviewMessageScript();
        else {
            if (user()->isGuest)
                return $this->loadSigninScript($page,$url);
            else
                return 'liketoggle('.$parentJsObj.');';
        }
    }      
    /**
     * Pick up form set by getAddCartUrl()
     * @return string
     */
    public function loadAddCartScript($page)
    {
        if ($this->getOwner()->onFacebook())
            return 'addcartnewwindow($(this).attr(\'form\'));';
        else
            return 'addcart($(this).attr(\'form\'));';//
    }    
    
    public function getAddCartUrl($page)
    {
        return $page->appendExtraQueryParams('/cart/management/add');//no need to put http scheme
    }        
    
    public function getCommentViewUrl($page,$model)
    {
        //transform to new page to be able to construct url
        if ($model instanceof Product){
            $newPage = $page->transformPage('ProductPage',$model);
            return $newPage->getProductPageUrl(ProductPage::COMMENT);
        }
        elseif ($model instanceof Campaign){
            $newPage = $page->transformPage('CampaignPage',$model);
            return $newPage->getCampaignPageUrl(CampaignPage::COMMENT);
        }   
        else
            return '#';//nothing
    }    

    public function loadQuestionScript($page)
    {
        if ($page instanceof ProductPage)
            $url = $page->getProductPageUrl(ProductPage::QUESTION);
        elseif ($page instanceof CampaignPage)
            $url = $page->getCampaignPageUrl(CampaignPage::QUESTION);
        else
            $url = $page->getUrl(ProductPage::QUESTION);
            
        if ($page->onPreview)
            return $this->loadPreviewMessageScript();
        elseif ($this->getOwner()->onFacebook()){
           return 'newwindowpage("'.$url.'");';
        }
        else {
            if (user()->isGuest)
                return $this->loadSigninScript($page,$url);
            else
                return 'postquestion($(this).attr(\'form\'));';
        }
    }    
    
    public function loadSigninScript($page,$returnUrl=null)
    {
        if ($page->onPreview)
            return $this->loadPreviewMessageScript();
        elseif ($this->getOwner()->onFacebook())
            return 'newwindowpage("'.url('/signin').'");';
        elseif (userOnScope('shop')){
            $url = 'https://'.$this->getOwner()->getCurrentShop()->domain.'/login';//use secure connection
            if (isset($returnUrl))
                $url .= '?returnUrl='.$returnUrl;
            return 'window.location.href = "'.$url.'"';
        }
        else
            return 'signin();';
    }    
    
    public function loadSignupScript($page)
    {
        if (userOnScope('shop')){
            $url = 'https://'.$this->getOwner()->getCurrentShop()->domain.'/register';//use secure connection
            if ($page->onPreview)
                $url = $page->appendExtraQueryParams($url);
            return 'window.location.href = "'.$url.'"';
        }
        else
            return 'signup("'.$this->getOwner()->authHostInfo.'");';
    }
    
    public function getCatalogSidebarWidth()
    {
        return $this->getOwner()->onFacebook()?SPageLayout::WIDTH_25PERCENT:SPageLayout::WIDTH_20PERCENT;
    }
    
    public function getShippingHiddenField($cartItemForm)
    {
        return CHtml::activeHiddenField($cartItemForm,'shipping_id',array('data-source'=>'shipping_'.$cartItemForm->getAttributeLabel('shipping_id')));
    }    
    
    public function getProductOptionsArray($product,$locale=null)
    {
        $options = new CMap();
        foreach ($product->attrs as $model){
            $options->add($model->displayLanguageValue('name',$locale),$model->getOptionsArray($locale,true));
        }
        return $options->toArray();
    }   
    /**
     * Loaded from 
     * @see $this->getCartFormPreparedData
     */
    public function getProductOptionHiddenFields($cartItemForm,$model,$locale)
    {
        $result = '';
        $options = $this->getOwner()->getProductOptionsArray(($model instanceof CampaignBga)?$model->x_product:$model,$locale);
        if (!empty($options)){
            foreach (array_keys($options) as $value){
                $result .= CHtml::hiddenField(get_class($cartItemForm).'[options]['.$value.']','',array('data-source'=>'product_option_'.$value));
            }
        }
        if (($model instanceof CampaignBga)&&$model->hasG()){
            $options = $this->getOwner()->getProductOptionsArray($model->y_product);
            if (!empty($options)){
                foreach (array_keys($options) as $value){
                    $result .=  CHtml::hiddenField(get_class($cartItemForm).'[campaign_item][options]['.$value.']','',array('data-source'=>'promo_product_option_'.$value));
                }
            }
        }
        return $result;
    }      
    /**
     * Return campaign buttons
     * 
     * @param CActiveRecord $model Either Product or Campaign model
     * @param type $exclude Campaign to be excluded from the buttons
     * @return type
     */
    public function getCampaignButtons($model,$exclude=null)
    {
        $output='';
        if ($model instanceof Product){
            $dataProvider = $model->searchCampaigns();
        }
        if (is_array($model)&&isset($model['campaign'])){
            $dataProvider = $model['campaign']->x_product->searchCampaigns();
            $exclude = $model['exclude'];
        }
        foreach($dataProvider->data as $campaign){
            if ((isset($exclude)&&$campaign->id!=$exclude) || !isset($exclude)){
                logTrace(__METHOD__,$campaign->getAttributes());
                $output .= $this->getOwner()->widget('zii.widgets.jui.CJuiButton',array(
                                'name'=>'promo-button-'.hash('crc32b',$campaign->id),
                                'buttonType'=>'button',
                                'caption'=>$campaign->getOfferTag(true),
                                'value'=>'promobtn',
                                'htmlOptions'=> array('class'=>'ui-button','data-key'=>base64_encode($campaign->id)),
                                'onclick'=>'js:function(){viewpromo(\''.base64_encode($campaign->id).'\');}',
                            ),true);
            }
        }
            
        return $output;
    }    
    /**
     * BGA Campaign data provider commonly shared between live and preview mode
     * 
     * @param Shop $shop
     * @return type
     */
    public function getBgaCampaignsDataProvider($shop,$pageSize=null) 
    {
        if (!isset($this->_bgaDataProvider)){
            if ($shop instanceof Shop){
                if ($this->getOwner()->onLive()){
                    $exceptOfferXOnly = false;
                    $this->_bgaDataProvider = $shop->searchCampaignBgas(Process::CAMPAIGN_ONLINE,$exceptOfferXOnly,$pageSize);
                    logTrace(__METHOD__.' Total found',$this->_bgaDataProvider->getTotalItemCount());
                }
                else {//preview mode
                    $this->_bgaDataProvider = $shop->searchCampaignBgas();
                }    
            }        
        }
        return $this->_bgaDataProvider;
    }    
    public function hasBgaCampaigns($shop) 
    {
        return $this->getBgaCampaignsDataProvider($shop)->getTotalItemCount()>0;
    }     
    public function getCartFormPreparedData($cartForm,$model,$locale)
    {
        $data = CHtml::openTag('div', ['style'=>'display:none']);
        if ($cartForm!=null){
            $data .= CHtml::hiddenField(get_class($cartForm).'[pkey]', base64_encode($cartForm->product_id));
            if ($model instanceof Product && $model->hasCampaign())
                $data .= CHtml::hiddenField(get_class($cartForm).'[ckey]', base64_encode($model->getCampaign()->id));
            else if ($model instanceof CampaignBga)
                $data .= CHtml::hiddenField(get_class($cartForm).'[ckey]', base64_encode($model->id));
            else 
                $data .= CHtml::hiddenField(get_class($cartForm).'[ckey]', -1);//invalid model
            $data .= $this->getProductOptionHiddenFields($cartForm,$model,$locale);
            $data .= $this->getShippingHiddenField($cartForm);
        }
        $data .= CHtml::closeTag('div');
        return $data;
    }
    /**
     * Check if to show like form button 
     */
    public function showLikeButton()
    {
        return $this->getOwner()->onLive() && !$this->getOwner()->onFacebook();
    }    
    
    public function loadFavicon($shopModel)
    {
        if ($this->getOwner()->onLive()){
            $this->getOwner()->widget('common.widgets.sfavicon.SFavicon',[
                'enable'=>$shopModel->getFavicon()!=null,
                'url'=>$shopModel->getFaviconUrl(),
            ]);
        }
    } 
    /**
     * Get the shop route according to action
     * @param type $shopModel
     * @param type $action
     * @return type
     */
    public function getShopRoute($shopModel,$action=null)
    {
        $baseroute = $shopModel->getCustomDomain()==null ? '/' : '/shops/storefront/';
        return $baseroute.$action;
    }
    
    public function showJoinUsButton()
    {
        return user()->isGuest && $this->getOwner()->getCurrentShop()->isRegisterToViewMore();
    }    
}
