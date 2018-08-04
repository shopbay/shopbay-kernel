<?php
$this->widget('common.widgets.spage.SPage',[
    'id'=>'login_page',
    'heading'=> false,
    'body'=>$this->renderPartial('_form',['model'=>$model,'nonAjax'=>true],true),
]);
