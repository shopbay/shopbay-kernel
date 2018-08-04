<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    	Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName())),    
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => array(get_class($searchModel).'.success',get_class($searchModel).'.error'),
    'heading'=> array(
        'name'=> l($searchModel->getScenario()=='activate'?'<i class="fa fa-toggle-on" style="color:green"></i>':'<i class="fa fa-toggle-off"></i>',
                   'javascript:void(0);',
                   array('style'=>'vertical-align:bottom;cursor:pointer;',
                         'title'=> Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName())),
                         'onclick'=>'javascript:task()'))
                .' '.Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName(Helper::PLURAL))),
        //'image'=> CHtml::image($searchModel->getScenario()=='activate'?$this->getImage('play-48.png'):$this->getImage('stop-48.png'),
        //                        $this->modelType,
        //                        array('style'=>'vertical-align:bottom;cursor:pointer;',
        //                              'title'=> ucfirst($action),
        //                              'onclick'=>'javascript:task()')),
    ),
    'body'=>$this->renderPartial('common.modules.tasks.views.workflow._transition_body',array('dataProvider'=>$dataProvider,'searchModel'=>$searchModel),true),
));