<?php
$this->widget('common.widgets.spage.SPage',array(
    'id'=> 'closed_page',
    'heading'=> array(
        'name'=> Sii::t('sii','Your account is closed.'),
        'image'=>'<i class="fa fa-check-circle fa-fw" style="color:limegreen;font-size:2em;"></i>',
    ),
    'layout'=>false,
    'linebreak'=>false,
    'loader'=>false,
    'body'=>Sii::t('sii','Thanks for using {service}',array('{service}'=>param('SITE_NAME'))).
            '<br>'.
            Sii::t('sii','If you ever change your mind, we welcome you back to sign up with us again!'),
));
