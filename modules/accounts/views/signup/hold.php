<?php
$message = CHtml::tag('h1',[],Sii::t('sii','Sign up is currently unavailable.'));
$message .= CHtml::tag('p',[],Sii::t('sii','Thanks for your interest with {app}.',['{app}'=>app()->name]));
$message .= CHtml::tag('p',[],Sii::t('sii','Due to overwhelming responses, our server has reached maximum capacity it can handle. We are currently upgrading our server.'));
$message .= CHtml::tag('p',[],Sii::t('sii','You may drop us a {message} so that we can contact you when sign up is open again.',['{message}'=>CHtml::link(Sii::t('sii','message'),url('contact'))]));
$this->widget('common.widgets.spage.SPage',[
    'id'=>'signup_page',
    'heading'=> false,
    'body'=>$message,
]);

