<p>
    <?php echo Sii::t('sii','Sender: {avatar} {sender}',['{avatar}'=>$model->getReferenceImage(Image::VERSION_XXSMALL),'{sender}'=>$model->getReferenceName()]); ?> 
</p>
<p>
    <?php echo Sii::t('sii','Sent Time: {datetime}',['{datetime}'=>$model->formatDateTime($model->answer_time,true)]); ?> 
</p>

<p style="padding: 20px 0px;border-top: 1px solid white;">
    <?php echo Sii::t('sii','Q:').' '.$model->question;?>
</p>

<p style="padding: 20px 0px;border-top: 1px solid white;">
    <?php echo Sii::t('sii','A:').' '.$model->answer;?>
</p>
<p>
    <?php echo CHtml::link(Sii::t('sii','View Question'),$model->viewUrl);?>
</p>