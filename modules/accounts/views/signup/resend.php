<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=> 'signup_page',
    'heading'=> array(
        'name'=> Sii::t('sii','Activation Key Regenerated'),
    ),
    'layout'=>false,
    'linebreak'=>false,
    'loader'=>false,
    'body'=>$this->renderPartial('_resend_body',array('email'=>$email),true),
));