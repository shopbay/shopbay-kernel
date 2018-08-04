<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SSearch
 *
 * @author kwlok
 */
class SSearch extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.ssearch.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'ssearch';
    /**
     * The search function
     * @var string 
     */
    protected $searchFn = 'ssearch';
    /**
     * The search input placeholder; Default to 'Search'
     * @var string 
     */
    public $placeholder = 'Search';
    /**
     * The search input element id/name; Default to 'ssearch_q'
     * @var string 
     */
    public $inputId = 'ssearch_q';
    /**
     * The query field to send to search server; Default to "query"
     * @var string 
     */
    public $queryField = 'query';
    /**
     * The search server url
     * @var string 
     */
    public $url;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->id = $this->id.'_'.$this->inputId;//make it unique in case multiple instance are loaded at the same time
        $this->render('index');
    }    
    
    public function getSearchScript()
    {
        if (strlen($this->url)>0){
            $uri = Helper::parseUri($this->url);
            if (empty($uri['query']) && substr($this->url, -1)!='?')
                $this->url .= '?';//add ? mark as inside javascript the query param is to be appended
            return $this->searchFn."('$this->inputId','$this->queryField','$this->url')";
        }
        else {
            return '';//nothing
        }
    }
    
}
