<div style="margin: 0px 50px 0 20px;float: left;height: auto;">
    <?php echo CHtml::image(CHtml::encode($model->getProductImageUrl()),'Image',array('style'=>'vertical-align:top;width:200px;height:200px'));?>
</div>
<div style="display:inline;width: 340px;margin-left: 10px;float: left;">
    <table>
        <tbody>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('shop_id');?></span></td>
                <td><?php echo $model->shop->displayLanguageValue('name',user()->getLocale());?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('product_sku');?></span></td>
                <td><?php echo $model->product_sku;?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('unit_price');?></span></td>
                <td><?php echo $model->formatCurrency($model->unit_price,$model->currency);?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('quantity');?></span></td>
                <td><?php echo $model->quantity;?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('weight');?></span></td>
                <td><?php echo $model->formatWeight($model->weight);?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('option_fee');?></span></td>
                <td><?php echo $model->formatCurrency($model->option_fee,$model->currency);?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('shipping_surcharge');?></span></td>
                <td><?php echo $model->formatCurrency($model->shipping_surcharge,$model->currency);?></td>
            </tr>
            <?php foreach ($model->getOptions() as $key => $value): ?>
            <tr>
                <td><span class="field"><?php echo $key;?></span></td>
                <td><?php echo $value;?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('shipping_id');?></span></td>
                <td><?php echo $model->shipping->displayLanguageValue('name',user()->getLocale());?></td>
            </tr>
            <tr>
                <td><span class="field"><?php echo $model->getAttributeLabel('total_price');?></span></td>
                <td><?php echo $model->formatCurrency($model->total_price,$model->currency);?></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 10px 0px;">
                    <a href="<?php echo $itemUrl;?>" style="display: inline-block;margin-right: 10px;background: darkcyan;color: white;font-size: 2em;padding: 10px 20px;text-decoration: none;">
                        <?php echo Sii::t('sii','View Item');?>
                    </a>
                    <a href="<?php echo $orderUrl;?>" style="display: inline-block;background: darksalmon;color: white;font-size: 2em;padding: 10px 20px;text-decoration: none;">
                        <?php echo Sii::t('sii','View Order');?>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>

</div>
