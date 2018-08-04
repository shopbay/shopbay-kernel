<div id="method-<?php echo $model->id;?>" class="form" style="background:whitesmoke;display:none">
    <table style="margin-bottom:5px">
        <tr>
            <td style="vertical-align:top;padding-top:6px">
                <i class="fa fa-info-circle"></i>
            </td>
            <td class="payment-method-message" style="padding:5px;width:100%;">
                <?php $this->widget('common.extensions.braintree.widgets.PayPalForm',array(
                            'formActionUrl'=>$model->formUrl,
                            'attachSubmitButton'=>false,
                            'shopId'=>$model->shop_id,
                            'shopName'=>$model->shop->displayLanguageValue('name',user()->getLocale()),
                            'currency'=>$model->shop->currency,//TODO Braintree now supports only USD.. but we need to support supports SGD or MYR
                            'shippingAddress'=>$model->getOverridenShippingAddress($this->cart->getShippingAddress()),
                            'braintreeMethod'=>$model->method,
                      ));
                ?>
            </td>
        </tr>
    </table>
</div>