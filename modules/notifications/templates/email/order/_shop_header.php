<style>
.header-logo {
    display:inline;
}
.header-field {
    float:right;
    position: relative;
    top: 20px;
    padding: 0px 20px;
}
    
/*Responsive Styles*/
@media screen and (max-width : 640px){
    .header-logo {
        display:block;
    }
    .header-field {
        display:block;
        margin-bottom: 20px;
        float: left;
    }
}
</style>
<div style="width: 100%;margin-bottom:10px;;background: white;">
     <div class="header-logo">
        <?php echo $shop->getLogo(['style'=>'vertical-align: middle;'],Image::VERSION_XSMALL);?>
         <span style="font-size:2em;padding: 20px;position: relative;top: 5px;">
            <?php echo $shop->displayLanguageValue('name',user()->getLocale());?>
         </span>
     </div>
     <div class="header-field">
         <span>
            <?php echo $order->getAttributeLabel('create_time');?>:
         </span>
         <span>
            <strong><?php echo $order->formatDatetime($order->create_time,true);?></strong>
         </span>
     </div>
     <div class="header-field">
         <span>
            <?php echo $order->getAttributeLabel('order_no');?>:
         </span>
         <span>
            <strong><?php echo $order->order_no;?></strong>
         </span>
     </div>
</div>
