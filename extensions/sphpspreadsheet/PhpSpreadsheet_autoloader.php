<?php
//This is custom made autoloader (obtained from stackoverflow.com)
//Not provided by PhpSpreadsheet team

$filepath = Yii::getPathOfAlias('common.vendors.phpspreadsheet.src.PhpSpreadsheet');

spl_autoload_register(function ($class_name) use ($filepath) {
	$preg_match = preg_match('/^PhpOffice\\\PhpSpreadsheet\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^PhpOffice\\/PhpSpreadsheet\\//', '', $class_name);
		require_once( $filepath. DIRECTORY_SEPARATOR . $class_name . '.php');
	}
});