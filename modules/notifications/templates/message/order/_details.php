<table>
    <tr>
        <td><?php echo Sii::t('sii','Order No');?></td>
        <td><?php echo CHtml::link($model->order_no,Notification::getActionUrl($model,isset($domain)?$domain:null));?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Purchase Date');?></td>
        <td><?php echo $model->formatDateTime($model->create_time,true);?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Price');?></td>
        <td><?php echo $model->formatCurrency($model->grand_total);?></td>
    </tr>
    <?php if (isset($showPaymentMethodName)):?>
    <tr>
        <td><?php echo Sii::t('sii','Payment Method');?></td>
        <td><?php echo $model->getPaymentMethodName(user()->getLocale());?></td>
    </tr>
    <?php endif;?>
    <tr>
        <td><?php echo Sii::t('sii','Status');?></td>
        <td><?php echo Process::getHtmlDisplayText($model->status);?></td>
    </tr>
    <?php if (isset($showPaymentMethodDesc)):?>
    <tr>
        <td colspan="2">
            <div>
                <?php echo $model->getPaymentMethodModel()->getDescription(user()->getLocale());?>
            </div>
        </td>
    </tr>
    <?php endif;?>
</table>

