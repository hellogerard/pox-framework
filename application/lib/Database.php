<?php

// TODO needs tests

/*
 * This class based on the MySQLForge SQL connection class:
 * http://www.jpipes.com/index.php?/archives/99-MySQL-Connection-Management-in-PHP-How-Not-To-Do-Things.html
 */

class Database
{
    private $_conn;
    private $_config;
    private $_logger;

    public function __construct()
    {
        $this->_conn = null;

        // get any and all DB connection parameters
        $this->_config = Zend_Registry::get('config');

        // this class hard-coded for mysqli
        if (! extension_loaded('mysqli'))
        {
            throw new Database_Exception("could not find mysqli extension");
        }

        // use a logger helper for convenience
        $this->_logger = Zend_Registry::get('logger');
    }

    private function _getConnection()
    {
        $this->_conn = mysqli_init();

        $masters = array();
        $i = 0;
        $master = "master$i";

        // gather connection info for each configured master
        while (isset($this->_config->database->$master))
        {
            $masters[] = $this->_config->database->$master;
            $master = "master" . ++$i;
        }

        // randomly pick a master to connect to. if the first one fails, try the 
        // next one, and so on
        shuffle($masters);
        while (($master = array_shift($masters)) !== null)
        {
            try
            {
                if ($this->_conn->real_connect($master->host,
                                               $master->user,
                                               $master->password,
                                               $master->name))
                {
                    $this->_logger->debug("connecting to {$master->host}");
                    $this->_conn->query("set names utf8");
                    $this->_conn->query("set character_set utf8");
                    return;
                }
            }
            catch (PHPException $e)
            {
                $this->_logger->warning("could not connect to master database: " . $e->getMessage());
            }

            shuffle($masters);
        }

        // if all masters fail to connect, throw exception
        $this->_conn = null;
        throw new Database_Exception("could not connect to master database: " . $e->getMessage());
    }

    private function _escape($sql, $bind)
    {
        // emulates a prepared statement by substituting all question marks 
        // (?'s) with the given, escaped parameter
        // - based on ADOdb emulated prepared statements

        if (is_array($bind))
        {
            $sqlpieces = explode('?', $sql);
            $sql = '';
            $i = 0;

            foreach ($bind as $param)
            {
                $sql .= $sqlpieces[$i];

                if (is_string($param))
                {
                    $sql .= "'" . $this->_conn->real_escape_string($param) . "'";
                }
                else if (is_bool($param))
                {
                    $sql .= $param ? '1' : '0';
                }
                else if (is_null($param))
                {
                    $sql .= 'NULL';
                }
                else
                {
                    $sql .= $param;
                }

                $i += 1;
            }

            if (isset($sqlpieces[$i]))
            {
                $sql .= $sqlpieces[$i];

                if ($i + 1 != sizeof($sqlpieces))
                {
                    throw new Database_Exception("could not prepare statment due to parameter mismatch");
                }
            }
            else if ($i != sizeof($sqlpieces))    
            {
                throw new Database_Exception("could not prepare statment due to parameter mismatch");
            }
        }

        $this->_logger->debug("escaped sql: $sql");

        return $sql;
    }

    /**
     * this method executes a read query i.e. a select query
     */

    private function _execute($sql, $bind = null)
    {
        // if no DB connection yet, connect
        if ($this->_conn === null)
        {
            $this->_getConnection();
        }

        $this->_logger->debug("executing SQL: $sql");
        $this->_logger->debug("with bind:");
        $this->_logger->debug($bind);

        // execute the select statement
        if (! ($result = $this->_conn->query($this->_escape($sql, $bind))))
        {
            throw new Database_Exception("could not execute SQL: " . $this->_conn->error);
        }

        return $result;
    }

    /**
     * returns the left-most column of a result set 
     */

    public function getCol($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        while ($row = $result->fetch_row())
        {
            $col[] = reset($row);
        }

        return $col;
    }

    /**
     * returns the first cell of the first row
     */

    public function getOne($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        $row = $result->fetch_row();

        return $row[0];
    }

    /**
     * returns all results in an array of associative arrays
     */

    public function getRows($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        while ($row = $result->fetch_assoc())
        {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * executes a DML statemtent i.e. insert, update, or delete
     */

    public function query($sql, $bind = null)
    {
        if ($this->_conn === null)
        {
            $this->_getConnection();
        }

        $this->_logger->debug("executing DML: $sql");
        $this->_logger->debug("with bind:");
        $this->_logger->debug($bind);

        // execute the DML statement
        if (! $this->_conn->query($this->_escape($sql, $bind)))
        {
            throw new Database_Exception("could not execute DML: " . $this->_conn->error);
        }

        return true;
    }

    public function startTransaction()
    {
        if ($this->_conn === null)
        {
            $this->_getConnection();
        }

        $this->_conn->autocommit(false);
        $this->query('start transaction');
    }

    public function commit()
    {
        $this->_conn->commit();
        $this->_conn->autocommit(true);
    }

    public function rollback()
    {
        $this->_conn->rollback();
        $this->_conn->autocommit(true);
    }

    public function lastInsertId()
    {
        if ($this->_conn === null || ($ret = $this->_conn->insert_id) === 0)
        {
            throw new Database_Exception("could not retrieve last insert ID: " . $this->_conn->error);
        }

        return $ret;
    }
}

