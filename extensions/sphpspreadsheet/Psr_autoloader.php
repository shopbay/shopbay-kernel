<?php
//This is custom made autoloader (obtained from stackoverflow.com)
//Not provided by PhpSpreadsheet team

$filepath = Yii::getPathOfAlias('common.extensions.sphpspreadsheet');

spl_autoload_register(function ($class_name) use ($filepath)  {
	$preg_match = preg_match('/^Psr\\\/', $class_name);

	if (1 === $preg_match) {
		require_once( $filepath. DIRECTORY_SEPARATOR. 'Psr.php');
	} else if (false === $preg_match) {
		assert(false, 'Error de preg_match().');
	}
} );