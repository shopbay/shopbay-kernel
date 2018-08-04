<div>
    <p style="font-size:1.1em">
    <?php echo Sii::t('sii','Please take note of our Refund Policy:');?>
    </p>
    <div style="margin:20px;padding:5px;background: whitesmoke">
    <?php 
        $md = new CMarkdown();
        echo $md->transform(Helper::purify($model->getRefundPolicy()));
    ?>
    </div>
</div>

<?php $this->renderPartial('common.modules.notifications.templates.message.shop._contact',[
        'model'=>$model,
    ]); 

