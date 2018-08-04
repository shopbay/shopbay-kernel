<?php
if ($success){

    if (isset($form->email)){
        $message = Sii::t('sii','Email is changed successfully. You are required to re-activate your account at your next login.');
        $message .= '<br>'.Sii::t('sii','A new activation token key is sent to your new mailbox <em>{email}</em>',array('{email}'=>$form->email));
    }


    $this->widget('common.widgets.spage.SPage',array(
        'id'=>'password_reset_page',
        'flash'  => get_class($form),
        'heading'=> array(
            'name'=> Sii::t('sii','You have completed account setup. '),
        ),
        'linebreak'=>false,
        'body'=>isset($message)?$message:'',
    ));
}
else {
    $this->widget('common.widgets.spage.SPage',array(
        'id'=>'password_reset_page',
        'flash'  => get_class($form),
        'heading'=> array(
            'name'=> Sii::t('sii','Please change your account password').(user()->isSuperuser? Sii::t('sii',' and email'):''),
            'superscript'=>null,
            'subscript'=>null,
        ),
        'body'=>$this->renderPartial('_password_reset_form',['model'=>$form,'showEmailField'=>user()->isSuperuser],true),
    ));
}
