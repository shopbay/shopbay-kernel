<?php $this->module->registerFormCssFile();?>
<?php $this->module->registerChosen();?>
<?php $this->module->registerMediaGalleryAssets();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Profile'),
);
$this->menu=array(
    array('id'=>'account','title'=>Sii::t('sii','Manage Account'),'subscript'=>Sii::t('sii','account'), 'url'=>url('account'),'linkOptions'=>array('class'=>'active')),
    array('id'=>'password','title'=>Sii::t('sii','Change Password'),'subscript'=>Sii::t('sii','password'), 'url'=>url('account/management/password'),'visible'=>user()->isAuthorizedActivated),
    array('id'=>'email','title'=>Sii::t('sii','Change Email'),'subscript'=>Sii::t('sii','email'), 'url'=>url('account/management/email'),'visible'=>user()->isAuthorizedActivated),
);

$this->widget('common.widgets.spage.SPage',array(
    'id'=>'account_page',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash' => get_class($model),
    'heading'=> array(
        'name'=> $model->account->name,
        'superscript'=>null,
//        'subscript'=>Sii::t('sii','Last update time {datetime}',array('{datetime}'=>$model->formatDatetime($model->update_time,true))),
    ),
    'body'=>$this->renderPartial('_profile_body',array('model'=>$model->account),true),
    'sidebars' => $this->getProfileSidebar(user()->getAccountMenu(),SPageLayout::WIDTH_15PERCENT),
));