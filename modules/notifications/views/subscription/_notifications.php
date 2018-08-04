<div class="form notification">
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'notification_subscribe_form',
            'enableAjaxValidation'=>false,
    )); ?>

    <?php foreach ($this->module->runControllerMethod('notifications/subscription','prepareNotifications') as $key => $config):?>
        <div class="row notification">
            <h4><?php echo $config['title'];?></h4>
            <p><?php echo $config['subtitle'];?></p>
            <?php 
                foreach ($config['channels'] as $channel){
                    $encodedKey = Notification::encodeKey($key,$channel);
                    echo CHtml::openTag('span');
                    echo CHtml::checkBox($encodedKey, $config['subscription'][$encodedKey], ['uncheckValue'=>0,'style'=>'height:auto']);
                    echo CHtml::openTag('span',['class'=>'channel']);
                    echo ' '.Notification::siiName()[$channel];
                    echo CHtml::closeTag('span');
                    echo CHtml::closeTag('span');
                }
            ?>
        </div>
    <?php endforeach;?>
    
    <div class="row buttons" style="padding-top:20px">
        <?php $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'notificationbutton',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Save'),
                    'value'=>'btn1',
                    'onclick'=>'js:function(){submitform(\'notification_subscribe_form\');}',
                ]);
        ?>
    </div>

    <?php $this->endWidget(); ?>

</div><!-- form -->