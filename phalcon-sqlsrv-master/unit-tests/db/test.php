<?php

// Creates the autoloader
$loader = new \Phalcon\Loader();

$loader->registerDirs(
	array('models/')
);

//Register some namespaces
$loader->registerNamespaces(
		array(
			"Phalcon\Db\Adapter\Pdo"    => "../../mssql/adapter/",
			"Phalcon\Db\Dialect\Pdo"    => "../../mssql/dialect/"
			)
		);

// register autoloader
$loader->register();

echo '<h1>connect</h1>';
$mc = array(
		'server'		=> '127.0.0.1',
		'username'	=> 'sa',
		'password'	=> 'Jan0935!',
		'database'	=> 'phalcon_test',

	);
$ec = array(
		'host'		=> 'MSsql',
		'username'	=> 'apedtuser2',
		'password'	=> 'Ecg01dedt',
		'dbname'	=> 'CCCECST2',
		'dialectClass'	=> '\Twm\Db\Dialect\Mssql'	
	);
$db = new \Phalcon\Db\Adapter\Pdo\Mssql($mc); 
if (!$db->connect()){
	$db->close();
	die('connection failed');
}

//testModel($db);
testQueryBinding($db);

function testModel($db){
	$boutique = new Boutique();
}

function testQueryBinding($db){
	echo '<h1>execute query</h1>';
	$sqlStatement = "select * from tb_a_boutique_data where 1=':aaa' and 2=':bbb'";
	$bindParams = array(':aaa'=>'1',':bbb'=>'2');

	var_dump($db->query($sqlStatement, $bindParams));
}

function testDescribeColumns(){
	var_dump($db->describeColumns('tb_a_frist_data'));
}
