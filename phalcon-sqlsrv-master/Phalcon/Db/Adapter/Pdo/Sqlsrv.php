<?php

namespace Phalcon\Db\Adapter\Pdo;

use Phalcon\Db\Column;
use Phalcon\Db\Result\PdoSqlsrv as ResultPdo;
use Phalcon\Db\Dialect\Sqlsrv as DialectSqlsrv;

/**
 * Phalcon\Db\Adapter\Pdo\Sqlsrv
 * Specific functions for the MsSQL database system
 * <code>
 * $config = array(
 * "host" => "192.168.0.11",
 * "dbname" => "blog",
 * "port" => 3306,
 * "username" => "sigma",
 * "password" => "secret"
 * );
 * $connection = new \Phalcon\Db\Adapter\Pdo\Sqlsrv($config);
 * </code>.
 *
 * @property \Phalcon\Db\Dialect\Sqlsrv $_dialect
 */
class Sqlsrv extends \Phalcon\Db\Adapter\Pdo\AbstractPdo implements \Phalcon\Db\Adapter\AdapterInterface
{
    protected $dns = [];

    /**
     * This method is automatically called in Phalcon\Db\Adapter\Pdo constructor.
     * Call it when you need to restore a database connection.
     *
     * @param array $descriptor
     *
     * @return bool
     */
    public function connect(array $descriptor = null): bool
    {
        if (is_null($descriptor) === true) {
            $descriptor = $this->descriptor;
        }

        /*
         * Check if the developer has defined custom options or create one from scratch
         */
        if (isset($descriptor['options']) === true) {
            $options = $descriptor['options'];
            unset($descriptor['options']);
        } else {
            $options = array();
        }

        $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        $options[\PDO::ATTR_STRINGIFY_FETCHES] = true;

        if (isset($descriptor['pdoType'])) {
            $this->type = $descriptor['pdoType'];
        } else $this->type = 'sqlsrv';

        $this->dns = $descriptor;
        $this->pdo = new \PDO("{$this->type}:server={$descriptor['host']};database={$descriptor['dbname']}", $descriptor['username'], $descriptor['password'], $options);
//        $this->execute('SET QUOTED_IDENTIFIER ON');
//        $this->execute("SET ANSI_WARNINGS ON ");
//        $this->execute("SET ANSI_NULLS ON ");
//        $this->execute("SET NOCOUNT ON ");
//        $this->execute("SET XACT_ABORT ON ");

        /*
         * Set dialect class
         */
        $this->dialectType = $descriptor['dialectType'];
        if (isset($descriptor['dialectClass']) === false) {
            $dialectClass = 'Phalcon\Db\Dialect\\' . $this->dialectType;
        } else {
            $dialectClass = $descriptor['dialectClass'];
        }
        /*
         * Create the instance only if the dialect is a string
         */
        if (is_string($dialectClass) === true) {
            $this->dialect = new DialectSqlsrv();
        }
        return true;
    }

    public function getDsnDefaults(): array
    {
        return $this->dns;
    }

