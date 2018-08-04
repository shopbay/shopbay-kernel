<tr id="child_<?php echo $form->id;?>">
    <?php if (count($form->localeAttributes())>0)
              echo CHtml::tag('td',array(),$form->renderForm($this,!Helper::READONLY,array(),true));  
    ?>      
    <?php if ($form->hasNonLocaleAttributes())
              echo $form->renderNonLocaleAttributes(); 
    ?>   
    <td style="text-align:center">
        <?php echo $form->renderHiddenFields(); ?>
        <?php if ($form->showDeleteButton()):?>
        <span class="del-button" style="display:none">
            <?php echo CHtml::link('<i class="fa fa-remove"></i>','javascript:void(0)',array(
                            'title'=>Sii::t('sii','Remove {object}',array('{object}'=>$form->displayName())),
                            'onclick'=>$form->getDeleteOnclick(),
                        ));
            ?>
        </span>
        <?php endif;?>
    </td>         
</tr>