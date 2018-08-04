<div class="list-box" style="position: relative">
    
    <div style="font-size: 1.1em;padding-bottom: 10px;">
        <i class="fa fa-envelope-o" style="padding-right: 5px"></i>
        <?php echo CHtml::link(CHtml::encode(Helper::rightTrim($data->getSubject(),isset($trimLength)?$trimLength:150)), $data->viewUrl); ?>
    </div>

    <div class="summary">
        <?php echo Sii::t('sii','Message sent {datetime}',array('{datetime}'=>strtolower(Helper::prettyDate($data->send_time)))); ?>
    </div>
        
</div>

