<div id="<?php echo $this->id;?>" class="navmenu-widget <?php echo $this->menuCssClass;?>" <?php echo $this->getPikabuDataTags();?> >
    <?php if ($this->loadPikabu):?>
        <a class="m-pikabu-nav-toggle-icon" data-role="left"><i class="fa fa-bars"></i></a>
    <?php endif;?>
    <?php $this->widget('zii.widgets.CMenu', [
              'encodeLabel'=>false,
              'items'=>$this->menu,
              'htmlOptions'=>['class'=>$this->itemsCssClass.' '.Yii::app()->id],
          ]);
    ?>
</div>
<?php
//if ($this->loadPikabu){
//    $script = <<<EOJS
//$(document).ready(function(){
//    loadpikabu();
//});            
//EOJS;
//    Helper::registerJs($script);
//}    
//$this->widget('common.extensions.pikabu.Pikabu',array('config'=>array(
//    'viewportSelector'=>'.m-pikabu-viewport',//default .m-pikabu-viewport
//    'selectors'=>array(
//        'navToggles'=>'.m-pikabu-nav-toggle-icon',//Pikabu toggle button, default .m-pikabu-nav-toggle
//    ),
//    'onInit'=>new CJavaScriptExpression('function(){initnavmenuleft();}'),
//    'onOpened'=>new CJavaScriptExpression('function(){opennavmenuleft();;}'),
//    'onClosed'=>new CJavaScriptExpression('function(){closenavmenuleft();}'),
//)));
