<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.wcm.models.WcmPage');
/**
 * Description of WcmContentTrait
 * 
 * This trait read and render wcm page content
 * The page source can be "db" or "file" based
 *
 * @author kwlok
 */
trait WcmContentTrait 
{
    /**
     * Page source; Either "db" or "file", default to "db"
     * 
     * db: content are stored in table s_page
     * file: markdown files located at folder wcm/content
     * 
     * @property string the page source.
     */
    public $_source = 'db';//default to db
    /**
     * Support direct language switching via url
     */
    protected function checkLanguage()
    {
        if (isset($_GET['lang'])){
            $this->setUserLocale($_GET['lang']);
        }
    }
    /**
     * Set page source
     * @param type $source
     */
    public function setPageSource($source)
    {
        $this->_source = $source;
    }
    /**
     * 
     * @return string page source
     */
    public function getPageSource()
    {
        return $this->_source;
    }
    
    public function getContentSource($subject,$locale=null)
    {
        switch ($this->getPageSource()) {
            case 'file':
                return $subject.'.md';
            default:
                return $this->getPageContent($subject, $locale);
        }
    }    
    
    public function getPageContent($subject,$locale=null)
    {
        if (!isset($locale))
            $locale = user()->getLocale();
        
        $cacheContent = WcmPage::getCacheContent($subject);
        if ($cacheContent==null)
            return null;
        else {
            $content = json_decode($cacheContent,true);
            return $content[$locale];
        }
    }
    
    public function getPageSeo($subject,$field)
    {
        $cacheSeo = WcmPage::getCacheSeo($subject);
        if ($cacheSeo==null)
            return null;
        else {
            $seo = json_decode($cacheSeo,true);
            return $seo[$field];
        }
    }    
    /**
     * Render markdown
     */
    public function renderMarkdown($page,$replaceParams=[])
    {
        if ($this->getPageSource()=='file')
            $content = file_get_contents($this->getDocpath().'/'.$page.'.md');
        else
            $content = $this->getContentSource($page);   
                
        $md = new CMarkdown();
        $output = $md->transform($content);
        foreach ($replaceParams as $key => $value) {
            if (is_string($key))
                $output = str_replace('{'.$key.'}', $value, $output);
            else 
                $output = str_replace('{'.$value.'}', param($value), $output);
        }
        echo Helper::purify($output,[
            'Attr.EnableID'=>true,
        ]);
    }
    
    protected function getDocpath($locale=null) 
    {
        if (!isset($locale))
            $locale = user()->getLocale();
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$locale;
    }
    
}
