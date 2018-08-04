<div id="method-<?php echo $model->id;?>" class="form" style="background:whitesmoke;display:none">
    <table style="margin-bottom:5px">
        <tr>
            <td style="vertical-align:top;padding-top:6px">
                <i class="fa fa-info-circle"></i>
            </td>
            <td class="payment-method-message" style="padding:5px;width:100%;">
                <?php $this->widget('common.extensions.braintree.widgets.HostedFieldsForm',array(
                            'formActionUrl'=>$model->formUrl,
                            'attachSubmitButton'=>false,
                            'shopId'=>$model->shop_id,
                            'braintreeMethod'=>$model->method,
                      ));
                ?>
            </td>
        </tr>
    </table>
</div>