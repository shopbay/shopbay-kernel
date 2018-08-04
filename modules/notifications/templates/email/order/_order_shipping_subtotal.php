<table class="subtotal">  
    <tr>  
          <td>
              <span style="float:right;"><?php echo Sii::t('sii','Sub Total Price');?>:</span>
          </td>  
          <td align="right">
              <span id="items_subtotal" style="float:right;"><?php echo $shopModel->formatCurrency($subtotal->price);?></span>
          </td>  
    </tr>  
    <tr>  
          <td>
              <span style="float:right;"><?php echo Sii::t('sii','Sub Total Shipping Fee');?>:</span>
          </td>  
          <td align="right">
              <span id="shippingFee_subtotal" style="float:right;"><?php echo $shopModel->formatCurrency($subtotal->shipping_fee);?></span>
          </td>  
    </tr>  
</table>  