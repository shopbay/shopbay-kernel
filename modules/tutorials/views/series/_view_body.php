<?php 
$this->widget('common.widgets.SDetailView', [
    'data'=>$model,
    'columns'=>[
        [
            ['name'=>'tags','type'=>'raw','value'=>$model->hasTags()?Helper::htmlList($model->parseTags(),['class'=>'tags']):Sii::t('sii','not set')],
            ['name'=>'slug','type'=>'raw','value'=>$model->url.' '.($model->online()?CHtml::link(Sii::t('sii','Go to tutorial series'),$model->url,['class'=>'shortcut-button rounded','target'=>'_blank']):'')],
        ],
        [
            ['name'=>'account_id','type'=>'raw','value'=>$model->account->getAvatar(Image::VERSION_XXSMALL).' '.$model->account->name,'visible'=>user()->hasRole(Role::ADMINISTRATOR)],
            ['name'=>'create_time','value'=>$model->formatDatetime($model->create_time,true)],
            ['name'=>'update_time','value'=>$model->formatDatetime($model->update_time,true)],
        ],
    ],
]);

$this->widget('common.widgets.SDetailView', [
    'id'=>'seo_section',
    'data'=>$model,
    'attributes'=>[
        ['label'=>$model->getAttributeLabel('seoTitle'),'type'=>'raw','value'=>$model->getMetaTag('seoTitle')],
        ['label'=>$model->getAttributeLabel('seoDesc'),'type'=>'raw','value'=>$model->getMetaTag('seoDesc')],
        ['label'=>$model->getAttributeLabel('seoKeywords'),'type'=>'raw','value'=>$model->getMetaTag('seoKeywords')],
    ],
]);
    
$model->languageForm->renderForm($this,Helper::READONLY);

echo CHtml::tag('h2',[],Sii::t('sii','Tutorials'));

$this->widget('common.widgets.SListView', [
            'id' => 'tutorials',
            'dataProvider' => $model->searchTutorials(user()->getLocale()),
            'itemView' => '_tutorial',
//            'template' => '{items}',
        ]);

echo '<br>';