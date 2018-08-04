<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageTrait
 *
 * @author kwlok
 */
trait PageTrait 
{
    /**
     * Fall back page title if $seoTitle is not found/set
     * @var boolean 
     */
    protected $fallbackPageTitle = true;
    /**
     * Parent model to find fall back page title if $seoTitle is not found/set
     * @return string
     */
    protected function getSeoModel()
    {
        return $this;
    }
    protected function getMetaTagAttribute()
    {
        return 'params';
    }
    protected function getSeoTitleAttribute()
    {
        return 'seoTitle';
    }
    protected function getSeoDescAttribute()
    {
        return 'seoDesc';
    }
    protected function getSeoKeywordsAttribute()
    {
        return 'seoKeywords';
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function pageSeoAttributeLabels()
    {
        return [
            'seoTitle' => Sii::t('sii','SEO Page Title'),
            'seoDesc' => Sii::t('sii','SEO Page Description'),
            'seoKeywords' => Sii::t('sii','SEO Page Keywords'),
        ];
    }       
    /**
     * Get meta tags setting 
     * @param type $field
     * @return param value
     */
    public function getMetaTag($field)
    {
        $params = json_decode($this->{$this->getMetaTagAttribute()},true);
        if (!empty($params))
            return isset($params[$field])?$params[$field]:null;
        else
            return null;
    }
    /**
     * Get meta tag attributes
     * @return array 
     */
    public function getMetaTagAttributes()
    {
        $metaTags = json_decode($this->{$this->getMetaTagAttribute()},true);
        return $metaTags!=null ? $metaTags : [];
    }   
    /**
     * Get seo title
     * @todo Need to support internalization for param 'seoTitle'
     * @param type $locale
     * @return string 
     */
    public function getSeoTitle($locale)
    {
        $title = $this->getMetaTag($this->getSeoTitleAttribute());
        if ($this->fallbackPageTitle && (strlen($title)==0 || $title==null))
            return $this->getSeoModel()->displayLanguageValue('name',$locale);
        else
            return $title;
    }
    /**
     * Get seo description
     * @todo Need to support internalization for param 'seoDesc'
     * @param type $locale
     * @return string 
     */
    public function getSeoDesc($locale)
    {
        return $this->getMetaTag($this->getSeoDescAttribute());
    }
    /**
     * Get seo keywords
     * @todo Need to support internalization for param 'seoKeywords'
     * @param type $locale
     * @return string 
     */
    public function getSeoKeywords()
    {
        return $this->getMetaTag($this->getSeoKeywordsAttribute());
    }    
}
