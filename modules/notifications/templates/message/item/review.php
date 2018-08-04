<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Review Comment:');?>
</p>
<?php if ($model->getReview()!=null):?>
<table style="width:500px;">
    <tr>
        <td>
            <blockquote>
                <?php echo $model->getReview()->content;?>
            </blockquote>
        </td>
    </tr>
</table>
<?php endif;?>

<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
    ]); 
?>

<?php $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model,app()->urlManager->merchantDomain),
                'orderUrl'=>Notification::getActionUrl($model->order,app()->urlManager->merchantDomain))
            );