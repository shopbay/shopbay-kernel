<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model]);?>

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Unsuccessful Payment Verification');?></span>
                     <div style="padding:5px 10px;margin:5px 10px;border: 2px dashed lightgrey;float: left;width:45%">
                            <?php echo Sii::t('sii','Notes:');?>
                            <ul style="padding-left:15px">
                                <li><?php echo Sii::t('sii','We are unable to verify your payment by <strong>{payment_method}</strong>.',['{payment_method}'=>$model->getPaymentMethodName(user()->getLocale())]);?></li>
                                <?php if ($model->getTransitionCondition()!=null):?>
                                    <li><?php echo $model->getTransitionCondition()->message;?></li>
                                <?php endif;?>
                                <li><?php echo Sii::t('sii','You may want to call your bank to check the possible causes');?></li>
                                <li><?php echo Sii::t('sii','Or, if your payment amount is not correct, you can repay the order.');?></li>
                            </ul>
                     </div>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order',['model'=>$model]); ?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',[
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model),
        ]); 
    ?>
    
</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.order._shop_footer',['shop'=>$model->shop]);