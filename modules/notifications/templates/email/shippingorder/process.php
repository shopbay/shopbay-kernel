<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model->order]);?>

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Pending Process');?></span>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order',['model'=>$model->order]); ?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',[
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model,app()->urlManager->merchantDomain),
        ]); 
    ?>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>

</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.footer');