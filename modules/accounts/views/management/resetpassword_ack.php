<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=> 'password_reset_ack_page',
    'heading'=> array(
        'name'=> Sii::t('sii','Password is reset successfully.'),
    ),
    'layout'=>false,
    'linebreak'=>false,
    'loader'=>false,
    'body'=>Sii::t('sii','A new password is sent to your email <em>{email}</em>',array('{email}'=>$email)),
));
