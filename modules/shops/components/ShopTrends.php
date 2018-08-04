<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopTrends
 *
 * @author kwlok
 */
trait ShopTrends 
{
    /*
     * Trend topic if any; Default is TREND_RECENTLIKED
     */
    public $trendTopic = ShopPage::TREND_RECENTLIKED;
    public $trendViewContainer = '_trends';
    public $trendViewList      = '_trends_listing';
    private $_dp;//data provider
    
    public function getTrendsData()
    {
        $shopTheme = $this->shopModel->themeModel;
        if ($shopTheme==null)
            $shopTheme = $this->shopModel->getThemeModel(null);//set status to null to pick up the first offline theme (shop should be using default theme, and not yet published as LIVE) 
        return [
            'view'=>$this->controller->getThemeView($this->trendViewContainer),
            'data'=>[
                'topic'=>$this->trendTopic,
                'page'=>$this,
                'theme'=>$shopTheme,
                'dataProvider'=>$this->trendsDataProvider,
            ],
        ];
    }
    
    public function getTrendsDataProvider()
    {
        if (!isset($this->_dp)){
            switch ($this->trendTopic) {
                case ShopPage::TREND_MOSTLIKED:
                    $this->_dp = $this->model->searchMostLikedProducts();
                    break;
                case ShopPage::TREND_RECENTPURCHASED:
                    $this->_dp = $this->model->searchRecentPurchasedProducts();
                    break;
                case ShopPage::TREND_MOSTPURCHASED:
                    $this->_dp = $this->model->searchMostPurchasedProducts();
                    break;
                case ShopPage::TREND_RECENTDISCUSSED:
                    $this->_dp = $this->model->searchRecentDiscussedProducts();
                    break;
                case ShopPage::TREND_MOSTDISCUSSED:
                    $this->_dp = $this->model->searchMostDiscussedProducts();
                    break;
                case ShopPage::TREND_RECENTLIKED://default trend
                default:
                    $this->_dp = $this->model->searchRecentLikedProducts();
                    break;
            }
        }
        $this->_dp->pagination = $this->getPagination('storefront/trend', ['shop'=>$this->model->id,'idx'=>$this->trendTopic]);
        return $this->_dp;
    }         
    
    public function getTrendsMenu($containerSelection='.trends-container',$viewFile=null)
    {
        $route = 'trend'.static::pageIdSuffix();//need suffix to get it trimmed away due to getUrl() method
        $params = ['shop'=>$this->shopModel->id,'container'=>$containerSelection,'view'=>isset($viewFile)?$viewFile:$this->trendViewList];//common params
        return [ 
            [
                'label'=>Sii::t('sii','Recent Likes'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_RECENTLIKED,
                'itemOptions'=> ['class'=>ShopPage::TREND_RECENTLIKED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_RECENTLIKED])).'")'],
            ],
            [
                'label'=>Sii::t('sii','Most Likes'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_MOSTLIKED,
                'itemOptions'=>['class'=>ShopPage::TREND_MOSTLIKED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_MOSTLIKED])).'")'],
            ],
            [
                'label'=>Sii::t('sii','Recent Purchased'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_RECENTPURCHASED,
                'itemOptions'=>['class'=>ShopPage::TREND_RECENTPURCHASED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_RECENTPURCHASED])).'")'],
            ],
            [
                'label'=>Sii::t('sii','Most Purchased'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_MOSTPURCHASED,
                'itemOptions'=>['class'=>ShopPage::TREND_MOSTPURCHASED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_MOSTPURCHASED])).'")'],
            ],
            [
                'label'=>Sii::t('sii','Recent Discussed'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_RECENTDISCUSSED,
                'itemOptions'=>['class'=>ShopPage::TREND_RECENTDISCUSSED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_RECENTDISCUSSED])).'")'],
            ],
            [
                'label'=>Sii::t('sii','Most Discussed'),
                'url'=>'javascript:void(0);',
                'active'=>$this->trendTopic==ShopPage::TREND_MOSTDISCUSSED,
                'itemOptions'=>['class'=>ShopPage::TREND_MOSTDISCUSSED],
                'linkOptions'=> ['onclick'=>'trendview("'.$this->getUrl($route, array_merge($params,['topic'=>ShopPage::TREND_MOSTDISCUSSED])).'")'],
            ],
        ];
    }    
}
