<?php 

if ($this->layout){
    $this->widget('SPageLayout',$this->getPageLayoutColumns());
}
else {
    $this->render('_page');
}
