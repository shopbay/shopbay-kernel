<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.ShopViewPage");
Yii::import("common.modules.shops.components.ShopPage");
Yii::import("common.modules.shops.components.ShopTrends");
Yii::import("common.modules.shops.components.ShopSortBy");
Yii::import("common.modules.shops.components.ShopNews");
/**
 * Description of CustomPage
 *
 * @author kwlok
 */
class CustomPage extends ShopViewPage 
{
    protected $pageId;
    use ShopNews, ShopSortBy, ShopTrends;//need to include ShopTrends in case home page is using trends in category block, and need to access $trendTopic
    /**
     * Get page data
     * @return type
     * @throws CException
     */
    public function getData($locale=null) 
    {
        $pageId = $this->getCustomPageId();
        switch ($pageId) {
            case ShopPage::NEWS:
                if (isset($_GET['article'])){
                    $this->setNews($_GET['article']);
                    return $this->getDefaultData($this->getNewsArticleContent($locale));
                }
                else
                    return $this->getDefaultData($this->getNewsPageContent($locale));
            case ShopPage::SITEMAP:
                return $this->getDefaultData($this->sitemapContent,ShopPage::getTitle($pageId));
            case ShopPage::CONTACT://load contact form
                return $this->getDefaultData($this->contactPageContent);
            case ShopPage::PAYMENT:
                return $this->getDefaultData($this->getPaymentPageContent($locale),ShopPage::getTitle($pageId),Sii::tl('sii','We accept:',$locale));
            case ShopPage::SHIPPING:
                return $this->getDefaultData($this->getShippingPageContent($locale),ShopPage::getTitle($pageId),Sii::tl('sii','We deliver by:',$locale));
            default://default custom page
                $content = Helper::purify($this->model->displayLanguageValue('content',$locale));
                if (!empty($content))
                    return $this->getDefaultData($content);
                else //if content is not found (likely is a new page or default in-built page), show heading
                    return $this->getDefaultData(null,$this->model->displayLanguageValue('title',$locale));
        }        
    }
    
    public function getCustomPageId()
    {
        if (!isset($this->pageId)){
            $this->pageId = static::restorePageId($this->model->slug);//since page has standard "page_page" id, has to used internally slug to identify which page to load
            logTrace(__METHOD__.' load data for page '.$this->pageId);
        }
        return $this->pageId;
    }
    /**
     * Need to customize as custom page id is changed according to page name
     * @inheritdoc
     */
    public function getUrl($page=null,$params=[])
    {
        if (isset($page))
            return parent::getUrl($page, $params);
        else {
            $route = static::trimPageId($this->getCustomPageId());//if no targetted page, return url pointing to itself
            return $this->constructUrl($route,$params);
        }        
    }   
    /**
     * @inheritdoc
     */
    protected function getDefaultData($content, $heading=null, $desc=null, $pageId=null)
    {
        if ($pageId==null)
            $pageId = $this->getCustomPageId();
        return parent::getDefaultData($content, $heading, $desc, $pageId);
    }     
    
    public function getNewsPageContent($locale)
    {
        return $this->renderPage('_news_listing',['dataProvider'=>$this->getNewsDataProvider()]);
    }    
    
    public function getNewsArticleContent($locale)
    {
        $article = $this->getNews();
        if ($article === null)
            return $this->renderPage('404');//error page not found
        else
            return $this->renderPage('_news_article',['model'=>$article]);
    }     
        
    public function getPaymentPageContent($locale)
    {
        $content = CHtml::openTag('ol');
        foreach ($this->shopModel->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE)->data as $payment) {
            $content .= CHtml::tag('li',['class'=>'name'],$payment->getMethodName($locale));
            if ($payment->method==PaymentMethod::PAYPAL_EXPRESS_CHECKOUT)
                $content .= $this->controller->renderPartial($this->controller->module->getView('payments.paypallogo'),[],true);
            $content .= CHtml::tag('div',[],$payment->getDescription($locale));
        };
        $content .= CHtml::closeTag('ol');
        return $content;
    }
    
    public function getShippingPageContent($locale)
    {
        $content = CHtml::openTag('ol');
        foreach ($this->shopModel->searchShippings(Process::SHIPPING_ONLINE)->data as $shipping) {
            $content .= CHtml::tag('li',['class'=>'name'],$shipping->displayLanguageValue('name',$locale));
            $content .= Helper::htmlList($shipping->getShippingRemarks($locale),['style'=>'margin:0;']);
        };
        $content .= CHtml::closeTag('ol');
        return $content;
    }
    
    public function getContactPageContent()
    {
        $form = new ShopContactForm($this->shopModel);
        return $form->render($this->controller);
    }        
    /**
     * Get sitemap definition
     * @param bool $configOnly
     * @param string $contentType
     * @return array
     */
    public function getSitemapContent($configOnly=false,$contentType='text/html')
    {
        Yii::import('shopwidgets.shopsitemap.ShopSitemap');        
        $config = ShopSitemap::getDefaultConfig($this,$contentType);
        if ($configOnly)
            return $config;
        else
            return $this->controller->widget('ShopSitemap',$config,true);
    }    
}
