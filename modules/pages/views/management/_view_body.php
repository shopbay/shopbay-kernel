<?php 
$this->widget('common.widgets.SDetailView', [
    'data'=>$model,
    'attributes'=>[
        ['name'=>'create_time','value'=>$model->formatDatetime($model->create_time,true)],
        ['name'=>'update_time','value'=>$model->formatDatetime($model->update_time,true)],
        ['name'=>'slug','type'=>'raw','value'=>$model->getUrl(),'visible'=>$model->updatable()],
    ],
]);

if ($model->updatable()){
    $this->widget('common.widgets.SDetailView', [
        'id'=>'seo_section',
        'data'=>$model,
        'attributes'=>[
            ['label'=>$model->getAttributeLabel('seoTitle'),'type'=>'raw','value'=>$model->getMetaTag('seoTitle')],
            ['label'=>$model->getAttributeLabel('seoDesc'),'type'=>'raw','value'=>$model->getMetaTag('seoDesc')],
            ['label'=>$model->getAttributeLabel('seoKeywords'),'type'=>'raw','value'=>$model->getMetaTag('seoKeywords')],
        ],
    ]);
}

$model->languageForm->renderForm($this,Helper::READONLY);


