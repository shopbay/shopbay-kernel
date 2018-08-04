<div>
    <p style="font-size:1em;padding:5px 20px 0px;">
    <?php echo Sii::t('sii','Please take note of our Refund Policy:');?>
    </p>
    <div style="margin:20px;padding:5px;background: whitesmoke">
    <?php 
        $md = new CMarkdown();
        echo $md->transform(Helper::purify($model->getRefundPolicy()));
    ?>
    </div>
</div>  

<?php $this->renderPartial('common.modules.notifications.templates.email.shop._contact',[
        'model'=>$model,
    ]); 
