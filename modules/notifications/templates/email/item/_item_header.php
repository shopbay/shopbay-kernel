<table style="margin-bottom: 10px;background: white;border-bottom: 0px dashed #EDEDED;width:100%">
     <tbody>
        <tr>
            <td style="padding-left:20px;vertical-align: top;width:40%">
<!--               <span style="float:right;margin-right:10px;font-size:1.3em">
                   <?php //echo $model->formatDatetime($model->create_time,true);?>
               </span>-->
<!--            <?php //echo Sii::t('sii','Order No');?>: -->
                <?php //echo $model->order_no;?>
               <h1 style="font-size:3em;margin-top:0px"><?php echo $model->displayLanguageValue('name',user()->getLocale());?></h1>
            </td>
        </tr>
     </tbody>   
</table> 
