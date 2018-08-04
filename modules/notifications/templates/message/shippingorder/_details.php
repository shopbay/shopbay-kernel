<table>
    <tr>
        <td><?php echo Sii::t('sii','Order No');?></td>
        <td><?php echo CHtml::link($model->order_no,Notification::getActionUrl($model->order,app()->urlManager->merchantDomain));?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Shipping No');?></td>
        <td><?php echo $model->shipping_no;?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Shipping Method');?></td>
        <td><?php echo $model->shippingname;?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Purchase Date');?></td>
        <td><?php echo $model->formatDateTime($model->create_time,true);?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Price');?></td>
        <td><?php echo $model->formatCurrency($model->grand_total);?></td>
    </tr>
    <?php if (isset($showRefundDetails)):?>
    <tr>
        <td><?php echo Sii::t('sii','Refund Date');?></td>
        <td><?php echo $model->formatDateTime($model->update_time,true);?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Refund Amount');?></td>
        <td><?php echo $model->formatCurrency($model->actualRefundAmount);?></td>
    </tr>
    <?php endif;?>
    <tr>
        <td><?php echo Sii::t('sii','Status');?></td>
        <td><?php echo Process::getHtmlDisplayText($model->status);?></td>
    </tr>
    
</table>

