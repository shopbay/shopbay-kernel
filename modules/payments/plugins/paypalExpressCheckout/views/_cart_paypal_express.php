<div id="method-<?php echo $model->id;?>" class="form" style="background:whitesmoke;display:none">
    <table style="margin-bottom:5px">
        <tr>
            <td style="vertical-align:top;padding-top:6px">
                <i class="fa fa-info-circle"></i>
            </td>
            <td>
                <p style="margin:5px;">
                    <?php echo Sii::t('sii','Select <b>{method}</b> payment method will use your PayPal account to pay this order.',array('{method}'=>PaymentMethod::getName(PaymentMethod::PAYPAL_EXPRESS_CHECKOUT,user()->getLocale())));?>
                    <?php echo CHtml::link(Sii::t('sii','How it works'),'https://www.paypal.com/webapps/mpp/paypal-popup',array('target'=>'_blank'));?>
                </p>
            </td>
        </tr>
    </table>
</div>