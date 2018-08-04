<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.views.workflow.sections.OrderSections');
/**
 * Description of ShippingOrderSections
 *
 * @author kwlok
 */
class ShippingOrderSections extends OrderSections
{
    public function prepareData()
    {
        if ($this->model->orderCancelled()){
            $refundableDataProvider = $this->model->searchRefundableItems();
            //section 1: Order Summary
            $this->add(array('id'=>'summary-refundable','name'=>Sii::t('sii','Refund Suggestion'),'heading'=>true,'top'=>true,
                                 'viewFile'=>$this->refundSummaryView,'viewData'=>array('model'=>$this->model,'refundable'=>$this->model->getRefundable($refundableDataProvider))));
            //section 2: Refundable Products
            $this->add(array('id'=>'refund','name'=>Sii::t('sii','Refundable Items'),'heading'=>true,
                                 'viewFile'=>$this->itemsView,'viewData'=>array('dataProvider'=>$refundableDataProvider,
                                                                                'purchaserInfoInvisible'=>true,
                                                                                'btnColumnInvisible'=>true)));
            $this->addShippedProducts();
            $this->addShippingOrderTotal();
        }
        else {
            $this->addProducts();
            //section 2: Order Summary
            $this->add(array('id'=>'summary-order','name'=>Sii::t('sii','Order Summary'),'heading'=>true,
                                 'viewFile'=>$this->orderSummaryView,'viewData'=>array('dataProvider'=>$this->model->search())));
        }
        //common sections
        $this->addShippingMethod($this->model->search());
        $this->addShippingAddress();
        $this->addPaymentRecord();
        $this->addProcessHistory();
    }
    /**
     * OVERRIDDEN
     */
    public function addProducts()
    {
        $dataProvider = $this->model->searchItems();
        $dataProvider->data;//init behavior: need to do this to load data into $dataProvider
        $itemViewParams = array('dataProvider'=>$dataProvider,'purchaserInfoInvisible'=>true);
        $this->add(array('id'=>'products','name'=>Sii::t('sii','Purchased Items'),'heading'=>true,'top'=>true,
                             'viewFile'=>$this->itemsView,'viewData'=>$itemViewParams));
    }
    
    public function addShippedProducts()
    {
        $dataProvider = $this->model->searchShippedItems();
        $dataProvider->data;//init behavior: need to do this to load data into $dataProvider
        $this->add(array('id'=>'shipped','name'=>Sii::t('sii','Shipped Items'),'heading'=>true,
                     'viewFile'=>$this->itemsView,'viewData'=>array('dataProvider'=>$dataProvider,
                                                                    'purchaserInfoInvisible'=>true,
                                                                    'btnColumnInvisible'=>true)));
    }
    
    public function addShippingOrderTotal($top=false)
    {
        $this->add(array('id'=>'total','name'=>Sii::t('sii','Shipping Order Total'),'heading'=>true,'top'=>$top,
                             'viewFile'=>$this->controller->module->getView('orders.merchanttotal'),
                             'viewData'=>array('dataProvider'=>$this->model->search(),'purchaserInfoInvisible'=>true)));
    }
    
}
