<?php

if (file_exists('unit-tests/config.db.local.php')) {
	$configMysql = array(
			'host' => 'localhost',
			'username' => 'root',
			'password' => 'ok123456',
			'dbname' => 'phalcon_test'
			);

	$configPostgresql = array(
			'host' => '127.0.0.1',
			'username' => 'postgres',
			'password' => '',
			'dbname' => 'phalcon_test',
			'schema' => 'public'
			);

	$configSqlite = array(
			'dbname' => '/tmp/phalcon_test.sqlite',
			);

	$configMssql = array(               
			'host'          => 'localhost',  
			'username'      => 'sa',
			'password'      => 'Jan0935!', 
			'dbname'        => 'phalcon_test',
			);                                                                                                                                           
}
else {
	require 'unit-tests/config.db.local.php';
}
