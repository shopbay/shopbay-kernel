<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SInfiniteScroll
 * Wrapper of common.extensions.infiniteScroll.IasPager
 * 
 * @author kwlok
 */
class SInfiniteScroll extends CComponent
{    
    public $rowSelector;
    public $listViewId;
    public $pagerSelector;
    public $onRenderComplete;
    public $https;
    
    public function __construct($config) 
    {
        foreach ($config as $key => $value) {
            $this->{$key}=$value;    
        }
    }
    
    public function getPager()
    {
        return [
            'class' => 'common.extensions.infiniteScroll.IasPager', 
            'loadLib'=>false, 
            'https'=>$this->https, 
            'rowSelector'=>$this->rowSelector, 
            'listViewId' =>$this->listViewId, 
            'header' => '',
            'pagerSelector' => $this->pagerSelector,
            'loaderText'=>'<i class="fa fa-circle-o-notch fa-spin"></i>',
            'options' => [
                'history' => false, 
                'triggerPageTreshold' => 2, 
                'trigger'=>Sii::t('sii','Load more'),
                'onRenderComplete'=>'js:function(items){'.$this->onRenderComplete.'}',
            ],
        ];
    }
}
