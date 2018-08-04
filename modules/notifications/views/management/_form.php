<div class="subform">
    <div class="row">
        <?php echo CHtml::dropDownList('email_template','', array_merge(array(''=>Sii::t('sii','Select Email Template')),Notification::getEmailTemplates()),array('style'=>'margin-left:10px;'));?>
    </div>
    <div class="row" style="margin-top:20px;">
        <?php echo CHtml::dropDownList('message_template','', array_merge(array(''=>Sii::t('sii','Select Message Template')),Notification::getMessageTemplates()),array('style'=>'margin-left:10px;'));?>
    </div>
</div>
<script>
$(document).ready(function(){
    $('#email_template').change(function() {
      if ($('#email_template').val().length > 0){
            window.location.href = '<?php echo url('notifications/management/template/key');?>/'+$('#email_template').val();
      }
    });    
    $('#message_template').change(function() {
      if ($('#message_template').val().length > 0){
            window.location.href = '<?php echo url('notifications/management/template/key');?>/'+$('#message_template').val();
      }
    });    
});
</script>
