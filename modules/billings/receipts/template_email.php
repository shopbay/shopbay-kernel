<html>
    <head>
        <style>
            tr.border_bottom td {
                border-bottom:1pt dotted gainsboro;
            }      
            td {
                padding-top:10px;
                padding-bottom:10px;
           }
           td:first-child{
               width:200px;
               color:gray;
           }
        </style>
    </head>
    <body>
        <p><?php echo Sii::t('sii','We received payment for your {app} subscription. Thanks for business!',['{app}'=>$app]);?></p>
        <p><?php echo Sii::t('sii','Contact us at <em style="color:skyblue;">{email}</em> if you have questions.',['{email}'=>$email]);?></p>
        <table style="margin-top:20px;">
            <tr class="border_bottom">
                <td><?php echo $model->getAttributeLabel('receipt_no');?></td>
                <td><?php echo $model->receipt_no;?><td>
            </tr>
            <tr class="border_bottom">
                <td><?php echo $model->getAttributeLabel('billed_to');?></td>
                <td><?php echo $model->billedTo;?><td>
            </tr>
            <?php foreach ($model->itemsData as $item): ?>
                <?php foreach ($fields as $field): ?>
                    <?php if ($field=='amount'): ?>
                    <tr class="border_bottom">
                        <td><?php echo $model->getAttributeLabel($field);?></td>
                        <td><?php echo $model->formatCurrency($item[$field],$item['currency']);?></td>
                    </tr>
                    <?php elseif ($field=='service_start'): ?>
                    <tr class="border_bottom">
                        <td><?php echo Sii::t('sii','Service Validity');?></td>
                        <td><?php echo Sii::t('sii','{start_date} to {end_date}',['{start_date}'=>$item[$field],'{end_date}'=>$item['service_end']]);?></td>
                    </tr>
                    <?php else: ?>
                    <tr class="border_bottom">
                        <td><?php echo $model->getAttributeLabel($field);?></td>
                        <td><?php echo $item[$field];?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach;?>
            <?php endforeach;?>
        </table>
        <div style="margin-top:20px;">
            <p style="font-size:1.5em;font-weight: 500;"><?php echo $site;?></p>
            <p style="color:lightgray"><?php echo $url;?></p>
        </div>
    </body>
</html>
