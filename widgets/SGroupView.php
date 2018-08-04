<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.SListView');
Yii::import('common.extensions.groupgridview.GroupGridView');
/**
 * Description of SGroupView
 *
 * @author kwlok
 */

class SGroupView extends GroupGridView 
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

}
