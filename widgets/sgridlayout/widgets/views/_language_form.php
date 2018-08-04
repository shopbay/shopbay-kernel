<input id="<?php echo isset($inputId)?$inputId:$field;?>" name="<?php echo isset($inputName)?$inputName:$field;?>" data-field="<?php echo $field;?>" data-field-type="<?php echo $fieldType;?>" type="hidden" class="form-control form-field language-field <?php echo isset($fieldCssClass)?$fieldCssClass:'';?>" value="<?php echo isset($value)?$element->serializeValue($value):$element->serializeValue($field);?>">

<ul class="language-tabs nav nav-tabs" role="tablist">
    <?php
        if (!isset($value))
            $value = isset($element->$field) ? $element->$field : [];

        $fieldId = isset($inputId)?$inputId:$field;//assign unique input field id
        $key = $element->type.'-'.$fieldId.'-';

        foreach ($element->getLocales() as $locale => $localeTitle) {
                
            if (!isset($value[$locale])){
                $value[$locale] = '';//Assign locale key
            }
            echo CHtml::openTag('li',['role'=>'presentation']);
            echo CHtml::link($localeTitle,'#'.$key.$locale,[
                'aria-controls'=>$key.$locale,
                'role'=>'tab',
                'data-toggle'=>'tab',
            ]);
            echo CHtml::closeTag('li');
        }     
    ?>
</ul>

<div class="language-tab-content tab-content form-content">
    <?php
        foreach ($element->getLocales() as $locale => $localeTitle) {
                
            if (!isset($value[$locale])){
                $value[$locale] = '';//Assign locale key
            }
            echo CHtml::openTag('div',[
                'id'=>$key.$locale,
                'role'=>'tabpanel',
                'class'=>'tab-pane',
            ]);
            echo CHtml::{$inputType}($fieldId.'['.$locale.']',$value[$locale],array_merge(['data-locale'=>$locale],$htmlOptions));
            echo CHtml::closeTag('div');
        }     
    ?>    
</div>     