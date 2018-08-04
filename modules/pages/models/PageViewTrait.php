<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.BasePage");
/**
 * Description of PageViewTrait
 * Class use this trait must implement {@link PageViewInterface}
 *
 * @author kwlok
 */
trait PageViewTrait
{
    public $edit = false;//if in edit mode
    public $https = false;//if use https
    public $previewToken;
    public $previewTheme;
    public $previewStyle;
    public $previewOfflineItems = false;
    public $currentTheme;
    public $currentStyle;
    private $_pageModel;
    /**
     * Transform current page to a new target page;
     * This will transfer those key view trait attributes
     * @return ShopViewPage
     */
    public function transformPage($targetPage,$targetModel)
    {
        $newPage = new $targetPage($this->id, $targetModel, $this->controller, false);
        //transfer page params to new page
        foreach (['currentTheme','previewTheme','currentStyle','previewStyle', 'previewToken','https','previewOfflineItems'] as $field) {
            $newPage->$field = $this->$field;
        }
        return $newPage;
    }
    /**
     * To be in sync with preview url
     * @see themes/controllers/PreviewController 
     * @return type
     */
    public function getPreviewParams()
    {
        if ($this->onPreview)
            return [
                'theme'=>$this->previewTheme,
                'style'=>$this->previewStyle,
                'preview'=>$this->previewToken,
                'https'=>$this->https,
                'offline'=>$this->previewOfflineItems,
            ];
        else
            return [];
    }
    /**
     * Check if can be run in preview mode
     * Under preview mode, override theme to follow preview setting, else use back current theme
     * Only preview request with a valid token are allowed
     * @see self::getPreviewParams() for the expected query params 
     * @return type
     */
    public function switchPreviewTheme($theme,$style,$token)
    {
        if ($token==Page::previewToken($this->pageOwner)){
            $this->previewTheme = $theme;//this is for page object
            $this->previewStyle = $style;//this is for page object
            $this->previewToken = $token;
            if (isset($_GET['offline']))
                $this->previewOfflineItems = $_GET['offline'];
            $this->currentTheme = $this->previewTheme;//set preview theme as current theme
            $this->currentStyle = $this->previewStyle;//set preview theme as current theme
            $this->controller->setViewMode(BasePage::PREVIEW);//this is for Controller
            logInfo(__METHOD__." Run theme '$this->currentTheme', '$this->currentStyle' preview", $this->id);
        }
        else {
            $this->controller->renderErrorPage(Sii::t('sii','No preview available'));
        }
    }    
    /**
     * @return boolean If page is on preview
     */
    public function getOnPreview()
    {
        return isset($this->previewTheme) && isset($this->previewStyle) && isset($this->previewToken);
    }
    /**
     * Turn on to display offline items, only works in preview mode
     * @todo This field is to let merchant config as an option 
     * @return boolean True to enable offline items preview
     */
    public function allowPreviewOfflineItems()
    {
        return $this->onPreview && false;//for now is disabled
    }
    /**
     * @return boolean true or false
     */
    public function getIncludeOfflineItems()
    {
        return $this->allowPreviewOfflineItems() && $this->previewOfflineItems;
    }
    /**
     * Home page  
     * Default home page value
     * Note: Class use the trait can override the setting
     */
    public function getHomePage()
    {
        return BasePage::HOME;
    }   
    /**
     * Home page url 
     */
    public function getHomeUrl()
    {
        return $this->getUrl($this->homePage);
    }    
    /**
     * Base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->pageOwner->getUrl($this->https || request()->isSecureConnection);
    }
    /**
     * Page url 
     * @param mixed $params query params; If value is false, will not auto append query params
     * @inheritdoc
     */
    public function getUrl($page=null,$params=[])
    {
        if (isset($page))
            $route = $page==$this->homePage ? null : static::trimPageId($page);//if a specific page is targeted
        else 
            $route = static::trimPageId($this->id);//if no targetted page, return url pointing to itself
        
        return $this->constructUrl($route,$params);
    }    
    /**
     * Construct url (using base url)
     * @param string $route the url route
     * @param mixed $params query params; If value is false, will not auto append query params
     */
    public function constructUrl($route=null,$params=[])
    {
        $url = $this->baseUrl.'/'.$route;
        if (is_bool($params) && $params==false)
            return $url;
        else
            return $this->appendExtraQueryParams($url, $params);
    }      
    /**
     * Get the extra query params to associate with url
     * $params can be changed depends on scenarios
     * @return array
     */
    public function getExtraQueryParams($params=[])
    {
        if ($this->onPreview)
            $params = array_merge($params,$this->previewParams);
        if ($this->onOffsite())
            $params = array_merge($params,$this->getOffsiteUriParams());
        return $params;
    }
    
    public function appendExtraQueryParams($url,$params=[])
    {
        $params = $this->getExtraQueryParams($params);
        return $url.(empty($params) ? '' : '?'.http_build_query($params));
    }
    /**
     * Page preview url
     * @return type
     */
    public function getPagePreviewUrl()
    {
        $uri = http_build_query([
            'page'=>$this->pageModel->id,
        ]);
        return url('themes/preview/'.$this->currentTheme.'/'.$this->currentStyle).'?'.$uri;
    }
    /** 
     * The page layout edit url
     * @return string
     */
    public function getPageEditUrl()
    {
        return $this->model instanceof Page ? $this->model->layoutUrl : url('pages/layout?page='.$this->id);
    }        
    /** 
     * The page layout view url
     * @return string
     */
    public function getPageViewUrl()
    {
        return $this->pageModel->viewUrl;
    }     
    /** 
     * The underlying page model
     * @return CModel
     */
    public function getPageModel()
    {
        if (!isset($this->_pageModel)){
            if ($this->model instanceof Page)
                $this->_pageModel = $this->model;
            else
                $this->_pageModel = Page::model()->locateOwner($this->pageOwner)->locatePage($this->id)->find();
        }
        return $this->_pageModel;
    }       
    
    public function getPageSeoTitle($locale)
    {
        return $this->pageModel!=null ? $this->pageModel->getSeoTitle($locale) : '';
    }
    
    public function getPageSeoDesc($locale)
    {
        return $this->pageModel!=null ? $this->pageModel->getSeoDesc($locale) : '';
    }
    
    public function getPageSeoKeywords()
    {
        return $this->pageModel!=null ? $this->pageModel->getSeoKeywords() : '';
    }
    
}
