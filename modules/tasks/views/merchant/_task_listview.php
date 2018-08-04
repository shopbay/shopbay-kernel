<?php
    $this->widget($this->getModule()->getClass('listview'), array(
                        'dataProvider'=>TaskBaseController::getTaskGroupDataProvider($role),
                        'template'=>'{items}',
                        'viewData'=>array('role'=>$role),
                        'itemView'=>$this->module->getView('tasks.taskgroup'),
                    ));
