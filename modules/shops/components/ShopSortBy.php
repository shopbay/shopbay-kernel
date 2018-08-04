<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopSortBy
 *
 * @author kwlok
 */
trait ShopSortBy 
{
    /*
     * Default sort by; Default is SORTBY_NAME_A_Z
     */
    public $sortby = ShopPage::SORTBY_NAME_A_Z;
    public $sortbyBaseurl;
    
    public function getSortByCriteria()
    {
        $criteria = new CDbCriteria();
        $criteria->order = $this->getSortByCondition();
        return $criteria;
    }      
    
    public function getSortByCondition()
    {
        $condition = '';
        switch ($this->sortby) {
            case ShopPage::SORTBY_PRICE_L_H:
                $condition = 't.unit_price ASC';
                break;
            case ShopPage::SORTBY_PRICE_H_L:
                $condition = 't.unit_price DESC';
                break;
            case ShopPage::SORTBY_DATE_O_N:
                $condition = 't.create_time ASC';
                break;
            case ShopPage::SORTBY_DATE_N_O:
                $condition = 't.create_time DESC';
                break;
            case ShopPage::SORTBY_NAME_Z_A:
                $condition = 't.name DESC';
                break;
            case ShopPage::SORTBY_NAME_A_Z:
            default:
                $condition = 't.name ASC';
                break;
        }
        return $condition;
    }         
    
    public function getSortBySelect($id='products_sort_by')
    {
        $htmlOptions['options'] = [];//option tag html settings, if any
        $options = [
            ShopPage::SORTBY_NAME_A_Z => Sii::t('sii','Alphabetically, A-Z'),
            ShopPage::SORTBY_NAME_Z_A => Sii::t('sii','Alphabetically, Z-A'),
            ShopPage::SORTBY_PRICE_L_H => Sii::t('sii','Price, low to high'),
            ShopPage::SORTBY_PRICE_H_L => Sii::t('sii','Price, high to low'),
            ShopPage::SORTBY_DATE_O_N => Sii::t('sii','Date, old to new'),
            ShopPage::SORTBY_DATE_N_O => Sii::t('sii','Date, new to old'),
        ];
        $select = '<select id="'.$id.'">';
        $select .= CHtml::listOptions($this->sortby, $options, $htmlOptions);//last array is html options
        $select .= '</select>';
        $this->loadSortByScript($id);
        return $select;
    }
    
    public function loadSortByScript($id='products_sort_by')
    {
        $this->sortbyBaseurl = $this->hasFilter ? $this->filter->getUrl() : $this->url;
        
        $url = $this->appendExtraQueryParams($this->sortbyBaseurl,['filter'=>'true']);//add a dummy param to make the url below safe (as we are adding a &)
        
        $script = <<<EOJS
$('#$id').change(function(){
    var url = '$url&sort_by='+$(this).val();
    window.location.href = url;
});
EOJS;
        Helper::registerJs($script,__CLASS__.$this->id);
    }    
}

