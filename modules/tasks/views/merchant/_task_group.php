<?php $dataProvider = TaskBaseController::getTaskDataProvider($role,$data,$this->modelFilter);?>
<?php if ($dataProvider->getTotalItemCount()>0):?>
<div class="task-group">
    <div class="name">
        <?php echo $this->getModelDisplayName($data,true);?>
    </div>
    <?php
        $this->widget($this->getModule()->getClass('listview'), array(
                'dataProvider'=>$dataProvider,
                'template'=>'{items}',
                'emptyText'=>'',
                'itemView'=>$this->module->getView('tasks.task'),
            ));
    ?>
</div>
<?php endif;?>