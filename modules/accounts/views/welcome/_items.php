<div class="items">
    <?php
        $this->widget($this->getModule()->getClass('listview'), 
            array(
                'id'=>'items',
                'showViewOptions'=>false,
                'extendedSummaryText'=>'<span class="extendedSummary"> | '.CHtml::link(Sii::t('sii','Show All'), url('items')).'</span>',
                'dataProvider'=>$this->getRecentItems(),
                'itemView'=>$this->modelView,
            ));
    ?>    
</div>