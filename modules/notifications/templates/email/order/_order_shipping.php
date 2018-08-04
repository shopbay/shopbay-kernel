<tbody>  
    <?php 
        $row=1;
        $items = Item::model()->order($order_id)->locateShop($shopModel->id)->shipping($shipping->shipping_id)->findAll();
        foreach ($items as $item){
    ?>  

        <tr class="<?php echo ($row%2)?'odd':'even';?>">
            <!-- Name -->
            <td style="width:42%;">
                <table class="imagename">
                    <tr>
                        <td>
                            <?php echo CHtml::image(CHtml::encode($item->getProductImageUrl()),'Image',array('style'=>'vertical-align:top;width:60px;height:60px'));?>
                        </td>
                        <td width="80%" style="vertical-align:top">
                           <div style="display:inline;">
                                <?php echo $item->displayLanguageValue('name',user()->getLocale());?>
                                <?php if ($item->option_fee>0): ?>
                                <div style="clear:both;font-size:0.9em;">
                                	<div style="padding-top:3px;"><?php echo $item->getAttributeLabel('option_fee').': '.$item->formatCurrency($item->option_fee,$item->currency);?></div>
                                </div>
                                <?php endif;?>
                                <?php if ($item->shipping_surcharge>0): ?>
                                <div style="clear:both;font-size:0.9em;">
                                	<div style="padding-top:3px;"><?php echo $item->getAttributeLabel('shipping_surcharge').': '.$item->formatCurrency($item->shipping_surcharge,$item->currency);?></div>
                                </div>
                                <?php endif;?>
                                <div style="clear:both;font-size:0.9em;">
                                    <div style="padding-top:3px;"><?php echo Sii::t('sii','SKU').': '.$item->product_sku;?></div>
                                </div>   
                                <div style="clear:both;font-size:0.9em;">
                                    <?php echo Helper::htmlSmartKeyValues($item->getOptions());?>
                                </div>
                                <?php if ($item->weight!=null): ?>
                                <div style="clear:both;font-size:0.9em;">
                                	<div style="padding-top:3px;"><?php echo Sii::t('sii','Weight').': '.$item->formatWeight($item->weight);?></div>
                                </div>
                                <?php endif;?>
                                <?php if (isset($item->tracking_no)): ?>
                                    <br><span class="tracking-label" style=""><?php echo l(Sii::t('sii','Tracking No').': '.$item->tracking_no,$item->tracking_url,array('target'=>'_blank'));?></span>   
                                <?php endif; ?>
                           </div>

                        </td>
                    </tr>
                </table>
            </td>
            <!-- Price -->
            <td style="width:10%;text-align:center">
                <?php echo $item->formatCurrency($item->unit_price,$item->currency);?>
                <?php if ($item->option_fee>0): ?>
                    <div><?php echo Sii::t('sii','plus {additional_fee}',array('{additional_fee}'=>$item->formatCurrency($item->option_fee,$item->currency)));?></div>
                <?php endif;?>
                <?php if ($item->shipping_surcharge>0): ?>
                    <div><?php echo Sii::t('sii','plus {additional_fee}',array('{additional_fee}'=>$item->formatCurrency($item->shipping_surcharge,$item->currency)));?></div>
                <?php endif;?>
            </td>
            <!-- Quantity -->
            <td style="width:8%;text-align:center">
                <?php echo $item->quantity;?>
            </td>
            <!-- Sub Total -->
            <td id="subtotal" style="width:10%;text-align:center">
                <?php echo $item->formatCurrency($item->total_price,$item->currency);?>
            </td>
        </tr>

    <?php  $row++; } ?>

</tbody>  


<tbody style="background:white">
    <tr>
        <td colspan="6" class="shipping" style="padding: 10px 20px;">
            <div style="float:left;">
                    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order_shipping_data',array('data'=>$shipping,'shopModel'=>$shopModel));?>
            </div>
            <div style="float:right;">
                    <?php $this->renderPartial('common.modules.notifications.templates.email.order._order_shipping_subtotal',array('subtotal'=>$shipping,'shopModel'=>$shopModel));?>
            </div>
        </td>
    </tr>
</tbody>   