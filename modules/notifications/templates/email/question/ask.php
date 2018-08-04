<div style="border:1px solid #EDEDED;margin:10px 10px;padding:10px 10px;">
    <div style="width:100%;">
        <p>
            <?php echo Sii::t('sii','From: {avatar} {sender}',array('{avatar}'=>$model->questioner->getAvatar(Image::VERSION_XXSMALL,array('style'=>'vertical-align:middle;')),'{sender}'=>$model->questioner->getAlias())); ?> 
        </p>
        <p>
            <?php echo Sii::t('sii','Time: {datetime}',array('{datetime}'=>$model->formatDateTime($model->question_time,true))); ?> 
        </p>
    </div>
    <div style="width:100%;">
        <div class="element q">
            <div style="background-color: whitesmoke;margin: 10px 0;padding: 10px 10px;text-indent: 0;border-radius: 3px;">
                <span style="color: gainsboro;font-size: 1.5em;vertical-align: middle;"><?php echo Sii::t('sii','Q: ');?></span>
                <?php echo $model->question;?>
            </div>
        </div>
    </div>
    <div style="width:100%;">
        <a href="<?php echo Notification::getActionUrl($model);?>" style="display:block;color:white;text-align:center;width:auto;background:lightskyblue;font-size:3em;margin: 20px auto;padding: 5px 0px;text-decoration: none;">
            <?php echo Notification::getActionLabel($model);?>
        </a>
    </div>
    
    <?php $this->renderPartial('common.modules.notifications.templates.email.signature');?>
    
</div>

<?php 
$this->renderPartial('common.modules.notifications.templates.email.footer');
