<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model]);?>

    <table style="width: 100%;margin-bottom:10px;;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Order Cancellation');?></span>
                     <div style="padding:5px 10px;margin:5px 10px;border: 2px dashed lightgrey;float: left;width:45%">
                        <?php if (user()->currentRole==Role::CUSTOMER):?>
                            <div style="font-size:1.1em">
                                <?php echo Sii::t('sii','You had cancelled this order');?>
                            </div>
                        <?php else:?>
                            <div style="font-size:1.1em">
                                <?php echo Sii::t('sii','Merchant has cancelled this order');?>
                            </div>
                            <p style="font-size:1.1em">
                                <?php echo Sii::t('sii','We are sorry to inform you that this order fulfillment is unsuccessful.');?>
                                <?php //if ($model->getTransitionCondition()!=null):?>
                                    <?php //echo $model->getTransitionCondition()->message;?>
                                <?php //endif;?>
                                <?php echo Sii::t('sii','Our Customer Support will contact you to arrange refund matter for those unfulfilled items.');?>
                                <?php echo Sii::t('sii','Should you have any further queries, please contact us at {contact} or <em>{email}</em>.',array('{contact}'=>$model->shop->contact_no,'{email}'=>$model->shop->email));?>
                            </p>
                        <?php endif;?>
                     </div>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order',array('model'=>$model)); ?>

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._button',array(
            'model'=>$model,
            'orderUrl'=>Notification::getActionUrl($model),
        )); 
    ?>

</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.order._shop_footer',['shop'=>$model->shop]);
