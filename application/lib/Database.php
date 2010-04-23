<?php

// TODO needs tests

/*
 * This class based on the MySQLForge SQL connection class:
 * http://www.jpipes.com/index.php?/archives/99-MySQL-Connection-Management-in-PHP-How-Not-To-Do-Things.html
 */

class Database
{
    private $_reader;
    private $_writer;
    private $_config;
    private $_logger;

    public function __construct()
    {
        $this->_reader = null;
        $this->_writer = null;

        // get any and all DB connection parameters
        $this->_config = Zend_Registry::get('config');

        // this class hard-coded for mysqli
        if (! extension_loaded('mysqli'))
        {
            throw new Exception("could not find mysqli extension");
        }

        // use a logger helper for convenience
        $this->_logger = Zend_Registry::get('logger');
    }

    private function _getReadConnection()
    {
        $this->_reader = mysqli_init();

        $slaves = array();
        $i = 0;
        $slave = "slave$i";

        // gather connection info for each configured slave
        while (isset($this->_config->database->$slave))
        {
            $slaves[] = $this->_config->database->$slave;
            $slave = "slave" . ++$i;
        }

        // randomly pick a slave to connect to. if the first one fails, try the 
        // next one, and so on
        shuffle($slaves);
        while (($slave = array_shift($slaves)) !== null)
        {
            try
            {
                if ($this->_reader->real_connect($slave->host,
                                                 $slave->user,
                                                 $slave->password,
                                                 $slave->name))
                {
                    $this->_logger->debug("connecting to {$slave->host}");
                    $this->_reader->query("set names utf8");
                    $this->_reader->query("set character_set utf8");
                    return;
                }
            }
            catch (PHPException $e)
            {
                $this->_logger->warning("could not connect to slave database: " . $e->getMessage());
            }

            shuffle($slaves);
        }

        // if all slaves fail to connect, try the master
        try
        {
            $this->_getWriteConnection();
        }
        catch (Exception $e)
        {
            $this->_reader = null;
            throw $e;
        }

        $this->_reader = $this->_writer;
    }

    private function _getWriteConnection()
    {
        $this->_writer = mysqli_init();

        // there should only be one "write" database
        $master = $this->_config->database->master;

        try
        {
            if ($this->_writer->real_connect($master->host,
                                             $master->user,
                                             $master->password,
                                             $master->name))
            {
                $this->_logger->debug("connecting to {$master->host}");
                $this->_writer->query("set names utf8");
                $this->_writer->query("set character_set utf8");
                return;
            }
        }
        catch (PHPException $e)
        {
            $this->_writer = null;
            throw new Exception("could not connect to master database: " . $e->getMessage());
        }
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
                    $conn = (is_null($this->_reader)) ? $this->_writer : $this->_reader;
                    $sql .= "'" . $conn->real_escape_string($param) . "'";
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
                    throw new Exception("could not prepare statment due to parameter mismatch");
                }
            }
            else if ($i != sizeof($sqlpieces))    
            {
                throw new Exception("could not prepare statment due to parameter mismatch");
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
        if ($this->_reader === null)
        {
            $this->_getReadConnection();
        }

        $this->_logger->debug("executing SQL: $sql");
        $this->_logger->debug("with bind:");
        $this->_logger->debug($bind);

        // execute the select statement
        if (! ($result = $this->_reader->query($this->_escape($sql, $bind))))
        {
            throw new Exception("could not execute SQL: " . $this->_reader->error);
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
     * returns the first result of a result set
     */

    public function getRow($sql, $bind = null)
    {
        $results = $this->getRows($sql, $bind);

        return (empty($results)) ? $results : $results[0];
    }

    /**
     * executes a DML statemtent i.e. insert, update, or delete
     */

    public function query($sql, $bind = null)
    {
        if ($this->_writer === null)
        {
            $this->_getWriteConnection();
        }

        $this->_logger->debug("executing DML: $sql");
        $this->_logger->debug("with bind:");
        $this->_logger->debug($bind);

        // execute the DML statement
        if (! $this->_writer->query($this->_escape($sql, $bind)))
        {
            throw new Exception("could not execute DML: " . $this->_writer->error);
        }

        return true;
    }

    public function startTransaction()
    {
        if ($this->_writer === null)
        {
            $this->_getWriteConnection();
        }

        $this->_writer->autocommit(false);
        $this->query('start transaction');
    }

    public function commit()
    {
        $this->_writer->commit();
        $this->_writer->autocommit(true);
    }

    public function rollback()
    {
        $this->_writer->rollback();
        $this->_writer->autocommit(true);
    }

    public function lastInsertId()
    {
        if ($this->_writer === null || ($ret = $this->_writer->insert_id) === 0)
        {
            throw new Exception("could not retrieve last insert ID: " . $this->_writer->error);
        }

        return $ret;
    }

    public function affectedRows()
    {
        if ($this->_writer === null)
        {
            throw new Exception("could not retrieve affected row count: " . $this->_writer->error);
        }
    
        return $this->_writer->affected_rows;
    }
}

