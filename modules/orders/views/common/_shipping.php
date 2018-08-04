<?php 
$this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'shipping_method_grid',
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',
    'columns'=>array(
        array(
           'header'=>Sii::t('sii','Name'),
           'value'=>'$data->getShippingName(user()->getLocale())',
           'htmlOptions'=>array('style'=>'text-align:center;width:30%'),
           'type'=>'html',
        ),
        array(
           'header'=>Sii::t('sii','Method'),
           'value'=>'$data->getShippingMethodDesc()',
           'htmlOptions'=>array('style'=>'text-align:center;width:20%'),
           'type'=>'html',
        ),
        array(
           'header'=>Sii::t('sii','Type'),
           'value'=>'$data->getShippingTypeDesc()',
           'htmlOptions'=>array('style'=>'text-align:center;width:20%'),
           'type'=>'html',
        ),
        array(
           'header'=>Sii::t('sii','Total Fee'),
           'value'=>'$data->formatCurrency($data->getShippingRate())',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
           'type'=>'html',
        ),
        array(
           'header'=>Sii::t('sii','Total Surcharge'),
           'value'=>'$data->formatCurrency($data->getShippingSurcharge())',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
           'type'=>'html',
        ),
   ),
));
        