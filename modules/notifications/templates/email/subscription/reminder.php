<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Thanks for your support!');?></span>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <div style="margin-top:10px;">

        <p><?php echo Sii::t('sii','Please note that your free trial is expiring in {n} days.',['{n}'=>$days]);?></p>

        <p><?php echo Sii::t('sii','We want to thank you again for trying our product and hope that you will continue to stay with us.');?></p>

        <p><?php echo Sii::t('sii','If you like our product and services, please login to choose a plan that is suitable to you before free trial expires.');?></p>

    </div>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.subscription._subscription',array('model'=>$model));?>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',array(
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model),
        )); 
    ?>

</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>