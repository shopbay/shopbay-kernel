<?php     
echo CHtml::openTag('div', $this->htmlOptions);
    //render name
    echo CHtml::tag('div', array('class'=>'chart-name'),$this->name);
    
    //render filter options
    if ($this->hasFilterOptions())
        echo CHtml::tag('div', array('class'=>'chart-filter'),$this->getFilterBar());
    
    echo CHtml::openTag('div', array('class'=>'chart-canvas','id'=>$this->getCanvasId()));
        //render loader
        $this->widget('common.widgets.sloader.SLoader',array(
            'id'=>$this->getCanvasId().'_loader',
            'type'=>SLoader::ABSOLUTE,
            'display'=>'inline-block',
        ));
        //render chart (by javascript)
        Yii::app()->clientScript->registerScript($this->getCanvasId(),$this->getChartScript());
        
    echo CHtml::closeTag('div');//end chart-canvas

echo CHtml::closeTag('div');//end chart-container




