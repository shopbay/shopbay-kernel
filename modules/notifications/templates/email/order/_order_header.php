<!--<table style="margin-bottom: 10px;background: white;border-bottom: 0px dashed #EDEDED;width:100%">
     <tbody>
        <tr>
            <td style="padding-left:20px;vertical-align: top;width:40%">
                   <span style="float:right;margin-right:10px;font-size:1.3em">
                       <?php //echo $model->formatDatetime($model->create_time,true);?>
                   </span>
                   <?php //echo Sii::t('sii','Order No');?>:
                   <h1 style="font-size:3em;margin-top:0px">
                       <?php //echo $model->order_no;?>
                   </h1>
            </td>
        </tr>
     </tbody>   
</table> -->
<div style="border-top: 1px dashed #EDEDED;padding-top:15px;">
    <!-- Header for items -->
    <table class="items" style="margin-bottom:0px;border: 1px solid white;">
       <thead >
            <tr>
                <th colspan="2" style="width:42%;padding:10px 0px;"><?php echo Item::model()->getAttributeLabel('name');?></th>
                <th style="width:10%;padding:10px 0px;"><?php echo Item::model()->getAttributeLabel('unit_price');?></th>
                <th style="width:8%;padding:10px 0px;"><?php echo Item::model()->getAttributeLabel('quantity');?></th>
                <th style="width:10%;padding:10px 0px;"><?php echo Item::model()->getAttributeLabel('total_price');?></th>
            </tr>
        </thead>
    </table> 
</div>   