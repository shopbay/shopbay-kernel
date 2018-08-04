<?php echo l($model->switchAction(true)==LikeForm::ACTION_LIKE?LikeForm::getIcon():LikeForm::getIcon(LikeForm::ACTION_DISLIKE),
                'javascript:void(0);',
                array('title'=>$model->title,
                      'style'=>'float:right;cursor:pointer;',
                      'onclick'=>$model->getButtonScript()));
