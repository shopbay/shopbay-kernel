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
        <h1 style="margin-top:20px;margin-bottom: 50px;"><?php echo Sii::tp('sii','{site} Receipt',['{site}'=>$site],$model->fileLocale);?></h1>
        <p><?php echo Sii::tp('sii','We received payment for your purchase. Thanks for business!',['{app}'=>$app],$model->fileLocale);?></p>
        <p><?php echo Sii::tp('sii','Contact us at <em style="color:skyblue;">{email}</em> if you have questions.',['{email}'=>$email],$model->fileLocale);?></p>
        <table style="margin-top:20px;">
            <tr class="border_bottom">
                <td><?php echo Sii::tl('sii','Date',$model->fileLocale);?></td>
                <td><?php echo date('Y-m-d H:i:s',$model->create_time);?></td>
            </tr>
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
                        <td>Amount</td>
                        <td><?php echo $model->formatCurrency($item[$field],$item['currency']);?></td>
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
        <div style="position:absolute;bottom: 0;">
            <h1 style="font-size:5em;color:gainsboro"><?php echo Sii::tl('sii','Thank you.',$model->fileLocale);?></h1>
            <p style="font-size:1.5em;font-weight: 500;"><?php echo $site;?></p>
            <p style="color:lightgray"><?php echo $url;?></p>
        </div>
    </body>
</html>
