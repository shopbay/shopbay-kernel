<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopViewPage");
Yii::import("common.modules.shops.components.ShopTrends");
Yii::import("common.modules.shops.components.ShopSortBy");
/**
 * Description of ShopPage
 *
 * @author kwlok
 */
class ShopPage extends ShopViewPage 
{
    use ShopTrends, ShopSortBy;
    /*
     * Trend types
     */
    const TREND_RECENTLIKED       ='recentliked';
    const TREND_RECENTDISCUSSED   ='recentdiscussed';
    const TREND_RECENTPURCHASED   ='recentpurchased';
    const TREND_MOSTLIKED         ='mostliked';
    const TREND_MOSTDISCUSSED     ='mostdiscussed';
    const TREND_MOSTPURCHASED     ='mostpurchased';       
    /*
     * Sort by types
     */
    const SORTBY_NAME_A_Z  = 'a_z';
    const SORTBY_NAME_Z_A  = 'z_a';
    const SORTBY_PRICE_L_H = 'low_to_high';
    const SORTBY_PRICE_H_L = 'high_to_low';
    const SORTBY_DATE_O_N  = 'old_to_new';
    const SORTBY_DATE_N_O  = 'new_to_old';       
    /**
     * In-built Pages
     * These pages are auto generated by system and currently does not support theme and layout editing
     * The major different between in-built and fixture page is that in-built page cannot be used as a navigation menu item
     * @todo Should all open to migrate to custom pages ??
     */
    const CART        = 'cart_page';
    const PRODUCT     = 'product_page';//product modal page
    const CAMPAIGN    = 'campaign_page';//campaign (single promotion) page
    const SEARCH      = 'search_page'; 
    const CATEGORY    = 'category_page';
    const BRAND       = 'brand_page';
    const LOGIN       = 'login_page';//shop customer login page
    const REGISTER    = 'register_page';//shop customer registration page
    const HTML        = 'html_page';//any html page content to be embedded as main content, more for system use such as error page, message page etc
    const SITEMAP_XML = 'sitemap.xml_page';
    const QUESTION    = 'question_page';
    const FAQ         = 'faq_page';
    public static function inBuiltPages()
    {
        $inbuilt = [//default in-built page set
            ShopPage::CART,
            ShopPage::LOGIN,
            ShopPage::REGISTER,
//            ShopPage::FAQ,
//            ShopPage::QUESTION,
        ];
        return $inbuilt;
    }
    /**
     * Custom pages
     * @see common.modules.shops.data.pages.php
     */
//    const HOME        = 'home_page';//Moved to BasePage 
    const ABOUT       = 'about_page';
    const TOS         = 'terms_page'; 
    const PRIVACY     = 'privacy_page';
    const CUSTOM      = 'page_page';//this is a custom shop page
    const CONTACT     = 'contact_page';//using fixture_layout (locked)
    const SHIPPING    = 'shipping_page';//using fixture_layout (locked)
    const PAYMENT     = 'payment_page';//using fixture_layout (locked)
    const PRODUCTS    = 'products_page';//all products view or direct single product page view
    const PROMOTIONS  = 'promotions_page';//promotion list page
    const TRENDS      = 'trends_page';
    const NEWS        = 'news_page';
    const REFUND      = 'refund_page';
    const RETURNS     = 'returns_page';
    const SITEMAP     = 'sitemap_page';
    public static function customPages()
    {
        $pages = include Yii::getPathOfAlias('common.modules.shops.data').DIRECTORY_SEPARATOR.'pages.php';
        return array_keys($pages);
    }    
    const FACEBOOK_PARAM = 'fbpage';
    
