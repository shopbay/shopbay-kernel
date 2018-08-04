<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.views.workflow.sections.OrderSections');
/**
 * Description of ItemSections
 *
 * @author kwlok
 */
class ItemSections extends OrderSections
{
    public function prepareData()
    {
        $this->addItems();
        $this->addOrderSummary();
        $this->addCommonViews();
    }
    
    public function addItems($heading=true)
    {
        if ($heading)
            $this->add(array('id'=>'items','name'=>Sii::t('sii','Purchased Items'),'heading'=>true,'top'=>true,
                             'viewFile'=>$this->itemsView,
                             'viewData'=>array('dataProvider'=>$this->model->search(),'statusColumnInvisible'=>true,'btnColumnInvisible'=>true)));
        else
            $this->add(array('id'=>'items',
                             'viewFile'=>$this->itemsView,
                             'viewData'=>array('dataProvider'=>$this->model->search(),'statusColumnInvisible'=>true,'btnColumnInvisible'=>true)));
    }   
    
    public function addInventory()
    {
        $this->add(array('id'=>'inventory','name'=>Sii::t('sii','Inventory Information'),'heading'=>true,
                         'viewFile'=>$this->controller->module->getView('orders.merchantinventory'),
                         'viewData'=>array('dataProvider'=>$this->model->searchProductInventory())));
    }
    
    public function addOrderSummary()
    {
        $this->add(array('id'=>'summary-order','name'=>Sii::t('sii','Order Summary'),'heading'=>true,
                            'viewFile'=>$this->orderSummaryView,'viewData'=>array('dataProvider'=>$this->model->order->search(),'customer'=>true)));
    }   
    
    public function addRefundSummary()
    {
        $this->add(array('id'=>'refund','viewFile'=>'common.modules.orders.views.merchant._summary_refund',
                              'viewData'=>array('model'=>$this->model)));      
    }   
    
    public function addCommonViews()
    {
        if ( user()->currentRole==Role::MERCHANT){
            //only merchant can view product inventory information
            $this->addInventory();
        }
        
        if ($this->model->shippingOrder!=null)
            $this->addShippingMethod($this->model->shippingOrder->search());
            
        $this->addShippingAddress();
        $this->addAttachments();        
        $this->addProcessHistory();
    }
}
