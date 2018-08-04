<div class="<?php echo $element->widgetCssClass;?>" style="display:none;">
    
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="widgetModalLabel"><?php echo $element->widgetName;?></h4>
    </div>
    
    <div class="modal-body">
        
        <form id="<?php echo $element->widgetFormId;?>" class="form-horizontal" <?php echo $element->widgetFormOptions;?>>
      
            <ul class="modal-tabs nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><?php echo Sii::t('sii','Settings');?></a></li>
                <li role="presentation"><a href="#css" aria-controls="css" role="tab" data-toggle="tab"><?php echo Sii::t('sii','CSS');?></a></li>
            </ul>

            <div class="modal-tab-content tab-content form-content">
                <div role="tabpanel" class="tab-pane active" id="settings">
                    <?php $this->renderPartial($element->widgetSettings,['element'=>$element]);?>       
                </div>
                <div role="tabpanel" class="tab-pane" id="css">
                    <?php $this->renderPartial($element->widgetCssSettings,['element'=>$element]);?>       
                </div>
            </div>        
            
            <input id="locked" name="locked" data-field="locked" type="hidden" class="form-field" value="<?php echo $element->locked;?>">
            <input id="editable" name="editable" data-field="editable" type="hidden" class="form-field" value="<?php echo $element->editable;?>">
            <input id="deletable" name="deletable" data-field="deletable" type="hidden" class="form-field" value="<?php echo $element->deletable;?>">
            <input id="moveable" name="moveable" data-field="moveable" type="hidden" class="form-field" value="<?php echo $element->moveable;?>">

        </form>        

    </div>
    
    <div class="modal-footer">
       
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Sii::t('sii','Close');?></button>
       
        <button type="button" class="btn btn-primary"><?php echo Sii::t('sii','Save');?></button>
       
    </div>
    
</div>
