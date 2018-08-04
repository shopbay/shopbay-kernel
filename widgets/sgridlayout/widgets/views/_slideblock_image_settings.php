<tr class="template-download image slide-field-group <?php echo $cssClass;?>">
    <td width="90%" class="preview">
        <div class="slide-image-wrapper">
            <?php
                echo  CHtml::hiddenField('slideImage_'.$nextNum, isset($item['image'])?$item['image']:null, [
                        'data-field'=>'slideImage',
                        'data-field-type'=>'slideImage',
                        'class'=>'slide-field slide-image form-field '.$cssClass,//add template css class if any to mark as template
                    ]);
            ?>
            <img src="<?php echo $item['image'];?>">
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Caption');?></label>
            <div class="col-sm-9 slide-text-wrapper">
                <?php   
                    $element->controller->renderPartial($element->getWidgetViewFile('_language_form'),[
                        'inputId'=>'slideText_'.$nextNum,
                        'inputName'=>'slideText['.$nextNum.']',
                        'inputType'=>'textField',
                        'field'=>'slideText',
                        'fieldType'=>'language',
                        'fieldCssClass'=> 'slide-field '.$cssClass,
                        'element'=> $element,
                        'htmlOptions'=>[
                            'class'=>'form-control language-field-slideText',
                            'placeholder'=>Sii::t('sii','Enter text'),
                        ],
                        'value'=>$element->getSlideItemValue($item,'text'),
                    ]);
                ?>        
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Button');?></label>
            <div class="col-sm-9 slide-cta-wrapper">
                <?php   
                    $element->controller->renderPartial($element->getWidgetViewFile('_language_form'),[
                        'inputId'=>'slideCtaLabel_'.$nextNum,
                        'inputName'=>'slideCtaLabel['.$nextNum.']',
                        'inputType'=>'textField',
                        'field'=>'slideCtaLabel',
                        'fieldType'=>'language',
                        'fieldCssClass'=> 'slide-field '.$cssClass,
                        'element'=> $element,
                        'htmlOptions'=>[
                            'class'=>'form-control language-field-slideCtaLabel',
                            'placeholder'=>Sii::t('sii','Enter button label'),
                        ],
                        'value'=>$element->getSlideItemValue($item,'ctaLabel'),
                    ]);
                ?>        
                <input id="<?php echo "slideCtaUrl_$nextNum";?>" name="<?php echo "slideCtaUrl[$nextNum]";?>" data-field-name="slideCtaUrl" data-field="slideCtaUrl" data-field-type="link" type="text" class="slide-field slide-cta-url form-control form-field <?php echo $cssClass;?>" placeholder="<?php echo Sii::t('sii','Enter button link, e.g. https://yourshop/yourpage');?>" value="<?php echo isset($item['ctaUrl'])?$item['ctaUrl']:'#';?>">
            </div>
        </div>
    </td>
    <td width="10%" class="delete">
        <button type="button" class="btn btn-danger" data-image="slideImage_<?php echo $nextNum;?>">
            <i style="cursor:pointer" class="fa fa-times"></i>    
        </button>
    </td>         
</tr>
