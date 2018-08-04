<div id="imgInstaller">

	<h1><?php echo Img::t('install','Error'); ?></h1>

	<p class="notice"><?php echo Img::t('install','An error occurred while installing the Image module.'); ?></p>

	<p><?php echo Img::t('install','Please try again or consult the documentation.') ;?></p>

	<p class="last"><?php echo CHtml::link(Img::t('install','Continue &raquo;'),Yii::app()->homeUrl); ?></p>

</div>