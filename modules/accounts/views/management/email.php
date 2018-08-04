<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Change Email'),
);

$this->menu=array(
    array('id'=>'account','title'=>Sii::t('sii','Manage Account'),'subscript'=>Sii::t('sii','account'), 'url'=>url('account')),
    array('id'=>'password','title'=>Sii::t('sii','Change Password'),'subscript'=>Sii::t('sii','password'), 'url'=>url('account/management/password'),'visible'=>user()->isAuthorizedActivated),
    array('id'=>'email','title'=>Sii::t('sii','Change Email'),'subscript'=>Sii::t('sii','email'), 'url'=>url('account/management/email'),'linkOptions'=>array('class'=>'active')),
);


$this->widget('common.widgets.spage.SPage',array(
    'id'=>'account_page',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => $this->loadWizards(get_class($form),user()),
    'heading'=> array(
        'name'=> user()->getEmail(),
        'superscript'=>null,
        'subscript'=>null,
    ),
    'body'=>$this->renderPartial('_email_form',array('model'=>$form),true),
    'sidebars' => $this->getProfileSidebar(user()->getAccountMenu(),SPageLayout::WIDTH_15PERCENT),
));
