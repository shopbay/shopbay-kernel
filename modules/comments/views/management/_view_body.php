<?php if ($model->rating!=null):?>
<div style="float:right">
<?php $this->widget('CStarRating',
        array('name'=>'rating'.$model->id,
              'readOnly'=>true,
              'value'=>$model->rating)
    );
?>
</div>
<?php endif;?>

<div style="padding-top:<?php echo $model->rating!=null?'20px':'0px';?>">
    <blockquote>
        <i class="fa fa-quote-left"></i>
        <div>
            <?php echo Helper::purify($model->content); ?>
        </div>
        <i class="fa fa-quote-right"></i>
    </blockquote>
</div>