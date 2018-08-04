<div class="grid-view" style="padding: 15px 0px;font-size: 0.9em;">
    <?php 
    
    $this->renderPartial('common.modules.notifications.templates.email.item._item_header',array('model'=>$model));

    $this->renderPartial('common.modules.notifications.templates.email.item._item_body',array('model'=>$model,'itemUrl'=>$itemUrl,'orderUrl'=>$orderUrl));

    $this->renderPartial('common.modules.notifications.templates.email.item._item_footer',array('model'=>$model)); 
    
    ?>
</div>
