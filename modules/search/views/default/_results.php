<?php   
if (isset($dataProvider)){            
    $this->widget('SListView', array(
        'ajaxUpdate'=>$this->getAjaxUpdateId(),
        'dataProvider'=>$dataProvider,
        'itemView'=>$this->getItemView(),
    )); 
}
