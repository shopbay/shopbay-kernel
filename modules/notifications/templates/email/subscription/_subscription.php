<table style="width:300px;">
    <tr>
        <td><?php echo $model->getAttributeLabel('plan_id');?></td>
        <td><?php echo $model->name;?></td>
    </tr>
    <tr>
        <td><?php echo $model->getAttributeLabel('subscription_no');?></td>
        <td><?php echo $model->subscription_no;?></td>
    </tr>
    <tr>
        <td><?php echo $model->getAttributeLabel('start_date');?></td>
        <td><?php echo $model->start_date;?></td>
    </tr>
    <tr>
        <td><?php echo $model->getAttributeLabel('end_date');?></td>
        <td><?php echo $model->end_date;?></td>
    </tr>
    <?php if ($model->plan->isRecurringCharge):?>
        <tr>
            <td><?php echo Sii::t('sii','Renewal');?></td>
            <td><?php echo $model->plan->recurringDesc;?></td>
        </tr>
    <?php endif;?>
</table>