<?php $this->beginWidget('CActiveForm', array('id'=>'task-form')); ?>
    <?php $this->renderPartial($this->searchView,array('dataProvider'=>$dataProvider,'searchModel'=>$searchModel)); ?>
<?php $this->endWidget(); ?>  