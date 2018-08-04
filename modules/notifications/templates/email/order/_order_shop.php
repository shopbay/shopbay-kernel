<div style="padding:15px 15px;">

<!--     <span class="shop" style="float:left;color: grey;background: pink;border: 1px solid #EDEDED; border-bottom: 0px solid #EDEDED;padding:2px 10px;">
         <?php //echo $order->shop->displayLanguageValue('name',user()->getLocale());?>
     </span>-->
     <table class="items" style="margin-bottom:5px;background: #F3F3F3;border-collapse: collapse;width: 100%;border: 1px #EDEDED solid;">
         <?php 
                foreach ($shippings as $shipping)
                    if ($shipping->shop_id==$shop_id)
                        $this->renderPartial('common.modules.notifications.templates.email.order._order_shipping',array('order_id'=>$order->id,'shopModel'=>$order->shop,'shipping'=>$shipping));
         ?>
     </table>
</div> 