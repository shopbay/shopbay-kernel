<div id="method-<?php echo $model->id;?>" class="form" style="background:whitesmoke;display:none">
    <table style="margin-bottom:5px">
        <tr>
            <td style="vertical-align:top;padding-top:6px">
                <i class="fa fa-info-circle"></i>
            </td>
            <td>
                <p style="margin:5px;">
                    <?php echo Sii::t('sii','Select <b>{method}</b> payment method will have this order saved and not processed. Detailed steps on how to proceed payment will be sent to your mailbox.',array('{method}'=>PaymentMethod::getName(PaymentMethod::ATM_CASH_BANK_IN,user()->getLocale())));?>
                </p>
                <p style="margin:5px;">
                    <?php echo $model->getTips(user()->getLocale());?>
                </p>
            </td>
        </tr>
    </table>
</div>