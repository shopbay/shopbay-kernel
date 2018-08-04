<p style="font-size:1.1em">
    <?php echo Sii::t('sii','We regret to inform you that your shop application dated {datetime} for shop {shop_name} is not successful.',array('{datetime}'=>$model->formatDatetime($model->create_time),'{shop_name}'=>$model->displayLanguageValue('name',$model->getLocale())));?>
</p>
<p>
    <a href="<?php echo $model->viewUrl;?>"><?php echo Sii::t('sii','View Shop');?></a>
</p>