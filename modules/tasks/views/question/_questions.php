<?php  $this->widget($this->getModule()->getClass('gridview'), array(
        'id'=>'question_grid',
        'dataProvider'=>$dataProvider,
        //'filter'=>$searchModel,
        'columns'=>array(
            array(
                'class'=>'CCheckBoxColumn',
                'id'=>'task-checkbox',
                'name'=>'id',
                'selectableRows'=>'2',
                'htmlOptions'=>array('style'=>'text-align:center;width:3%'),
                'visible'=>isset($checkboxInvisible)?false:true,
            ),
            //array(
            //    'header'=>false,
            //    'value'=>'$data->getReferenceImage(Image::VERSION_SMALL)',
            //    'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
            //    'type'=>'html',
            //),           
            array(
                'header'=>Sii::t('sii','To'),
                'value'=>'$data->getReferenceImage(Image::VERSION_SMALL).\'<span>\'.$data->getReference()->displayLanguageValue(\'name\',user()->getLocale()).\'</span>\'',
                'htmlOptions'=>array('style'=>'text-align:left;width:20%;','class'=>'reference'),
                'type'=>'html',
            ),           
            array(
                'name' =>'question',
                'htmlOptions'=>array('style'=>'text-align:center;'),
                'type'=>'html',
            ),           
            array(
                'name' =>'answer',
                'htmlOptions'=>array('style'=>'text-align:center;'),
                'type'=>'html',
                'visible'=>isset($checkboxInvisible)?false:true,
            ),
            //array(
            //    'name'=>'type',
            //    'value'=>'Helper::htmlColorText($data->getTypeLabel())',
            //    'htmlOptions'=>array('style'=>'text-align:center;width:5%'),
            //    'type'=>'raw',
            //    'filter'=>false,
            //),
            array(
                'name'=>'status',
                'value'=>'Helper::htmlColorText($data->getTypeLabel()).Helper::htmlColorText($data->getStatusText())',
                'htmlOptions'=>array('style'=>'text-align:center;width:5%','class'=>'tag'),
                'type'=>'raw',
                'filter'=>false,
            ),
            array(
                'class'=>'SButtonColumn',
                'buttons'=> SButtonColumn::getQuestionButtons(array(
                    'view'=>'$data->hasAnswer()',
                    'answer'=>'$data->answerable()',
                )),
                'template'=>'{view} {answer}',
                'htmlOptions' => array('style'=>'text-align:center;width:3%'),
            ),
        ),    
    )); 