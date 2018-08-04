<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Subscription Suspended');?></span>
                 </td>
             </tr>
         </tbody>   
    </table>

    <div style="margin-top:10px;">

        <p><?php echo Sii::t('sii','You have failed to make payment for your subscription within {days} days after your subscription is due for payment.',array('{days}'=>Config::getSystemSetting('subscription_dunning_days')));?></p>

        <p><?php echo Sii::t('sii','As a result, your subscription is automatically suspended and you are not be able to use our service anymore. If you wish to resume your subscription, please write to us at {email}.',array('{email}'=>Config::getSystemSetting('email_contact')));?></p>

    </div>

    <?php $this->renderPartial('common.modules.notifications.templates.email.subscription._subscription',array('model'=>$model));?>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',array(
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model),
        )); 
    ?>

</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>