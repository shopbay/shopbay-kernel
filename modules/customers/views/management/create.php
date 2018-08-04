<?php $this->getModule()->registerFormCssFile();?>
<?php
$this->breadcrumbs=array(
	Sii::t('sii','Customers')=>url('customers'),
	Sii::t('sii','Create'),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name' => Sii::t('sii','Create Customer'),
    ),
    'description'=>Sii::t('sii','You can track also potential customers and keep their profiles for future use.'),
    'body'=>$this->renderPartial('_form', array('model'=>$model,'addressForm'=>$this->getCustomerAddressForm()),true),
));
