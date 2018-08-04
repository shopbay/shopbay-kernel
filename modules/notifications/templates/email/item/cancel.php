<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model]);?>
    
    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Item Cancellation');?></span>
                     <div style="padding:5px 10px;margin:5px 10px;border: 2px dashed lightgrey;float: left;width:62%">
                         <div style="display:inline">
                            <?php echo Sii::t('sii','We are sorry to inform you that this item order fulfillment is unsuccessful and has been canceled.');?>
                            <?php echo Sii::t('sii','We will contact you to arrange refund matter shortly.');?>
                        </div>
                     </div>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.shop._refundpolicy',['model'=>$model->shop]);?>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.item._item',array(
        'model'=>$model,
        'itemUrl'=>Notification::getActionUrl($model),
        'orderUrl'=>Notification::getActionUrl($model->order))); 
    ?>

</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.order._shop_footer',['shop'=>$model->shop]);