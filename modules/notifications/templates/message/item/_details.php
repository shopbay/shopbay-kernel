<span style="float:right;vertical-align:top;margin-right:30px;">
    <?php echo CHtml::image($model->getProductImageUrl(),$model->displayLanguageValue('name',user()->getLocale()),array('width'=>100));?> 
</span>
<table style="width:500px;">
    <tr>
        <td><?php echo Sii::t('sii','Order No');?></td>
        <td><?php echo $model->order_no;?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Item Name');?></td>
        <td><?php echo $model->displayLanguageValue('name',user()->getLocale());?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Unit Price');?></td>
        <td><?php echo $model->formatCurrency($model->unit_price);?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Quantity');?></td>
        <td><?php echo $model->quantity;?></td>
    </tr>
    <tr>
        <td><?php echo Sii::t('sii','Total Price');?></td>
        <td><?php echo $model->formatCurrency($model->total_price);?></td>
    </tr>
    <?php if (isset($showShippingDetails)):?>
        <tr>
            <td><?php echo Sii::t('sii','Shipping Address');?></td>
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
        <?php if ($model->shipping!=null):?>
        <tr>
            <td><?php echo Sii::t('sii','Shipping Method');?></td>
            <td><?php echo $model->shipping->getMethodDesc();?></td>
        </tr>
        <tr>
            <td><?php echo Sii::t('sii','Shipping Type');?></td>
            <td><?php echo $model->shipping->getTypeDesc();?></td>
        </tr>
        <tr>
            <td><?php echo Sii::t('sii','Shipping ETA');?></td>
            <td><?php echo $model->shipping->speed==null?'':Sii::t('sii','{n} day(s)',array($model->shipping->speed));?></td>
        </tr>
        <?php endif;?>
        <tr>
            <td><?php echo Sii::t('sii','Tracking Number');?></td>
            <td><?php echo l($model->tracking_no,$model->tracking_url);?></td>
        </tr>
    <?php endif;?>
        
    <?php if (isset($showRefundDetails) && $model->actualRefundAmount!=null):?>
        <tr>
            <td><?php echo Sii::t('sii','Actual Refund Total');?></td>
            <td><?php echo $model->formatCurrency($model->actualRefundAmount);?></td>
        </tr>
    <?php endif;?>
    
    <tr>
        <td><?php echo Sii::t('sii','Status');?></td>
        <td><?php echo Process::getHtmlDisplayText($model->status);?></td>
    </tr>
</table>
