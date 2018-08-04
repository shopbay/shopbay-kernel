<div class="form" style="margin-top: 200px;">

    <h2 style="color: red;"><?php echo Sii::t('sii','Danger Zone');?></h2>

    <?php $form=$this->beginWidget('CActiveForm', [
            'id'=>'account-closed-form',
            'action'=>url('account/management/close'),
        ]);
    ?>

    <p class="note">
        <?php echo Sii::t('sii','If you no longer wish to keep your account with us, please click button below.');?>
    </p>
    <p class="note">
        <?php echo Sii::t('sii','Please note that by closing account you will lose all your data and not able to use {app} service anymore under this account.',['{app}'=>param('SITE_NAME')]);?>
    </p>

    <div class="row" style="margin-top:20px;">
        <?php
            $this->widget('zii.widgets.jui.CJuiButton',[
                'name'=>'closeButton',
                'buttonType'=>'button',
                'caption'=>Sii::t('sii','Close Account'),
                'value'=>'closebtn',
                'onclick'=>'js:function(){closeaccount(\''.Sii::t('sii','Are you sure you want to close this account? Please note that you will lose all your data upon account closure, and this action is irreversible.').'\',\'account-closed-form\');}',
                'htmlOptions'=>['style'=>'background:#f9f9f9;color:darkgray'],
            ]);
        ?>
    </div>

    <?php $this->endWidget(); ?>

</div><!-- form div -->
