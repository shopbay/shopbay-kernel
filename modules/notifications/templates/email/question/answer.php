<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">
    <div style="width:100%;">
        <p>
            <?php echo Sii::t('sii','From: {avatar} {sender}',['{avatar}'=>$model->getReferenceImage(Image::VERSION_XXSMALL),'{sender}'=>$model->getReferenceName()]); ?> 
        </p>
        <p>
            <?php echo Sii::t('sii','Time: {datetime}',array('{datetime}'=>$model->formatDateTime($model->answer_time,true))); ?> 
        </p>
    </div>
    <div style="width:100%;">
        <div class="element q">
            <div style="background-color: whitesmoke;margin: 10px 0;padding: 10px 10px;text-indent: 0;border-radius: 3px;">
                <span style="color: gainsboro;font-size: 1.5em;vertical-align: middle;"><?php echo Sii::t('sii','Q: ');?></span>
                <?php echo $model->question;?>
                <span style="float: right;color: darkgray;">
                    <?php echo $model->formatDateTime($model->question_time,true);?>
                </span>            
            </div>
        </div>
        <div class="element a">
            <div style="margin-left: 20px;padding: 10px 10px;background: whitesmoke;border-radius: 3px;">
                <span style="color: gainsboro;font-size: 1.5em;vertical-align: middle;"><?php echo Sii::t('sii','A: ');?></span>
                <?php echo $model->answer;?>
            </div>
        </div>
    </div>
</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.order._shop_footer',['shop'=>$model->shop]);