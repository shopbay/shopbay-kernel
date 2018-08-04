<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Linked Accounts'),
);

$this->widget('common.widgets.spage.SPage',array(
    'id'=>'account_page',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> Sii::t('sii','Linked Accounts to Social Networks'),
        'superscript'=>null,
        'subscript'=>null,
    ),    
    'description'=> Sii::t('sii','You can link your social network accounts to {app}, which enables you to be able to login {app} by using social network account. If any of the social networks below is shown with locked icon, it means that you are currently connected to it.',array('{app}'=>Yii::app()->name)),
    'body'=>$this->allowOAuth?$this->widget('common.modules.accounts.oauth.widgets.OAuthNetworks',array(),true):'',
    'sidebars' => $this->getProfileSidebar(user()->getAccountMenu(),SPageLayout::WIDTH_15PERCENT),
));