<?php echo CHtml::link(
                SPageMenu::menuItem($data['count'], $data['actionText'], Sii::t('sii','{action} {object}',array('{action}'=>$data['actionText'],'{object}'=>$data['object']))),
                $data['actionUrl'],
                array('style'=>'text-decoration:none')
            ); 