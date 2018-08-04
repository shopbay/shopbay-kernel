<?php 
$this->breadcrumbs=array(
	Sii::t('sii','Account')=>url('account/profile'),
	Sii::t('sii','Comments')=>url('comments'),
	Sii::t('sii','View'),
);

$this->menu=array(
    array('id'=>'update','title'=>Sii::t('sii','Update Comment'),'subscript'=>Sii::t('sii','update'), 'url'=>array('update', 'id'=>$model->id),'visible'=>$model->updatable()),
    array('id'=>'delete','title'=>Sii::t('sii','Delete Comment'),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(), 
            'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
                                 'onclick'=>'$(\'.page-loader\').show();',
                                 'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',array('{object}'=>strtolower($model->displayName()))))),
);

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> $model->getTarget()->displayLanguageValue('name',user()->getLocale()),
        'image'=> $model->getTarget()->getImageThumbnail(Image::VERSION_XSMALL),
        'tag'=> null,
        'superscript'=>null,
        'subscript'=>$model->formatDateTime($model->create_time,true),
    ),
    'body'=>$this->renderPartial('_view_body',array('model'=>$model),true),
));