<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.BasePage");
Yii::import("common.modules.shops.components.ShopPage");
Yii::import("common.modules.shops.components.ShopPageFilter");
Yii::import("common.modules.pages.models.PageViewTrait");
Yii::import("common.modules.pages.models.PageViewInterface");
/**
 * Description of ShopViewPage
 *
 * @author kwlok
 */
abstract class ShopViewPage extends BasePage implements PageViewInterface
{
    use PageViewTrait;
    private $_pt;//page theme model
    /*
     * Filter object
     */
    public $filter;
    /*
     * A form model placeholder; For page to load any form
     */
    public $formModel;
    /*
     * A form view file placeholder; For page to load any form
     */
    public $formViewFile;    
    /*
     * An embedded html page up to caller to fill
     */
    public $htmlPage;
   /**
     * Page constructor
     * @param type $id Page id
     * @param Shop $model Model owns the page
     * @param type $controller Controller of the page
     */
    public function __construct($id,$model,$controller,$trackVisit=true)
    {
        parent::__construct($id,$model,$controller);
        if (!$controller->onPreview() && $trackVisit)
            $this->trackVisit();//always track visit when page is constructed and live
    }
    /**
     * Track shop visit
     * Pageview: each page hit of a shop will be recorded
     * Visitor: unique visitor count; For login user, this will be account_id; For guest, this will be IP address
     * 
     * @see AnalyticManager::trackShopVisit()
     */
    public function trackVisit()
    {
        $shopId = $this->shopModel->id;
        if (isset($shopId)){
            Yii::app()->serviceManager->getAnalyticManager()->trackShopVisit($this->model->account_id,$shopId,Helper::getVisitor());
        }
    }   
    /**
     * Default rule to determine if to show page
     * use case: CLASSIC THEME tabs
     * @return boolean
     */
    public function getIsVisible()
    {
        return ($this->dataProvider && $this->dataProvider->getTotalItemCount()>0) ||
                $this->dataProvider===false;
    }
    /**
     * Filter data format: array($filterType=>$filterId)
     */
    public function setFilter($data)
    {
        $this->filter = new ShopPageFilter('filter.'.$this->model->id,$this->model,$this->controller);
        $this->filter->setData($data);
    }     
    
    public function getHasFilter()
    {
        return isset($this->filter);
    } 
    /**
     * @return Shop model
     */
    public function getShopModel()
    {
        if ($this->model instanceof Page)
            return $this->model->owner;
        elseif (!$this->model instanceof Shop)
            return $this->model->shop;
        else
            return $this->model;
    } 
    /**
     * Page display name
     * @return type
     */
    public function getDisplayName($locale)
    {
        return $this->model->displayLanguageValue('title',$locale);
    }
    /**
     * Get a particular page, attach theme based on controller
     * @return string
     */
    public function getPage($locale=null)
    {
        $theme = $this->onPreview ? $this->previewTheme : $this->shopModel->getTheme();
        $this->controller->setThemeLayout($theme);
        $data = $this->getData($locale);//need to have temp holder to prevent load data twice
        return $this->controller->renderPartial($data['view'],$data['data'],true);
    }        
    /**
     * A helper method to render page view
     * @return string
     */
    public function renderPage($viewFile,$viewData=[])
    {
        return $this->controller->renderPartial($this->controller->getThemeView($viewFile),$viewData,true);
    }
    /**
     * Get page owner
     * @return string
     */
    public function getPageOwner()
    {
        return $this->shopModel;
    }
    /**
     * Get current page theme
     * @return CActiveRecord
     */
    public function getPageTheme()
    {
        $this->_pt = ShopTheme::model()->locateOwner($this->pageOwner->id)->locateTheme($this->currentTheme,$this->currentStyle)->find();
        if ($this->_pt==null){
            $this->_pt = ShopTheme::create($this->pageOwner->id, $this->currentTheme, $this->currentStyle);
        }
        return $this->_pt;
    }
    /**
     * Interface method
     * @inheritdoc
     */
    public function onOffsite()
    {
        return $this->controller->onFacebook();
    }
    /**
     * Interface method
     * @inheritdoc
     */
    public function getOffsiteUriParams()
    {
        return $this->controller->getFacebookUriParams();
    }    
    /**
     * Set form model for loading
     * @return \CFormModel
     */
    public function setFormModel($form)
    {
        $this->formModel = $form;
    }     
    /**
     * Set form view file for loading
     * @return \CFormModel
     */
    public function setFormViewFile($view)
    {
        $this->formViewFile = $view;
    }     
    /**
     * Get form content
     * @return string 
     */
    public function getFormContent($viewData=[])
    {
        if (!isset($this->formModel) || !isset($this->formViewFile))
            return 'Error 404';
        
        return $this->controller->renderPartial($this->formViewFile,array_merge(['model'=>$this->formModel],$viewData),true);
    }      
    
    protected function getDefaultData($content, $heading=null, $desc=null, $pageId=null)
    {
        return [
            'view'=>$this->controller->getThemeView('_shop_page'),
            'data'=>[
                'heading'=>$heading,
                'desc'=>$desc,
                'content'=>$content,
                'pageId'=>isset($pageId)?$pageId:$this->id,
                //'script'=>new CJavaScriptExpression('alert("Test")'),
            ],
        ];          
    }    
    /**
     * Removing the suffix from page id
     * @param type $page
     * @return type
     */
    public static function trimPageId($page)
    {
        return substr($page,0,-strlen(static::pageIdSuffix()));
    }
    /**
     * Restore the page id (adding back suffix)
     * @param type $id The trimmed page id
     * @return type
     */
    public static function restorePageId($id)
    {
        return $id.static::pageIdSuffix();
    }
    /**
     * The page id suffix "_page" 
     * @param type $page
     * @return type
     */
    public static function pageIdSuffix()
    {
        return '_page';
    }
    
}