    private $_dataProvider;
    /*
     * Search query if any
     */
    public $searchQuery = '';
    public $searchByAjax = false;
    public $searchPageNum;
    /**
     * Page name; Change to short name (remove "_page")
     * @return type
     */
    public function getName($locale=null)
    {
        return static::trimPageId($this->id);
    }
    /**
     * Page display name
     * @return type
     */
    public function getDisplayName($locale=null)
    {
        if ($this->hasFilter){
            $sublinks = $this->filter->getMenuArray($locale);
            $sublinkKeys = array_keys($sublinks);
            return end($sublinkKeys);//return the last array element
        }
        else
            return ShopPage::getTitle($this->id,$locale);
    }
    /**
     * Get page data, used when $this->getPage() is invoked
     * 
     * [1] For inbuilt page, the data below is mandatory to construct page content
     * 
     * [2] For fixture and custom page:
     *  - Below are required if page_content/page_html is directly called from layout.json
     *  - Or, required for page editor as initial template 
     *  - If theme provides its own layout for the page, this code fragment is not executed. 
     *  - If theme does not provide page layout, then view file "_<page_id>" must be provided for rendering
     * 
     * @return array
     * @throws CException
     */
    public function getData($locale=null)
    {
        logTrace(__METHOD__.' load data for '.$this->id);
        switch ($this->id) {
            case self::SEARCH:
                $form = $this->getSearchForm();
                if ($form->hasQuery())
                    $response = $this->controller->parseQuery($form->query,$this->searchPageNum);
                else
                    $response = $this->controller->getSearchEmptyResponse();
                if ($this->searchByAjax){
                    return [
                        'query'=>$form->query,
                        'status'=>$response->status,
                        'results'=>$this->controller->getSearchResults($response),
                    ];
                }
                else {
                    return [
                        'view'=>$this->controller->module->getView('search.default'),
                        'data'=>[
                            'query'=>$form->query,
                            'response'=>$response,
                        ],
                    ];
                }
            case self::TRENDS:
                return $this->trendsData;
            case self::PROMOTIONS:
                return $this->getDefaultData(
                           $this->controller->renderPartial(
                                    $this->controller->getThemeView('_promo'),
                                    [
                                        'idx'=>'promotion_list',
                                        'dataProvider'=>$this->dataProvider,
                                    ],true),
                           Sii::t('sii','Promotions')
                        );            
            case self::CART:
                if (!$this->onPreview)
                    Yii::app()->serviceManager->getAnalyticManager()->trackAddCartVisit($this->model->account_id, $this->model->id, Helper::getVisitor());
                return $this->getDefaultData($this->cartPageContent,$this->getDisplayName($locale));
            //Need content below for initial loading (before user start edit them)    
            case self::ABOUT:
            case self::RETURNS:
            case self::REFUND:
            case self::TOS:
            case self::PRIVACY:
                return $this->getDefaultData(Sii::tl('sii','Enter your content here',$locale),$this->getDisplayName($locale));
            case self::REGISTER:
            case self::LOGIN:
                return $this->getDefaultData($this->getFormContent(['page'=>$this]),self::getTitle($this->id));
            case self::HTML:
                return $this->getDefaultData($this->htmlPage);
            case self::QUESTION:
                return $this->getDefaultData($this->renderPage('404'));//temp solution as shop level questions is hidden, but product/campaign level questions need to use the constant 
//                $form = $this->getQuestionForm();
//                $loginScript = $this->onPreview ? $this->controller->loadPreviewMessageScript() : 'postquestion("'.$form->id.'")';
//                return $this->getDefaultData(
//                           $this->controller->renderView('questionform',[
//                                        'model'=>$form,
//                                        'preview'=>$this->controller->onPreview()?true:null,
//                                    ],true),
//                           user()->isGuest?Sii::t('sii','You must be {loginlink} to ask question.',['{loginlink}'=>CHtml::link(Sii::t('sii','logged in'),'javascript:void(0);',['onclick'=>$loginScript])]):'',
//                           self::getTitle($this->id), 
//                        );
            case self::FAQ:
                return $this->getDefaultData($this->renderPage('404'));//temp solution as shop level questions is hidden
//                return $this->getDefaultData($this->getDisplayName($locale), $this->faqPageContent);
            case self::CATEGORY:
            case self::BRAND:
            case self::PRODUCT:
            case self::PRODUCTS:
            case self::HOME:
            default:
                return [
                    'view'=>$this->controller->getThemeView('_'.self::trimPageId($this->id)),
                    'data'=>[
                        'page'=>$this,
                        'dataProvider'=>$this->dataProvider,
                    ],
                ];
         }
    }
    public function setSearchQuery($query)
    {
        $this->searchQuery = $query;
    }     
    public function setSearchPageNum($pageNum)
    {
        $this->searchPageNum = $pageNum;
    }     
    public function setSearchByAjax($bool)
    {
        $this->searchByAjax = $bool;
    }     
    /**
     * Overriden method
     * @return boolean
     */
    public function getDataProvider()
    {
        switch ($this->id) {
            case self::CATEGORY:
            case self::BRAND:
            case self::PRODUCT:
            case self::PRODUCTS:
            case self::HOME://todo temporary , to make compatible with current theme
                return $this->productDataProvider;
            case self::PROMOTIONS:
                return $this->campaignDataProvider;
            case self::TRENDS:
                return $this->trendsDataProvider;
            case self::NEWS://support for SGridCategoryBlock used
                $page = new CustomPage(ShopPage::NEWS, $this->model, $this->controller, false);//create a dummy page to access news dataprovider
                return $page->newsDataProvider;
            default:
                return false;//no dataprivder attached
        }        
    }     
    
