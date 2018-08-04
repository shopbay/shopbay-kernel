<div style="width:80%;">

    <?php echo Sii::t('sii','We are pretty certain you have started something really special, so we wanted to take a moment to personally welcome you to {app}.',array('{app}'=>param('SITE_NAME')));?>

    <?php if ($model->hasRole(Role::MERCHANT) || isset($merchantRole)):?>

        <?php $this->renderPartial('common.modules.notifications.templates.message.account._welcome_merchant');?>

    <?php else:?>

        <?php $this->renderPartial('common.modules.notifications.templates.message.account._welcome_customer');?>

    <?php endif;?>
    
</div>
    