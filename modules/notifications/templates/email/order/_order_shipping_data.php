<table class="subtotal">  
    <tr>  
          <td>
               <span><?php echo $shopModel->parseLanguageValue($data->shipping_name,user()->getLocale());?>:</span>
               <span><?php echo $shopModel->formatCurrency($data->shipping_rate);?></span>
          </td>  
    </tr> 
    <?php if ($data->weight>0):?>
    <tr>  
          <td>
              <span><?php echo Sii::t('sii','Sub Total Weight');?>:</span>
              <span><?php echo $shopModel->formatWeight($data->weight);?></span>
          </td>  
    </tr>  
    <?php endif;?>
</table>  