    public function getProductDataProvider() 
    {
        if (!isset($this->_dataProvider)){
            
            if ($this->hasFilter){
                $browseBy = $this->filter->type;
                $browseValue = $this->filter->value;
            }
            
            //Set status to null means no status check, i.e. preview allow to view offline products also
            $status = $this->includeOfflineItems ? null : Process::PRODUCT_ONLINE;
                
            if (isset($browseBy) && $browseBy==ShopBrowseMenu::CATEGORY){
                $this->_dataProvider = $this->model->searchProductsByCategory($browseValue,$status,$this->getSortByCriteria());
                $this->sortbyBaseurl = $this->filter->getUrl();
            }
            elseif (isset($browseBy) && $browseBy==ShopBrowseMenu::BRAND){
                $this->_dataProvider = $this->model->searchProductsByBrand($browseValue,$status,$this->getSortByCriteria());
                $this->sortbyBaseurl = $this->filter->getUrl();
            }
            else {//default search products
                $this->_dataProvider = $this->model->searchProducts($status,$this->getSortByCriteria());
            }

            $paginationParams = ['shop'=>$this->model->id];
            if (isset($filterCondition)||!empty($filterCondition))
                $paginationParams = array_merge($paginationParams,['browseBy'=>$browseBy,'group'=>$browseValue]);
            //$this->_dataProvider->pagination = $this->getPagination(null,$paginationParams);//keep default route same, but requires pageSize
            $this->_dataProvider->pagination = $this->getPagination('storefront/catalog', $paginationParams);
            
        }
        return $this->_dataProvider;
    }
    
    public function getCampaignDataProvider()
    {
        if (!isset($this->_dataProvider)){
            $this->_dataProvider = $this->controller->getBgaCampaignsDataProvider($this->model);
            $this->_dataProvider->pagination = $this->getPagination('storefront/promotion', ['shop'=>$this->model->id]);
        }
        return $this->_dataProvider;
    }
    
    public function getCartPageContent()
    {
        return $this->renderPage('_shop_cart',['shop'=>$this->model->id,'queryParams'=>$this->getExtraQueryParams()]);
    }        
    /**
     * Get like form
     * @param type $modal
     * @return \LikeForm
     */
    public function getLikeForm()
    {
        $likeForm = new LikeForm(get_class($this->model),$this->model->id);
        if ($this->onPreview){
            $likeForm->formScript = $this->controller->loadPreviewMessageScript();
            $likeForm->buttonScript = $this->controller->loadPreviewMessageScript();
        }    
        return $likeForm;
    }    
    //todo Hide ‘Ask question’ and FAQ’ page for now. - Pending solution #279: [Product] Shop enquiry messages and FAQ management  is ready.
//    public function getFaqPageContent()
//    {
//        $QnA = new CMap();
//        foreach ($this->model->searchQuestions(Process::QUESTION_ONLINE)->data as $data) {
//            //$qHtml = Helper::purify($data->question).' <span class="date" style="float:right">'.$data->formatDatetime($data->question_time,true).'</span>';
//            //$aHtml = Helper::purify($data->answer).' <span class="date" style="float:right">'.$data->formatDatetime($data->answer_time,true).'</span>';
//            $QnA->add(Helper::purify($data->question), Helper::purify($data->answer));
//        };
//        return $this->controller->widget('shopwidgets.shopfaq.ShopFAQ',['QnA'=>$QnA],true);
//    }
//    /**
//     * Get question form
//     * @return \QuestionForm
//     */
//    public function getQuestionForm()
//    {
//        $form = new QuestionForm();
//        $form->id = 'shop_question_form';
//        $form->askUrl = $this->getUrl(ShopPage::QUESTION);
//        $form->obj_type = get_class(Shop::model());
//        $form->obj_id = $this->model->id;
//        $form->formScript = $this->controller->loadQuestionScript($this);
//        return $form;
//    }    
    /**
     * Get search form
     * @return \SearchForm
     */
    public function getSearchForm()
    {
        $form = new SearchForm();
        $form->id = 'shop_search_form';
        $form->shop_id = $this->model->id;
        $form->query = $this->searchQuery;
        return $form;
    }  
    
