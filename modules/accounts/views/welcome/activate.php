<div class="welcome-block rounded width-p-45">
    <h3 class="message">
        <?= Sii::t('sii','Create my {app} account',array('{app}'=>app()->name));?>    
    </h3>
    <?php $this->renderPartial('common.modules.accounts.views.activate.presignup',array('model'=>$this->module->runControllerMethod('accounts/welcome','loadPreSignupForm'))); ?>   
</div>
<div class="rounded width-p-45" style="display:inline-block;margin-left:20px;margin-top:65px;">
    <?php echo $this->renderAdvices(); ?>
</div>