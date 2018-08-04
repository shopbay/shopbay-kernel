<div class="sections">
    
    <?php foreach ($this->sections as $dataProvider): ?>
    
        <?php $section = (object)$dataProvider; ?>
    
        <div class="section-<?php echo $section->id;?>">

            <?php if (isset($section->heading)): ?>
            <div class="section-heading">
                
                <?php if (isset($section->top)&&$section->top): ?>
                <span class="section-button-all" name="section-all">
                    <?php if (isset($this->buttonMode) && $this->buttonMode=='image'): ?>
                        <?php echo CHtml::image(Yii::app()->controller->getImage('close.jpg',false));?>
                    <?php else: ?>
                        <a href='javascript:void(0);'><i class="fa fa-angle-double-up"></i></a>
                    <?php endif; ?>
                </span>
                <?php endif; ?>

                <h2>
                    <div class="section-button" name="section-<?php echo $section->id;?>">
                        <?php if (isset($this->buttonMode) && $this->buttonMode=='image'): ?>
                            <?php echo CHtml::image(Yii::app()->controller->getImage('close.jpg',false));?>
                        <?php else: ?>
                        <a href='javascript:void(0);'><i class="fa fa-angle-down"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php echo $section->name;?>
                </h2>

            </div>
            <?php endif; ?>
            
            <div class="section-body<?php echo isset($section->heading)?'':'-alone';?>">  
                <?php 
                    if (isset($section->widget)){
                        echo CHtml::openTag ('div', array('class'=>'widget'));
                        $this->widget($section->widget,$section->widgetData);
                        echo CHtml::closeTag('div');
                    }
                    else if (isset($section->html)){
                        if (isset($section->htmlOptions))
                            echo CHtml::openTag ('div', $section->htmlOptions);
                        else
                            echo CHtml::openTag ('div');
                        echo $section->html;
                        echo CHtml::closeTag('div');
                    }
                    else
                        echo Yii::app()->controller->renderPartial($section->viewFile,$section->viewData);
                ?>
            </div>
       </div>
     
    <?php endforeach;?>
    
    <span id="section-asseturl" style="display:none"><?php echo $this->getAssetsURL($this->pathAlias.'.images');?></span>
    
    <span id="section-buttonmode" style="display:none"><?php echo $this->buttonMode;?></span>
    
</div>