    protected function getPagination($route=null,$params=null)
    {
        $config = [
            'pageSize'=>Config::getBusinessSetting('catalog_item_per_page'),
            'params'=>$params,
        ];
        if (isset($route))
            $config = array_merge ($config,[
                'route'=>$route
            ]);
        return $config;
    }  
    /**
     * A helper method to consctruct shop url without ShopPage object.
     */
    public static function getPageUrl($shopModel,$page=null,$relative=false,$secure=false,$params=[])
    {
        if ($relative){
            if (isset($page))
                $url = '/shop/'.$shopModel->slug.'/'.self::trimPageId($page);
            else
                $url = '/shop/'.$shopModel->slug;
        }
        else {
            if (isset($page))
                $url = $shopModel->getUrl($secure).'/'.self::trimPageId($page);
            else
                $url = $shopModel->getUrl($secure);
        }
        
        return Helper::constructUrlQuery($url, $params);
    }    
    
    public static function getTitle($page,$locale=null)
    {
        switch ($page) {
            case self::CART:
                return Sii::tl('sii','Shopping Cart',$locale);
            case self::NEWS:
                return Sii::tl('sii','News',$locale);
            case self::ABOUT:
                return Sii::tl('sii','About us',$locale);
            case self::CONTACT:
                return Sii::tl('sii','Contact us',$locale);
            case self::PAYMENT:
                return Sii::tl('sii','Payment Methods',$locale);
            case self::SHIPPING:
                return Sii::tl('sii','Shippings',$locale);
            case self::RETURNS:
                return Sii::tl('sii','Returns Policy',$locale);
            case self::REFUND:
                return Sii::tl('sii','Refund Policy',$locale);
            case self::PRIVACY:
                return Sii::tl('sii','Privacy Policy',$locale);
            case self::TOS:
                return Sii::tl('sii','Terms of Service',$locale);
            case self::TRENDS:
                return Sii::tl('sii','Trends',$locale);
            case self::CATEGORY:
                return Sii::tl('sii','Categories',$locale);
            case self::BRAND:
                return Sii::tl('sii','Brands',$locale);
            case self::PROMOTIONS:
                return Sii::tl('sii','Promotions',$locale);
            case self::SITEMAP:
                return Sii::tl('sii','Sitemap',$locale);
            case self::LOGIN:
                return Sii::tl('sii','Login',$locale);
            case self::REGISTER:
                return Sii::tl('sii','Register',$locale);
            case self::PRODUCTS:
                return Sii::tl('sii','Products',$locale);
//            case self::FAQ:
//                return Sii::tl('sii','FAQ',$locale);
//            case self::QUESTION:
//                return Sii::tl('sii','Ask Question',$locale);
            case self::SEARCH:
            default://default page is HOME
                return Sii::tl('sii','Home',$locale);
         }
    }
        
    public static function existsPage($page)
    {
        $refl = new ReflectionClass('ShopPage');
        logTrace(__METHOD__,$page);
        return in_array($page, array_values($refl->getConstants()));
    }
    
    public static function isCustomPage($page)
    {
        return $page==ShopPage::CUSTOM || in_array($page, static::customPages());
    }
    /**
     * The shop default page
     */
    public static function defaultPage()
    {
        return ShopPage::HOME;
    }
    /**
     * @inheritdoc
     */
    public function getHomePage()
    {
        return ShopPage::defaultPage();
    }  
    
    public function getPageSeoTitle($locale)
    {
        $seoTitle = parent::getPageSeoTitle($locale);
        if (strlen($seoTitle)==0)
            $seoTitle = ShopPage::getTitle($this->id);
        return $seoTitle;
    }
}