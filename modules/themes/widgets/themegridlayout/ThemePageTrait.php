<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This trait containts all the support in-built theme page widgets can be used in layout
 * 
 * @see ThemeGridLayout use together with this
 * @see The model page object that use the layout; E.g. ShopPage
 * @author kwlok
 */
trait ThemePageTrait 
{
    /**
     * @return CModel Get the page theme to access its theme level settings
     */
    public function getPageTheme()
    {
        return $this->page->pageTheme;
    }
    /**
     * @return string page id
     */
    public function getPageId()
    {
        return $this->page->id;
    }
    /**
     * @return string page model name
     */
    public function getPageModelName()
    {
        return $this->page->model->localeName($this->locale);
    }     
    /**
     * @return string page content
     */
    public function getPageContent()
    {
        return $this->page->getPage($this->locale);
    }     
    /**
     * @return array page html (for SGridHtmlBlock)
     */
    public function getPageHtml()
    {
        $html = [];
        foreach ($this->page->pageOwner->getLanguageKeys() as $locale) {
            $html[$locale] = $this->page->getPage($locale);
        }
        return $html;
    }    
    /**
     * Customize page data provider
     * @param type $page Indicate which page data provider to call; If null, it will follow current page id
     * @param array $filter @see ShopPageBehavior::setPageSettings() for input format
     * @return type
     */
    public function getPageDataProvider($page=null,$filter=[])
    {
        if (isset($page)){
            $pageObj = $this->createPage($page, $filter);
            return $pageObj->dataProvider;
        }
        else
            return $this->page->dataProvider;//return current page provider
    }     
    /**
     * @return string page data provider total count
     */
    public function getPageDataProviderTotalCount($page=null)
    {
        return $this->getPageDataProvider($page)->getTotalItemCount();
    }         
    /**
     * Get data provider name
     * @see This can be mapped to SListBlockWidget $title (a string, not an array)
     * @return string
     */
    public function getPageDataProviderName()
    {
        return $this->page->getDisplayName($this->locale);
    } 

}
