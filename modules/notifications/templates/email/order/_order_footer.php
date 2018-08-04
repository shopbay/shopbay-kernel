<table class="items" style="margin-bottom:0px;border: 0px solid white;background:white;border-collapse: collapse;width: 100%;">
     <tfoot>
        <tr style="border-bottom: 0px dashed #EDEDED;">
            <td colspan="6" style="padding-left:20px;">
                <?php echo Sii::t('sii','Total <span style="color:red">{n}</span> item|Total <span style="color:red">{n}</span> items',array($order->item_count));?>
            </td>
        </tr>
        <tr>
            <td colspan="6" style="padding-top:15px;padding-bottom:30px">
                <!--Shipping Address Info-->
                <div style="float:left;position:relative;padding-left:30px;">
                    <div style="padding:5px 3px;"><b><?php echo Sii::t('sii','Shipping Address');?></b></div>
                    <div>
                        <span style="padding:3px 10px;"><?php echo $order->address->recipient;?></span><br>
                        <?php if ($order->address->mobile!=null):?>
                        <span style="padding:3px 10px;"><?php echo $order->address->mobile;?></span><br>
                        <?php endif;?>
                        <span style="padding:3px 10px;"><?php echo $order->address->address1;?></span><br>
                        <?php if ($order->address->address2!=null):?>
                        <span style="padding:3px 10px;"><?php echo $order->address->address2;?></span><br>
                        <?php endif;?>
                        <span style="padding:3px 10px;"><?php echo $order->address->postcode.', '.$order->address->city;?></span><br>
                        <span style="padding:3px 10px;"><?php echo $order->address->state.', '.$order->address->country;?></span>
                    </div>
                    <?php if ($order->remarks!=null):?>
                    <div style="padding:10px 3px 5px 3px;"><b><?php echo Sii::t('sii','Shipping Note');?></b></div>
                    <div style="width:200px;padding:0px 10px;"><?php echo $order->remarks;?></div>
                    <?php endif;?>
                </div>
                <!--Payment Info-->
                <div style="float:left;position:relative;padding-left:60px;">
                    <div style="padding:5px 3px;"><b><?php echo Sii::t('sii','Payment');?></b></div>
                    <div>
                        <?php $payment = $order->getPayment(); ?>
                        <?php if ($payment!=null): ?>
                        <div style="padding:3px 10px;">
                            <?php echo $payment->payment_no;?>
                        </div>
                        <?php endif;?>
                        <span style="padding:0px 10px;">
                         <?php  if ($order->getPaymentMethodMode()==PaymentMethod::PAYPAL_EXPRESS_CHECKOUT)
                                    $this->renderView('payments.paypallogo');
                                else 
                                    echo $order->getPaymentMethodName(user()->getLocale());
                         ?>
                        </span>
                    </div>
                </div>
                <!--Total Info-->
                <div style="float:right;padding-right:30px;">
                    <table>  
                        <tr>  
                              <td>
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo Sii::t('sii','Total Price');?>:</span>
                              </td>  
                              <td align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->formatCurrency($order->item_total,$order->currency);?></span>
                              </td>  

                        </tr>  
                        <?php  if ($order->hasCampaignSale()): ?>
                        <tr style="background: lightyellow; ">  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getCampaignSaleOfferTag();?>:</span>
                              </td>  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;" align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getCampaignsaleDiscountText();?></span>
                              </td>  
                        </tr>   
                        <?php endif;?>
                        <?php  if ($order->hasCampaignPromocode()): ?>
                        <tr style="background: lightcyan; ">  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getCampaignPromocodeCode().' '.$order->getCampaignPromocodeText(user()->getLocale());?>:</span>
                              </td>  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;" align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getCampaignPromocodeDiscountText();?></span>
                              </td>  
                        </tr>   
                        <?php endif;?>
                        <?php  foreach ($order->getTaxDisplaySet(user()->getLocale()) as $taxName => $taxAmount): ?>
                        <tr>  
                              <td>
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $taxName;?>:</span>
                              </td>  
                              <td align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->formatCurrency($taxAmount,$order->currency);?></span>
                              </td>  
                        </tr>   
                        <?php endforeach;?>
                        <tr>  
                              <td>
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo Sii::t('sii','Total Shipping Fee');?>:</span>
                              </td>  
                              <td align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->formatCurrency($order->shipping_total,$order->currency);?></span>
                              </td>  
                        </tr>   
                        <?php  if ($order->hasDiscountFreeShipping()): ?>
                        <tr style="background: linen; ">  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getDiscountFreeShippingOfferTag(user()->getLocale());?>:</span>
                              </td>  
                              <td style="border-top: 1px dashed black; border-bottom: 1px dashed black; margin-bottom: 5px;" align="right">
                                  <span style="color:#555;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->getDiscountFreeShippingDiscountText();?></span>
                              </td>  
                        </tr>   
                        <?php endif;?>
                        <tr>  
                              <td>
                                  <span style="color:red;float:right;font-weight: normal;font-size:1.2em;"><?php echo Sii::t('sii','Grand Total');?>:</span>
                              </td> 
                              <td align="right">
                                  <span style="color:red;float:right;font-weight: normal;font-size:1.2em;"><?php echo $order->formatCurrency($order->grand_total);?></span>
                              </td>  
                        </tr>  
                    </table>  

                </div>
            </td>
        </tr>
     </tfoot>   
</table>
