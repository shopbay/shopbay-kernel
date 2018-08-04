<div class="orders">
    <?php
        $this->widget($this->getModule()->getClass('listview'), 
            array(
                'id'=>'orders',
                'showViewOptions'=>false,
                'extendedSummaryText'=>$this->getOrderExtendedSumary(),
                'dataProvider'=>$this->getRecentOrders(),
                'itemView'=>$this->modelView,
            ));
    ?>    
</div>
