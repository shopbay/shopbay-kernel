<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model->order]);?>

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Refund Notice');?></span>
                     <div style="padding:5px 10px;margin:5px 10px;border: 2px dashed lightgrey;float: left;width:60%">
                        <p style="font-size:1.1em">
                            <?php echo Sii::t('sii','Thanks for your support!');?>
                        </p>
                        <table style="width:300px;">
                            <tr>
                                <td><?php echo Sii::t('sii','Refund Date');?></td>
                                <td><?php echo $model->formatDateTime($model->update_time,true);?></td>
                            </tr>
                            <tr>
                                <td><?php echo Sii::t('sii','Refund Amount');?></td>
                                <td><?php echo $model->formatCurrency($model->actualRefundAmount);?></td>
                            </tr>
                        </table>                        
                     </div>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.shop._refundpolicy',['model'=>$model->shop]);?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order',['model'=>$model->order]); ?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',[
            'model'=>$model->order,
            'orderUrl'=>Notification::getActionUrl($model->order),
        ]); 
    ?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>

</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.footer');