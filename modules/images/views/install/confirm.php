<div id="imgInstaller">

	<h1><?php echo Img::t('install','Reinstall'); ?></h1>

	<p class="notice"><?php echo Img::t('install','The Image module is already installed!'); ?></p>

	<p><?php echo Img::t('install','Do you wish to continue?'); ?></p>

	<p>
		<?php echo CHtml::link(Img::t('install', 'Yes'),array('install/index','confirm'=>1)); ?> /
		<?php echo CHtml::link(Img::t('install', 'No'),Yii::app()->homeUrl); ?>
	</p>

	<p class="warning last"><?php echo Img::t('install','WARNING: All your existing data will be lost.'); ?></p>

</div>