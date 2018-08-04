<div class="grid-view">
    <div class="refund-suggestion-note">
        <?php echo Sii::t('sii','Note: Refund suggestion {refund} shipping fee.',['{refund}'=>Helper::parseBool(Config::getBusinessSetting('refund_shipping_fee'))?Sii::t('sii','includes'):Sii::t('sii','does not include')]);?>
    </div>
    <table class="items">
        <thead>
            <tr>
                <th><?php echo Sii::t('sii','Refundable Items Count');?></th>
                <th><?php echo Sii::t('sii','Refundable Shipping Surcharge');?></th>
                <th><?php echo Sii::t('sii','Refundable Items Amount');?></th>
                <th><?php echo Sii::t('sii','Refundable Tax');?></th>
                <th><?php echo Sii::t('sii','Refundable Total');?></th></tr>
        </thead>
        <tbody>
            <tr class="odd">
                <td style="text-align:center"><?php echo $refundable->total_item;?></td>
                <td style="text-align:center"><?php echo $model->formatCurrency($refundable->total_shipping_surcharge);?></td>
                <td style="text-align:center"><?php echo $model->formatCurrency($refundable->total_price);?></td>
                <td style="text-align:center"><?php echo $model->formatCurrency($refundable->total_tax);?></td>
                <td style="text-align:center"><?php echo $model->formatCurrency($refundable->total_amount);?></td>
            </tr>
        </tbody>
    </table>
</div>
