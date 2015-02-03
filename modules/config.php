<?php
	session_start();
	$cfg = array();

	if( $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.0.135' )
	{
		$cfg['DBHost']					= 'localhost';
		$cfg['DBUser']					= 'root';
		$cfg['DBPasswd']				= '';
		$cfg['DBName']					= 'mycanvas';
	}
	else
	{
		$cfg['DBHost']					= 'localhost';
		$cfg['DBUser']					= 'mycanvas';
		$cfg['DBPasswd']				= 'cnxRux5,zx4D';
		$cfg['DBName']					= 'mycanvas';
	}
?>
