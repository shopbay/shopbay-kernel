<div class="form-wrapper">
    
    <div class="form form-container">

        <?php if (isset($model->title)):?>
            <div class="form-heading"><?php echo $model->title;?></div>
        <?php endif;?>

        <div id="flash-bar">
            <?php $this->sflashwidget([get_class($model),'activate']);?>
        </div>    

        <?php $form=$this->beginWidget('CActiveForm', [
                'id'=>'login-form',
                'action'=>url('account/authenticate/login'),
                'enableAjaxValidation'=>false,
        ]); ?>

        <div class="form-row">
            <?php //echo $form->label($model,'username',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model,'username',['class'=>'form-input','maxlength'=>32,'autofocus'=>'autofocus','placeholder'=>$model->getAttributeLabel('username')]); ?>
            <?php echo $form->error($model,'username'); ?>
        </div>

        <div class="form-row">
            <?php //echo $form->label($model,'password',array('class'=>'form-label')); ?>
            <?php echo $form->passwordField($model,'password',['class'=>'form-input','maxlength'=>64,'placeholder'=>$model->getAttributeLabel('password')]); ?>
            <?php echo $form->error($model,'password'); ?>
        </div>

        <div class="form-row">
            <?php echo $form->hiddenField($model,'token'); ?>
            <?php echo CHtml::hiddenField('returnUrl',request()->getQuery('returnUrl')); ?>
            <?php echo CHtml::hiddenField('oauthClient',request()->getQuery('oauthClient')); ?>
            <?php $this->widget('zii.widgets.jui.CJuiButton',[
                        'name'=>'login-button',
                        'buttonType'=>'submit',
                        'caption'=>isset($model->title)?$model->title:Sii::t('sii','Log in'),
                        'value'=>'loginBtn',
                        'htmlOptions'=>['class'=>'ui-button','style'=>'margin-top:10px;'],
                    ]); 
            ?>
        </div>

        <?php if (!$model->isActivateMode):?>
        <div class="form-row">
            <span class="checkbox-wrapper">
                <?php echo $form->checkBox($model,'rememberMe',['style'=>'width:auto;vertical-align:middle;margin-right:5px;']); ?>
                <?php echo $form->label($model,'rememberMe',['class'=>'form-label','style'=>'display:inline;']); ?>
                <?php echo $form->error($model,'rememberMe'); ?>
            </span>
            <span class="form-link">
                <?php echo l(Sii::t('sii','Forgot password?'),url('account/management/forgotpassword'),['style'=>'float:right;']);?>
            </span>
        </div>
        <?php endif;?>

        <?php if (user()->currentRole!=Role::ADMINISTRATOR):?>
        <div class="form-row tos">
            <?php echo $model->getAttributeLabel('acceptTOS'); ?>
        </div>
        <?php endif;?>

        <?php $this->endWidget(); ?>

    </div>
    
    <?php if ($this->allowOAuth && !$model->isActivateMode):?>
    <div class="link-container">
        <?php $this->widget('common.modules.accounts.oauth.widgets.OAuthWidget',[
            'route'=>'account/authenticate',
            'iconOnly'=>false,
        ]); ?>
    </div>    
    <?php endif;?>
    
    <?php if (user()->currentRole!=Role::ADMINISTRATOR):?>
    <div class="link-container">

        <span class="form-link">
            <?php //if (isset($nonAjax))
                      echo Sii::t('sii','Not yet have an account? {signup}',['{signup}'=>CHtml::link(Sii::t('sii','Sign up here'),url('signup'))]);
                  //else
                  //    echo Sii::t('sii','Not yet have an account? {signup}',array('{signup}'=>CHtml::link(Sii::t('sii','Sign up here'),'javascript:void(0);',array('onclick'=>'signup("'.$this->authHostInfo.'");'))));
            ?>
        </span>

    </div>
    <?php endif;?>
    
</div>
