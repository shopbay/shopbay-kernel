<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageSEOTrait
 *
 * @author kwlok
 */
trait PageSEOTrait 
{
    public static $suffixSeoAttribute = 'seo';
    public static $pageTitleLength = 60;
    public static $metaDescLength  = 160;
    public static $metaKeywordsLength = 155;
    
    protected function getSeoShopAttribute()
    {
        return 'shop_id';
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
     * SEO validation rules 
     * @return array 
     */
    public function seoRules()
    {
        return [
            [$this->getSeoTitleAttribute(), 'length', 'max'=>static::$pageTitleLength],
            [$this->getSeoKeywordsAttribute(), 'length', 'max'=>static::$metaKeywordsLength],
            [$this->getSeoDescAttribute(), 'length', 'max'=>static::$metaDescLength],
            [$this->getSeoDescAttribute(), 'match', 'pattern'=>'/^[A-Za-z0-9- ,.]+$/', 'message'=>Sii::t('sii','Meta tag description accepts only alphabet letters, digits or hypen.')],
        ];
    }    
    /**
     * Declares attribute labels.
     */
    public function seoAttributeLabels()
    {
        return [
            'seo' => Sii::t('sii','Search Engine Optimization'),
            'seoTitle' => Sii::t('sii','SEO Page Title'),
            'seoDesc' => Sii::t('sii','SEO Page Description'),
            'seoKeywords' => Sii::t('sii','SEO Page Keywords'),
        ];
    }    
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function seoAttributeToolTips()
    {
        return [
            $this->getSeoTitleAttribute()=>Sii::t('sii','Title tags are often used on search engine results pages (SERPs) to display preview snippets for a given page, and are important both for SEO and social sharing.'),
            $this->getSeoDescAttribute()=>Sii::t('sii','Please provide concise explanations of the content of your page. Meta descriptions are commonly used on search engine result pages (SERPs) to display preview snippets.'),
            $this->getSeoKeywordsAttribute()=>Sii::t('sii','Please separate keywords by commas (,)'),
        ];
    }    
    /**
     * Load params attributes from model instance 
     */
    public function loadSeoParamsAttributes()
    {
        foreach ($this->modelInstance->metaTagAttributes as $field => $value) {
            if (property_exists($this, $field))
                $this->$field = $value;
        }
    }
    /**
     * Return page seo related attributes into params array
     * @return array ['field']=>'value']
     */
    public function getSeoParams()
    {
        $params = [];
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            if (substr($prop->getName(), 0, strlen(static::$suffixSeoAttribute))==static::$suffixSeoAttribute){//pickup all attributes start with the seo suffix
                $params[$prop->getName()] = $this->{$prop->getName()};
            } 
        }  
        logTrace(__METHOD__,$params);
        return $params;
    }
    /**
     * @return string json encoded seo params string
     */
    public function getSeoParamsString()
    {
        return json_encode($this->seoParams);
    }    
    /**
     * Check if user has SEO configurator
     * This is linked back to shop subscription
     * @return bool
     */
    public function hasSEOConfigurator()
    {
        return Subscription::apiHasService(Feature::$hasSEOConfigurator, ['shop'=>$this->{$this->getSeoShopAttribute()}]);
    }    
}
