<div class="form-wrapper">

    <h1><?php echo Sii::t('sii','Sign up');?></h1>
    
    <div id="flash-bar">
        <?php   $message = Sii::t('sii','You only need to set password.');
                $message .= ' '.Sii::t('sii','Other account information are extracted from the shipping address you had previously filled for order {order_no}.',['{order_no}'=>$model->order_no]);
                echo $this->getFlashAsString('notice',$message,null);
        ?>
        <?php $this->sflashwidget(get_class($model));?>
    </div>    
    
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'signup-customer-form',
            'action'=>url('signup/customer/order/'.$model->order_no),
            'enableAjaxValidation'=>true,
    )); ?>
        
    <div class="form form-container customer-account-form">

        <div class="form-row">
            <?php echo $form->label($model,'email',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model,'email',array('class'=>'form-input','maxlength'=>100,'placeholder'=>$model->getAttributeLabel('email'))); ?>
            <?php echo $form->error($model,'email'); ?>
        </div>

        <div class="form-row">
            <?php echo $form->label($model,'password',array('class'=>'form-label')); ?>
            <?php echo $form->passwordField($model,'password',array('class'=>'form-input','maxlength'=>32,'placeholder'=>$model->getAttributeLabel('password'))); ?>
            <?php echo $form->error($model,'password'); ?>
        </div>

        <div class="form-row">
            <?php echo $form->label($model,'confirmPassword',array('class'=>'form-label')); ?>
            <?php echo $form->passwordField($model,'confirmPassword',array('class'=>'form-input','maxlength'=>32,'placeholder'=>$model->getAttributeLabel('confirmPassword'))); ?>
            <?php echo $form->error($model,'confirmPassword'); ?>
        </div>
        
        <?php if(CCaptcha::checkRequirements()): ?>
        <div class="form-row">
            <table>
                <tr>
                    <td>
                        <?php $this->widget('CCaptcha',array(
                                'id'=>'signup-captcha',
                                'buttonType'=>'button',
                                'captchaAction'=> 'captcha',
                                'clickableImage'=>true,
                                'showRefreshButton'=>false,
                                'imageOptions'=>array('style'=>'cursor:pointer','title'=>Sii::t('sii','Click to Refresh')),
                            )); 
                        ?>
                    </td>
                    <td>
                        <div style="display:inline">
                            <small>
                                <?php echo Sii::t('sii','Please enter the letters (case-insensitive) at shown at left. Click on the image to get a new one.');?>
                            </small>
                            <br>
                            <?php echo CHtml::activeTextField($model, 'verify_code'); ?>
                            <?php echo $form->error($model,'verify_code'); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>


    </div>

    <div class="customer-form-separator">
        <!-- empty -->
    </div>
    
    <div class="form customer-address-form">
        
        <div class="form-row">
            <?php echo $form->label($model,'alias_name',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model,'alias_name',array('class'=>'form-input','size'=>50,'maxlength'=>100,'placeholder'=>$model->getAttributeLabel('alias_name'))); ?>
            <?php echo $form->error($model,'alias_name'); ?>
        </div>

        <div class="form-row">
            <?php echo $form->label($model,'mobile',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model,'mobile',array('class'=>'form-input','size'=>50,'maxlength'=>100,'placeholder'=>$model->getAttributeLabel('mobile'))); ?>
            <?php echo $form->error($model,'mobile'); ?>
        </div>

        <div class="form-row">
            <?php echo $form->label($model->address,'address1',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model->address,'address1',array('class'=>'form-input','size'=>50,'maxlength'=>100,'placeholder'=>$model->getAttributeLabel('address'))); ?>
        </div>

        <div class="form-row">
            <?php echo $form->textField($model->address,'address2',array('class'=>'form-input','size'=>50,'maxlength'=>100,'placeholder'=>$model->getAttributeLabel('address'))); ?>
            <?php echo $form->error($model->address,'address1'); ?>
            <?php echo $form->error($model->address,'address2'); ?>
        </div>

        <div class="form-row">
            <div class="column">
                <?php echo $form->label($model->address,'postcode',array('class'=>'form-label')); ?>
                <?php echo $form->textField($model->address,'postcode',array('class'=>'form-input','size'=>25,'maxlength'=>20,'placeholder'=>$model->getAttributeLabel('postcode'))); ?>
                <?php echo $form->error($model->address,'postcode'); ?>
            </div>
            <div class="column">
            <?php echo $form->label($model->address,'city',array('class'=>'form-label')); ?>
                <?php echo $form->textField($model->address,'city',array('class'=>'form-input','size'=>30,'maxlength'=>40,'placeholder'=>$model->getAttributeLabel('city'))); ?>                
                <?php echo $form->error($model->address,'city'); ?>
            </div>
        </div>

        <div class="form-row">
            <div class="column">
                <?php echo $form->label($model->address,'country',array('class'=>'form-label','style'=>'margin-top:8px')); ?>
                <?php echo $form->dropDownList($model->address,'country',
                                                SLocale::getCountries(),
                                                array('class'=>'chzn-select-country form-input',
                                                      'prompt'=>'',
                                                      'data-placeholder'=>Sii::t('sii','Select Country'),
                                                      'style'=>'width:180px;')); 
                ?>
                <?php echo $form->error($model->address,'country'); ?>
            </div>
            <div class="column">
                <?php echo $form->label($model->address,'state',array('class'=>'form-label','style'=>'margin-top:8px')); ?>
                <?php echo $form->dropDownList($model->address,'state',
                                                SLocale::getStates($model->address->country),
                                                array('class'=>'chzn-select-state form-input',
                                                      'prompt'=>'',
                                                      'data-placeholder'=>Sii::t('sii','Select State'),
                                                      'style'=>'width:180px;')); 
                ?>
                <?php echo $form->error($model->address,'state'); ?>
            </div>
        </div>
    </div>

    <div class="form-row">
        <?php //echo $form->checkBox($model,'accept',array('style'=>'margin-left:10px;')); ?>
        <!--<span class="form-link">
            <?php //echo l($model->getAttributeLabel('accept'),url('terms'));?>
        </span>-->
        <?php 
            $this->widget('zii.widgets.jui.CJuiButton',array(
                'name'=>'signup-button',
                'buttonType'=>'button',
                'caption'=>Sii::t('sii','Sign up'),
                'value'=>'btn',
                'onclick'=>'js:function(){registercustomeraccount("'.$model->order_no.'");}',//this is reloaded from orders.js
            )); 
        ?>
    </div>

    <div class="form-row tos">
        <?php echo $model->getAttributeLabel('acceptTOS'); ?>
    </div>
    
    <?php $this->endWidget(); ?>
    
</div>
