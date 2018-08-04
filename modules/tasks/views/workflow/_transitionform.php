<div id="transition" class="transition-form-container">
    
    <span class="button-wrapper">
        <?php $workflow = $model->getWorkflow(); ?> 
        <?php if ($workflow->hasDecision()) {
                foreach ($workflow->getDecision() as $key=>$value) {
                    $this->widget('zii.widgets.jui.CJuiButton',
                        array(
                            'id'=>$value.'-button',
                            'name'=>$value,
                            'buttonType'=>'button',
                            'caption'=>Process::getDecisionText(ucfirst($value)),
                            'value'=>$value,
                            'htmlOptions'=>array(
                                'class'=>'transition-button decision button-'.($key+1).
                                          ((isset($transition->decision)&&$transition->decision!=$value)||!$model->decisionable(user()->currentRole,$value)?' hidden ':''),
                                'data-action'=>$workflow->action,
                                'data-decision'=>$value,
                                'data-message'=>$model->getPromptMessage($value),
                                'transition-disabled'=>!$model->decisionable(user()->currentRole,$value)?true:false,
                                'style'=>'background:'.Process::getColor($model->getNextStatus($workflow->action,$value)).';',
                            ),
                            'options'=>array(
                                'disabled'=> !$model->decisionable(user()->currentRole,$value)?true:false
                            ),
                    )); 
                }
            }
            else {
                $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'id'=>'transition-button',
                        'name'=>'transition',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Confirm {action}',array('{action}'=>Process::getActionText($workflow->action))),
                        'value'=>'transition',
                        'htmlOptions'=>array(
                            'data-action'=>$workflow->action,
                            'style'=>'background:'.Process::getColor($model->getNextStatus($workflow->action)).';',
                            'class'=>'transition-button',
                        )
                    )
                ); 
            }
        ?>
    </span>

    <div class="form">
        <?php  $this->beginWidget('CActiveForm', array(
                'id'=>'transition-form',
                'action'=>$model->getTaskUrl($workflow->parseAction()),
                'enableAjaxValidation'=>false,
        )); ?>

        <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>

        <?php echo CHtml::activeHiddenField($transition,'obj_id'); ?>
        <?php echo CHtml::activeHiddenField($transition,'action'); ?>

        <?php //echo CHtml::errorSummary($transition,'','',array('style'=>'width:70%')); ?>

        <div class="row">
            <span style="font-size: 1.1em">
                <?php echo CHtml::label($model->getCondition1Label($transition->decision), false , array('required'=>$model->getCondition1Required($transition->decision))); ?>
            </span>
            <?php   if (is_array($model->getCondition1Placeholder($transition->decision))) {
                        echo CHtml::activeDropDownList($transition,'condition1', 
                                        $model->getCondition1Placeholder($transition->decision), 
                                        array('prompt'=>'',
                                              'class'=>'chzn-select-condition1',
                                              'data-placeholder'=>Sii::t('sii','Select One'),
                                              'style'=>'width:315px;'));
                        cs()->registerScript('chosen-condition1','$(\'.chzn-select-condition1\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);
                    }
                    else {
                        echo CHtml::activeTextArea($transition,'condition1',
                                array('rows'=>2,'cols'=>42,'style'=>'resize:none','placeholder'=>$model->getCondition1Placeholder($transition->decision)));
                    } 
            ?>
            <?php //echo $form->error($transition,'condition1'); ?>
        </div>

        <div class="row">
            <span style="font-size: 1.1em">
                <?php echo CHtml::label($model->getCondition2Label($transition->decision), false , array('required'=>$model->getCondition2Required($transition->decision))); ?>
            </span>
            <?php   if (is_array($model->getCondition2Placeholder($transition->decision))) {
                        echo CHtml::activeDropDownList($transition,'condition2', 
                                        $model->getCondition2Placeholder($transition->decision), 
                                        array('prompt'=>'',
                                              'class'=>'chzn-select-condition2',
                                              'data-placeholder'=>Sii::t('sii','Select One'),
                                              'style'=>'width:315px;'));
                        cs()->registerScript('chosen-condition2','$(\'.chzn-select-condition2\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);
                    }
                    else {
                        echo CHtml::activeTextArea($transition,'condition2',
                            array('rows'=>2,'cols'=>42,'style'=>'resize:none','placeholder'=>$model->getCondition2Placeholder($transition->decision)));
                    } 
            ?>
            <?php //echo $form->error($transition,'condition2'); ?>
        </div>

        <?php $this->endWidget(); ?>

    </div><!-- form -->
    
</div>
