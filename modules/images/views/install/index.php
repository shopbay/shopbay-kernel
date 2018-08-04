<div id="imgInstaller">

	<h1><?php echo Img::t('install','Install'); ?></h1>

	<p class="notice"><?php echo Img::t('install','This will install the Image module.'); ?></p>

	<p><?php echo Img::t('install','Do you wish to continue?'); ?></p>

	<p class="last">
		<?php echo CHtml::link(Img::t('install', 'Yes'),array('index','confirm'=>1)); ?> /
		<?php echo CHtml::link(Img::t('install', 'No'),Yii::app()->homeUrl); ?>
	</p>

</div>