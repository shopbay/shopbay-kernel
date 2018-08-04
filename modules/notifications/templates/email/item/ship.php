<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <?php $this->renderPartial('common.modules.notifications.templates.email.order._shop_header',['shop'=>$model->shop,'order'=>$model]);?>

    <table style="width: 100%;margin-bottom:10px;background: white;">
         <tbody>
             <tr>
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px"><?php echo Sii::t('sii','Shipping Notice');?></span>
                     <div style="padding:5px 10px;margin:5px 10px;border: 2px dashed lightgrey;float: left;width:62%">
                         <div style="display:inline">
                            <table style="display:inline-table;">
                                <tr>
                                    <td style="vertical-align:top;font-weight:bold"><?php echo Sii::t('sii','Shipping Address');?></td>
                                    <td>
                                        <!--Shipping Address Info-->
                                        <div>
                                            <span><?php echo $model->order->address->recipient;?></span><br>
                                            <?php if ($model->order->address->mobile!=null):?>
                                            <span><?php echo $model->order->address->mobile;?></span><br>
                                            <?php endif;?>
                                            <span><?php echo $model->order->address->address1;?></span><br>
                                            <?php if ($model->order->address->address2!=null):?>
                                            <span><?php echo $model->order->address->address2;?></span><br>
                                            <?php endif;?>
                                            <span><?php echo $model->order->address->postcode.', '.$model->order->address->city;?></span><br>
                                            <span><?php echo $model->order->address->state.', '.$model->order->address->country;?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php if ($model->order->remarks!=null):?>
                                <tr>
                                    <td><?php echo Sii::t('sii','Shipping Note');?></td>
                                    <td><?php echo $model->order->remarks;?></td>
                                </tr>    
                                <?php endif;?>                            
                            </table>
                        </div>
                        <table style="display:inline-table;vertical-align:top;margin-left:20px">
                            <?php if ($model->shipping!=null):?>
                            <tr>
                                <td style="font-weight:bold"><?php echo Sii::t('sii','Shipping Method');?></td>
                                <td><?php echo $model->shipping->getMethodDesc();?></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold"><?php echo Sii::t('sii','Shipping Type');?></td>
                                <td><?php echo $model->shipping->getTypeDesc();?></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold"><?php echo Sii::t('sii','Shipping ETA');?></td>
                                <td><?php echo $model->shipping->speed==null?'':Sii::t('sii','{n} day(s)',array($model->shipping->speed));?></td>
                            </tr>
                            <?php endif;?>
                            <tr>
                                <td style="font-weight:bold"><?php echo Sii::t('sii','Tracking Number');?></td>
                                <td><?php echo l($model->tracking_no,$model->tracking_url);?></td>
                            </tr>
                        </table>
                     </div>
                 </td>
             </tr>
         </tbody>   
    </table> 
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.item._item',array(
        'model'=>$model,
        'itemUrl'=>Notification::getActionUrl($model),
        'orderUrl'=>Notification::getActionUrl($model->order))); 
    ?>

</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.order._shop_footer',['shop'=>$model->shop]);
