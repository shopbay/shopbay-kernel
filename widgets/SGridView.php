<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('zii.widgets.grid.CGridView');
Yii::import('common.widgets.SListView');
/**
 * Description of SGridView
 * 
 * @author kwlok
 */
class SGridView extends CGridView 
{
    public $securePagination = false;
    public $cssFile = false;
    public $pagerCssClass = 'spager';
    public $summaryText;    
    public $template = '{summary}{items}{pager}';
    public $viewOptionRoute;

    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate {@link columns} objects.
     */
    public function init()
    {
        parent::init();
        $this->pager = SListView::getPagerBoilerplate($this->securePagination);
        if (!isset($this->summaryText))
            $this->summaryText = SListView::getSummaryTextBoilerplate($this->dataProvider->getItemCount(),$this->viewOptionRoute);
        $this->emptyText = Sii::t('sii','No results found.');
    }
    
    /*
     * Overridden method
     * This method is required as CGridView set title to request()->getUrl() which is not pointing to controller/page ..
     * because referrer url is controller/index..
     * 
     */
    public function renderKeys()
    {
//        $myController = app()->getController()->getModule();
//        if ($myController==null)
//            $myController = app()->getController()->id;
//        else{
//            $myController = app()->getController()->getModule()->id;
//            $myController .= '/'.app()->getController()->id;
//        }
//        
        echo CHtml::openTag('div',array(
            'class'=>'keys',
            'style'=>'display:none',
            'title'=>'/'.app()->getController()->uniqueId.'/index?controller='.app()->getController()->id,
        ));
        foreach($this->dataProvider->getKeys() as $key)
            echo "<span>".CHtml::encode($key)."</span>";
        echo "</div>\n";
    }    
    
}