<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('zii.widgets.CListView');
Yii::import('common.widgets.SInfiniteScroll');
/**
 * Description of SListView
 * Support render keys as optional (default enable)
 * Customize summaryText
 * 
 * @author kwlok
 */
class SListView extends CListView 
{
    public $enableRenderKeys = true;
    public $cssFile = false;
    public $pagerCssClass = 'spager';
    public $summaryText;    
    public $template = '{summary}{items}{pager}';    
    public $infiniteScroll;  
    public $showViewOptions = true;    
    public $viewOptionRoute;    
    public $extendedSummaryText;    
    public $securePagination = false;
    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate {@link columns} objects.
     */
    public function init()
    {
        parent::init();
        if (isset($this->infiniteScroll)){
            $isPager = new SInfiniteScroll($this->infiniteScroll);
            $isPager->pagerSelector = '.'.$this->pagerCssClass;
            $isPager->https = Yii::app()->request->isSecureConnection;
            $this->pager = $isPager->getPager();
        }
        else {
            $this->pager = self::getPagerBoilerplate($this->securePagination);
        }
        if (!isset($this->summaryText))
            $this->summaryText = self::getSummaryTextBoilerplate($this->dataProvider->getItemCount(),$this->viewOptionRoute,$this->showViewOptions);
        if (isset($this->extendedSummaryText))
            $this->summaryText .= $this->extendedSummaryText;
        
        if (!isset($this->emptyText))
            $this->emptyText = Sii::t('sii','No results found.');
    }
    /**
     * Renders the view.
     * This is the main entry of the whole view rendering.
     * Child classes should mainly override {@link renderContent} method.
     */    
    public function run() 
    {
        $this->registerClientScript();

        echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

        $this->renderContent();

        if ($this->enableRenderKeys)
            $this->renderKeys();

        echo CHtml::closeTag($this->tagName);
    }
  
    public static function getSummaryTextBoilerplate($itemCount,$viewOptionRoute=null,$showViewOption=true)
    {
        $boilerplate = Sii::t('sii','<span style="color:red">{start}</span> - <span style="color:red">{end}</span> of <span style="color:red">{count}</span>');
        $summary = Sii::t('sii','Displaying {boilerplate} record|Displaying {boilerplate} records',array($itemCount,'{boilerplate}'=>$boilerplate));        
        if ($showViewOption && isset($viewOptionRoute))
            $summary .= '<span class="extendedSummary"> | '.self::getViewOptionsBoilerplate($viewOptionRoute).'</span>';
        return $summary;
    }

    public static function getViewOptionsBoilerplate($viewOptionRoute)
    {
        $output = CHtml::openTag('ul',array('class'=>'view-options'));
            foreach (SPageIndex::getViewOptions() as $key => $value) {
                $output .= '<li>'
                     .CHtml::link($value==SPageIndex::VIEW_GRID?'<i class="fa fa-table"></i>':'<i class="fa fa-list-ul"></i>', 
                                 url($viewOptionRoute,array('option'=>$value)),
                                 array('title'=>$value==SPageIndex::VIEW_GRID?Sii::t('sii','Grid View'):Sii::t('sii','List View')))
                     .'</li>';
            }
        $output .= "</ul>\n";
        return $output;
    }
    
    public static function getPagerBoilerplate($https=false)
    {
        return [
            'class' => 'common.widgets.SLinkPager', 
            'header'=>'',
            'nextPageLabel'=>Sii::t('sii','Next'),
            'prevPageLabel'=>Sii::t('sii','Previous'),
            'firstPageLabel'=>Sii::t('sii','First Page'),
            'lastPageLabel'=>Sii::t('sii','Last Page'),
            'cssFile'=>false,
            'https'=>$https,
        ];
    }
    /**
     * Register required client javascript lib
     */
    public function registerJsScript() 
    {
        $cs=Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('bbq');
        if($this->enableHistory)
            $cs->registerCoreScript('history');
        if($this->baseScriptUrl===null)
            $this->baseScriptUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/listview';
        $cs->registerScriptFile($this->baseScriptUrl.'/jquery.yiilistview.js',CClientScript::POS_END);
    }

}