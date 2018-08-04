<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.news.models.News');
/**
 * Description of ShopNews
 *
 * @author kwlok
 */
trait ShopNews 
{
    /*
     * News article if any
     */
    public $article;
    /**
     * Set news article
     * @param type $article
     */
    public function setNews($article)
    {
        $this->article = $article;
    }     
    /**
     * @return News article
     */
    public function getNews()
    {
        return $this->shopModel->retrieveNews($this->article);
    }     
    
    public function getNewsDataProvider()
    {
        return $this->shopModel->searchNews(Process::NEWS_ONLINE,Config::getSystemSetting('record_per_page'));
    }     
 
}

