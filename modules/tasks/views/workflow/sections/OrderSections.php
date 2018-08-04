<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.views.workflow.sections.WorkflowSections');
/**
 * Description of OrderSections
 *
 * @author kwlok
 */
class OrderSections extends WorkflowSections
{
    public function prepareData()
    {
        if ($this->model->orderOnHold() || $this->model->orderPendingVerified()){
            //section 1: Order Summary
            $this->addSummary();
            $this->addProducts();
            $this->addShippingMethod();
            $this->addShippingAddress();
        }

        if ($this->model->orderPendingVerified()){
            $this->addPaymentRecord();
            $this->addAttachments();
        }
        
        $this->addProcessHistory();
    }
    
    public function addSummary()
    {
        $this->add(array('id'=>'summary-order','name'=>Sii::t('sii','Order Summary'),'heading'=>true,'top'=>true,
                            'viewFile'=>$this->orderSummaryView,'viewData'=>array('dataProvider'=>$this->model->search(),'customer'=>true)));
    }
    
    public function addProducts()
    {
        $itemViewParams = array('dataProvider'=>$this->model->searchItems(),'purchaserInfoInvisible'=>true,'customer'=>true);
        $this->add(array('id'=>'products','name'=>Sii::t('sii','Purchased Items'),'heading'=>true,
                             'viewFile'=>$this->itemsView,'viewData'=>$itemViewParams));
    }
    
    public function addShippingMethod($dataProvider=null)
    {
        if (!isset($dataProvider))
            $dataProvider = $this->model->searchShippings();
        
        $this->add(array('id'=>'shipping_method','name'=>Sii::t('sii','Shipping Method'),'heading'=>true,
                             'viewFile'=>$this->controller->module->getView('orders.merchantshipping'),
                             'viewData'=>array('dataProvider'=>$dataProvider)));
    }
    
    public function addShippingAddress()
    {
        $this->add(array('id'=>'address','name'=>Sii::t('sii','Shipping Address'),'heading'=>true,
                             'viewFile'=>$this->controller->module->getView('orders.merchantaddress'),
                             'viewData'=>array('dataProvider'=>$this->model->searchShippingAddress())));
    }
    
    public function addPaymentRecord($top=false,$viewFile=null)
    {
        if (!isset($viewFile))
            $viewFile = $this->controller->module->getView('orders.merchantpayment');
        $this->add(array('id'=>'payment','name'=>Sii::t('sii','Payment Record'),'heading'=>true,'top'=>$top,
                     'viewFile'=>$this->controller->module->getView('orders.merchantpayment'),
                     'viewData'=>array('dataProvider'=>$this->model->searchPayments())));
    }
    
    public function addAttachments()
    {
        $this->add(array('id'=>'attachment','name'=>Sii::t('sii','Supporting Documents'),'heading'=>true,
                     'viewFile'=>$this->controller->module->getView('orders.merchantattachment'),
                     'viewData'=>array('dataProvider'=>$this->model->getAttachments())));
    }
    
    public function addProcessHistory()
    {
        $this->add(array('id'=>'history','name'=>Sii::t('sii','Process History'),'heading'=>true,
                 'viewFile'=>$this->controller->module->getView('orders.merchanthistory'),
                 'viewData'=>array('dataProvider'=>$this->model->searchTransition($this->model->id))));
    }
}
