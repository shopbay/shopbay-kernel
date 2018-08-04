<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=> 'signup_page',
    'heading'=> array(
        'name'=> isset($activated)?Sii::t('sii','Your account is already activated.'):Sii::t('sii','Thanks for signing up {service}',array('{service}'=>app()->name)),
        'image'=>'<i class="fa fa-check-circle fa-fw" style="color:limegreen;font-size:2em;"></i>',
    ),
    'layout'=>false,
    'linebreak'=>false,
    'loader'=>false,
    'body'=>isset($activated)?'':$this->renderPartial('_complete_body',array('email'=>$email),true),
));
