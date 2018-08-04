<div id="method-<?php echo $model->id;?>" class="form" style="background:whitesmoke;display:none">
    <table style="margin-bottom:5px">
        <tr>
            <td style="vertical-align:top;padding-top:6px">
                <i class="fa fa-info-circle"></i>
            </td>
            <td class="payment-method-message">
                 <?php echo $model->getTips(user()->getLocale());?>
            </td>
        </tr>
    </table>
</div>