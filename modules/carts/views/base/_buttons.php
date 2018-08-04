<?php 
$scripts = '';
foreach ($buttons as $button) {
    $this->widget('zii.widgets.jui.CJuiButton',
        array(
            'name'=>$button['name'],
            'buttonType'=>'button',
            'caption'=>$button['caption'],
            'value'=>'btn1',
            //'onclick'=>'js:function(){'.$button['onclickJS'].'}',
            'options'=>array('disabled'=>$button['disabled']),
            'htmlOptions'=>$button['htmlOptions'],
        )
    );
    $scripts .= '$(\'#'.$button['name'].'\').button().click(function(){'.$button['onclickJS'].';});';
}
?>
<script type="text/javascript">
<?php echo $scripts; ?>
</script>


  