    /**
     * Returns an array of Phalcon\Db\Column objects describing a table
     * <code>
     * print_r($connection->describeColumns("posts"));
     * </code>.
     *
     * @param string $table
     * @param string $schema
     *
     * @return \Phalcon\Db\Column
     */
    public function describeColumns(string $table, string $schema = null): array
    {
        $oldColumn = null;

        /*
         * Get primary keys
         */
        $primaryKeys = array();
        foreach ($this->fetchAll($this->dialect->getPrimaryKey($table, $schema)) as $field) {
            $primaryKeys[$field['COLUMN_NAME']] = true;
        }

        /*
         * Get the SQL to describe a table
         * We're using FETCH_NUM to fetch the columns
         * Get the describe
         * Field Indexes: 0:name, 1:type, 2:not null, 3:key, 4:default, 5:extra
         */
        foreach ($this->fetchAll($this->dialect->describeColumns($table, $schema)) as $field) {
            /*
             * By default the bind types is two
             */
            $definition = array('bindType' => Column::BIND_PARAM_STR);

            /*
             * By checking every column type we convert it to a Phalcon\Db\Column
             */
            $autoIncrement = false;
            $columnType = $field['TYPE_NAME'];
            switch ($columnType) {
                /*
                 * Smallint/Bigint/Integers/Int are int
                 */
                case 'int identity':
                case 'tinyint identity':
                case 'smallint identity':
                    $definition['type'] = Column::TYPE_INTEGER;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_INT;
                    $autoIncrement = true;
                    break;
                case 'bigint' :
                    $definition['type'] = Column::TYPE_BIGINTEGER;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_INT;
                    break;
                case 'decimal':
                case 'money':
                case 'smallmoney':
                    $definition['type'] = Column::TYPE_DECIMAL;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;
                case 'int':
                case 'tinyint':
                case 'smallint':
                    $definition['type'] = Column::TYPE_INTEGER;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_INT;
                    break;
                case 'numeric':
                    $definition['type'] = Column::TYPE_DOUBLE;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;
                case 'float':
                    $definition['type'] = Column::TYPE_FLOAT;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;

                /*
                 * Boolean
                 */
                case 'bit':
                    $definition['type'] = Column::TYPE_BOOLEAN;
                    $definition['bindType'] = Column::BIND_PARAM_BOOL;
                    break;

                /*
                 * Date are dates
                 */
                case 'date':
                    $definition['type'] = Column::TYPE_DATE;
                    break;

                /*
                 * Special type for datetime
                 */
                case 'datetime':
                case 'datetime2':
                case 'smalldatetime':
                    $definition['type'] = Column::TYPE_DATETIME;
                    break;

                /*
                 * Timestamp are dates
                 */
                case 'timestamp':
                    $definition['type'] = Column::TYPE_TIMESTAMP;
                    break;

                /*
                 * Chars are chars
                 */
                case 'char':
                case 'nchar':
                    $definition['type'] = Column::TYPE_CHAR;
                    break;

                case 'varchar':
                case 'nvarchar':
                    $definition['type'] = Column::TYPE_VARCHAR;
                    break;

                /*
                 * Text are varchars
                 */
                case 'text':
                case 'ntext':
                    $definition['type'] = Column::TYPE_TEXT;
                    break;

                /*
                 * blob type
                 */
                case 'varbinary':
                    $definition['type'] = Column::TYPE_BLOB;
                    break;

                /*
                 * By default is string
                 */
                default:
                    $definition['type'] = Column::TYPE_VARCHAR;
                    break;
            }

            /*
             * If the column type has a parentheses we try to get the column size from it
             */
            $definition['size'] = (int)$field['LENGTH'];
            $definition['precision'] = (int)$field['PRECISION'];

            if ($field['SCALE'] || $field['SCALE'] == '0') {
                //                $definition["scale"] = (int) $field['SCALE'];
                $definition['size'] = $definition['precision'];
            }

            /*
             * Positions
             */
            if (!$oldColumn) {
                $definition['first'] = true;
            } else {
                $definition['after'] = $oldColumn;
            }

            /*
             * Check if the field is primary key
             */
            if (isset($primaryKeys[$field['COLUMN_NAME']])) {
                $definition['primary'] = true;
            }

            /*
             * Check if the column allows null values
             */
            if ($field['NULLABLE'] == 0) {
                $definition['notNull'] = true;
            }

            /*
             * Check if the column is auto increment
             */
            if ($autoIncrement) {
                $definition['autoIncrement'] = true;
            }

            /*
             * Check if the column is default values
             */
            if ($field['COLUMN_DEF'] != null) {
                $definition['default'] = $field['COLUMN_DEF'];
            }

            $columnName = $field['COLUMN_NAME'];
            $columns[] = new Column($columnName, $definition);
            $oldColumn = $columnName;
        }

        return $columns;
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server is returning rows
     * <code>
     * //Querying data
     * $resultset = $connection->query("SELECTFROM robots WHERE type='mechanical'");
     * $resultset = $connection->query("SELECTFROM robots WHERE type=?", array("mechanical"));
     * </code>.
     *
     * @param string $sqlStatement
     * @param mixed $bindParams
     * @param mixed $bindTypes
     *
     * @return bool|\Phalcon\Db\ResultInterface
     */
    public function query(string $sqlStatement, $bindParams = null, $bindTypes = null)
    {
        echo $sqlStatement,"<br>";
        $eventsManager = $this->eventsManager;

        /*
         * Execute the beforeQuery event if a EventsManager is available
         */
        if (is_object($eventsManager)) {
            $this->sqlStatement = $sqlStatement;
            $this->sqlVariables = $bindParams;
            $this->sqlBindTypes = $bindTypes;

            if ($eventsManager->fire('db:beforeQuery', $this, $bindParams) === false) {
                return false;
            }
        }

        $pdo = $this->pdo;

        $cursor = \PDO::CURSOR_SCROLL;
        if (strpos($sqlStatement, 'exec') !== false) {
            $cursor = \PDO::CURSOR_FWDONLY;
        }

        if (is_array($bindParams)) {
            $statement = $pdo->prepare($sqlStatement, array(\PDO::ATTR_CURSOR => $cursor));
            if (is_object($statement)) {
                $statement = $this->executePrepared($statement, $bindParams, $bindTypes);
            }
        } else {
            $statement = $pdo->prepare($sqlStatement, array(\PDO::ATTR_CURSOR => $cursor));
            $statement->execute();
        }

        /*
         * Execute the afterQuery event if a EventsManager is available
         */
        if (is_object($statement)) {
            if (is_object($eventsManager)) {
                $eventsManager->fire('db:afterQuery', $this, $bindParams);
            }

            return new ResultPdo($this, $statement, $sqlStatement, $bindParams, $bindTypes);
        }

        return $statement;
    }

    public function tableExists(string $tableName, string $schemaName = null): bool
    {
        return $this->execute("select * from dbo.sysobjects where id = object_id(N'[dbo].[$tableName]')");
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server doesn't return any rows
     * <code>
     * //Inserting data
     * $success = $connection->execute("INSERT INTO robots VALUES (1, 'Astro Boy')");
     * $success = $connection->execute("INSERT INTO robots VALUES (?, ?)", array(1, 'Astro Boy'));
     * </code>.
     *
     * @param string $sqlStatement
     * @param mixed $bindParams
     * @param mixed $bindTypes
     *
     * @return bool
     */
//    public function execute($sqlStatement, $bindParams = null, $bindTypes = null)
//    {
//        $eventsManager = $this->eventsManager;
//
//        /*
//         * Execute the beforeQuery event if a EventsManager is available
//         */
//        if (is_object($eventsManager)) {
//            $this->sqlStatement = $sqlStatement;
//            $this->sqlVariables = $bindParams;
//            $this->sqlBindTypes = $bindTypes;
//
//            if ($eventsManager->fire('db:beforeQuery', $this, $bindParams) === false) {
//                return false;
//            }
//        }
//
//        /*
//         * Initialize affectedRows to 0
//         */
//        $affectedRows = 0;
//
//        $pdo = $this->pdo;
//
//        $cursor = \PDO::CURSOR_SCROLL;
//        if (strpos($sqlStatement, 'exec') !== false) {
//            $cursor = \PDO::CURSOR_FWDONLY;
//        }
//
//        if (is_array($bindParams)) {
//            $statement = $pdo->prepare($sqlStatement, array(\PDO::ATTR_CURSOR => $cursor));
//            if (is_object($statement)) {
//                $newStatement = $this->executePrepared($statement, $bindParams, $bindTypes);
//                $affectedRows = $newStatement->rowCount();
//            }
//        } else {
////            $statement = $pdo->prepare($sqlStatement, array(\PDO::ATTR_CURSOR => $cursor));
////            $statement->execute();
//            $affectedRows = $pdo->exec($sqlStatement);
//        }
//
//        /*
//         * Execute the afterQuery event if an EventsManager is available
//         */
//        if (is_int($affectedRows)) {
//            $this->affectedRows = affectedRows;
//            if (is_object($eventsManager)) {
//                $eventsManager->fire('db:afterQuery', $this, $bindParams);
//            }
//        }
//
//        return true;
//    }
}
