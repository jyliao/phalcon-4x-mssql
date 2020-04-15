<?php

// Creates the autoloader
$loader = new \Phalcon\Loader();

$loader->registerDirs(
		array(
			'models/'
			)
		);

//Register some namespaces
$loader->registerNamespaces(
		array(
			"Phalcon\Db\Adapter\Pdo"    => "Phalcon/Db/Adapter/Pdo/",
			"Phalcon\Db\Dialect"    => "Phalcon/Db/Dialect/",
			"Phalcon\Db\Result"    => "Phalcon/Db/Result/"
			)
		);

// register autoloader
$loader->register();

