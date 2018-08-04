<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=>'signup_page',
    'heading'=> false,
    'body'=>$this->renderPartial('_form',['model'=>$model,'nonAjax'=>true],true),
));
