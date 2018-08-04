<div class="form">
    <div class="errorSummary">
        <p><b><?= $message; ?></b></p>
        <p>
            <?php //this link requires javascript to close window and route back to parent window
                  //echo CHtml::link(Sii::t('sii','Return to main page'), '/').' | '.CHtml::link(Sii::t('sii','Return to login page'), url('signin'));?>
        </p>
    </div>
</div>