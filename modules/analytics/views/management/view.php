<?php 
$this->widget($this->getModule()->getClass('listview'), array(
            'dataProvider'=> $this->getDashboardMetrics(),
            'template'=>'{items}',
            'itemView'=>'_widget',
            'htmlOptions'=>array('class'=>'dashboard-widgets'),
        ));
