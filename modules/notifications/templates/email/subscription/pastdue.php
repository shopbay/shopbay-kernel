<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Subscription is due for payment');?></span>
                 </td>
             </tr>
         </tbody>   
    </table>
    
    <div style="margin-top:10px;">

        <p><?php echo Sii::t('sii','We attempted to charge the card you have on file but we were unable to do so. We will automatically attempt to charge your card again within 5 working days');?></p>

        <p><?php echo Sii::t('sii','Please login to confirm that your billing profile and create card are up to date and accurate.');?></p>

    </div>

    <div style="margin-top:10px;">

        <p><?php echo Sii::t('sii','Please note that you have to make payment by {dunning_date}, else your account will be suspended and you will not be able to continue using our services.',array('{dunning_date}'=>$model->dunningDate));?></p>

    </div>

    <?php $this->renderPartial('common.modules.notifications.templates.email.subscription._subscription',array('model'=>$model));?>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',array(
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model),
        )); 
    ?>

</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>