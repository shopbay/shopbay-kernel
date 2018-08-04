<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=>'password_reset_page',
    'heading'=> false,
    'body'=>$this->renderPartial('_resetpassword_form',['model'=>$model],true),
));
