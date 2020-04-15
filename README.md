# phalcon-4x-mssql
Connecting to Microsoft SQL Server PDO driver With Phalcon Framework v4

Supports: 'ODBC' - 'DBLIB' - 'SQLSRV'

Recommended use SQLSRV

https://pecl.php.net/package-search.php?pkg_name=sqlsrv




# package dir
```dir
app
----common
--------library
------------phalcon-sqlsrv-master
----------------......
```

# Example
```php
<?php
$di->setShared('db', function () {
    $config = $this->getConfig();
    return new \Phalcon\Db\Adapter\Pdo\Sqlsrv([
        "host" => $config->database->host,
        'port' => $config->database->port,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname,
        "pdoType" => $config->database->pdoType,
        "dialectType" => $config->database->dialectType,
    ]);
});
?>

<?php
//loader
$loader->registerNamespaces([
    .......
    'Phalcon\Db'    => APP_PATH . '/common/library/phalcon-sqlsrv-master/Phalcon/Db/',
]);
?>
```
# config ini
```ini
[database]
adapter  = Sqlsrv
host     = localhost
port     = 1433
username = sa
password = your password
dbname   = database name
pdoType  = sqlsrv
dialectType  = Sqlsrv
```
# original author: jeijei4

https://github.com/jeijei4/Phalcon-pdo-mssql
