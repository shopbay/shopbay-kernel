<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerChosen();?>
<?php
$this->breadcrumbs=[
    Sii::t('sii','Customers')=>url('customers'),
    Sii::t('sii','Update'),
];
$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', [
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => [
        'name'=> $model->alias,
        'image'=>$model->getImageThumbnail(),
        'subscript'=>Sii::t('sii','Record since').' '.$model->formatDatetime($model->create_time,false),
        'superscript'=> $model->isRegistered?$model->registeredTag:'',
    ],
    'body'=>$this->renderPartial('_profile',['model'=>$model],true).
            $this->renderPartial('_form', ['model'=>$model,'addressForm'=>$this->getCustomerAddressForm($model)],true),
]);