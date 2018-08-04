<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">

    <table style="width: 100%;margin-bottom:50px;background: white;">
         <tbody>
             <tr style="background:whitesmoke;">
                 <td colspan="2" style="border-bottom:0px dashed #EDEDED;">
                     <span style="font-size:2.5em;float:right;margin:10px">
                         <?php echo Sii::tl('sii','Low Inventory Notice',$data->locale);?>
                         <span style="font-size:0.45em;display:block;  text-align: right;margin-right: 2px;color: grey;">
                             <?php $today = new DateTime(); echo $today->format('d-M-Y h:m');?>
                         </span>
                     </span>
                 </td>
             </tr>
            <tr>
                <td width="60%" style="padding-top: 10px;padding-left:20px;">
                    
                    <p style="font-size: 1.5em;"><?php echo Sii::tp('sii','Dear {customer},',array('{customer}'=>Shop::model()->parseLanguageValue($data->shop_name,$data->locale)),$data->locale);?></p>

                    <div style="margin-top:10px;">
                        
                        <p><?php echo Sii::tl('sii','Below product inventories are running low. Please take action to replenish stocks to avoid impact to your shop sales.',$data->locale);?></p>
                        
                        <?php if ($data->hasItems()):?>
                            <div id="inventory-header" style="width:80%;margin-top:30px;">
                                <div style="display:inline-block;width:45%;font-weight:600;"><?php echo Sii::tl('sii','Product',$data->locale);?></div>
                                <div style="display:inline-block;width:15%;font-weight:600;"><?php echo Sii::tl('sii','SKU',$data->locale);?></div>
                                <div style="display:inline-block;width:8%;font-weight:600;"><?php echo Sii::tl('sii','Quantity',$data->locale);?></div>
                                <div style="display:inline-block;width:8%;font-weight:600;"><?php echo Sii::tl('sii','Available',$data->locale);?></div>
                                <div style="display:inline-block;width:8%;font-weight:600;"><?php echo Sii::tl('sii','Threshold',$data->locale);?></div>
                                <div style="display:inline-block;width:10%;font-weight:600;"></div>
                            </div>
                        <?php endif;?>
                        
                        <?php foreach($data->items as $item):?>
                            <div id="inventory-row" style="width:80%;">
                                <div style="display:inline-block;width:45%;">
                                    <?php echo CHtml::image(app()->urlManager->createCdnUrl($item['image_url']),'Image',array('width'=>50,'height'=>50,'style'=>'vertical-align:middle'));?>
                                    <div style="display:inline-block;"><?php echo Product::model()->parseLanguageValue($item['product_name'],$data->locale);?></div>
                                </div>
                                <div style="display:inline-block;width:15%;"><?php echo $item['sku'];?></div>
                                <div style="display:inline-block;width:8%;"><?php echo $item['quantity'];?></div>
                                <div style="display:inline-block;width:8%;"><?php echo $item['available'];?></div>
                                <div style="display:inline-block;width:8%;"><?php echo number_format($item['available']/$item['quantity'],2)*100;?>%</div>
                                <div style="display:inline-block;width:10%;">
                                    <?php $model = new Inventory(); $model->id = $item['id'];?>
                                    <?php echo CHtml::link(Sii::tl('sii','View',$data->locale), app()->urlManager->createMerchantUrl('/'.$model->getViewRoute()),array('style'=>'color:skyblue;'));?>
                                </div>
                            </div><!-- closing inventory row -->

                        <?php endforeach;?>
    
                    </div>

                </td>

            </tr>
         </tbody>   
    </table> 

    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php $this->renderPartial('common.modules.notifications.templates.email.footer');?>
