<p>
    <?php echo Sii::t('sii','Sender: {avatar} {sender}',['{avatar}'=>$model->questioner->getAvatar(Image::VERSION_XXSMALL),'{sender}'=>$model->questioner->getAlias()]); ?> 
</p>
<p>
    <?php echo Sii::t('sii','Sent Time: {datetime}',['{datetime}'=>$model->formatDateTime($model->question_time,true)]); ?> 
</p>

<p style="padding: 20px 0px;border-top: 1px solid white;">
    <?php echo Sii::t('sii','Q:').' '.$model->question;?>
</p>

<p>
    <a href="/questions/management/answer/id/<?php echo $model->id;?>"><?php echo Sii::t('sii','Answer Question');?></a>
</p>