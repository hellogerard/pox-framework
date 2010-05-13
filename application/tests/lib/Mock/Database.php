<?php

/**
 * SQLite database implementation. see:
 * - http://php.net/sqlite
 * - http://devzone.zend.com/node/view/id/760
 */

class Mock_Database
{
    private $_conn;

    public function __construct()
    {
        // this class hard-coded for SQLite
        if (! extension_loaded('SQLite'))
        {
            throw new Exception("could not find SQLite extension");
        }

        $this->_conn = new SQLiteDatabase(":memory:");

        $this->_conn->queryExec('PRAGMA short_column_names = ON');
        $this->_conn->createFunction('lpad', array($this, 'lpad'), 3);
        $this->_conn->createFunction('now', array($this, 'now'));
        $this->_conn->createFunction('field', array($this, 'field'));
    }

    public function field()
    {
        $args = func_get_args();
        return join(',', $args);
    }

    public function lpad($value, $numchars, $char)
    {
        return str_pad($value, $numchars, $char, STR_PAD_LEFT);
    }

    public function now()
    {
        return date('Y-m-d H:i:s');
    }

    public function escape($value)
    {
        if (is_string($value))
        {
            $value = "'" . sqlite_escape_string($value) . "'";
        }
        else if (is_bool($value))
        {
            $value = $value ? '1' : '0';
        }
        else if (is_null($value))
        {
            $value = 'NULL';
        }

        return $value;
    }

    private function _prepare($sql, $bind)
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
                $sql .= $this->escape($param);
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
            else if ($i != sizeof($sqlpices))
            {
                throw new Exception("could not prepare statment due to parameter mismatch");
            }
        }

        return $sql;
    }

    /**
     * this method executes a read query i.e. a select query
     */

    private function _execute($sql, $bind = null)
    {
        if (! ($result = $this->_conn->query($this->_prepare($sql, $bind))))
        {
            throw new Exception("could not execute query: " . sqlite_error_string($this->_conn->lastError()));
        }

        return $result;
    }

    /**
     * returns the left-most column of a result set 
     */

    public function getCol($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        while ($result->valid())
        {
            $col[] = $result->fetchSingle();
        }

        return $col;
    }

    /**
     * returns the first cell of the first row
     */

    public function getOne($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        return $result->fetchSingle();
    }

    /**
     * returns all results in an array of associative arrays
     */

    public function getRows($sql, $bind = null)
    {
        $result = $this->_execute($sql, $bind);

        return $result->fetchAll(SQLITE_ASSOC);
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
     * translate the 'ON DUPLICATE KEY UPDATE' MySQL feature to SQLite
     */

    private function _onDuplicateKey($sql, $bind)
    {
        preg_match('/ on duplicate key update.*$/si', $sql, $matches);
        $clause = $matches[0];
        $numParams = substr_count($clause, '?');
        $bind = array_slice($bind, 0, -$numParams);

        $sql = str_replace($clause, '', $sql);
        $sql = preg_replace('/^insert /i', 'insert or replace ', $sql);

        return array($sql, $bind);
    }

    /**
     * translate the syntax for inserting multiple rows in one statement
     */

    private function _multiInsert($sql)
    {
        $sql = preg_replace('/\)\s*,\s*\(/i', ' union all select ', $sql);
        $sql = preg_replace('/values\s*\(/i', ' select ', $sql);
        $sql = preg_replace('/\)\s*$/i', '', $sql);

        return $sql;
    }

    /**
     * executes a DML statemtent i.e. insert, update, or delete
     */

    public function query($sql, $bind = null)
    {
        if (stripos($sql, "on duplicate key update") > 0)
        {
            list($sql, $bind) = $this->_onDuplicateKey($sql, $bind);
        }

        if (preg_match('/\)\s*,\s*\(/', $sql))
        {
            $sql = $this->_multiInsert($sql);
        }

        // execute the DML statement
        if (! $this->_conn->queryExec($this->_prepare($sql, $bind), $errorMsg))
        {
            throw new Exception("could not execute query: " . $errorMsg);
        }

        return true;
    }

    public function startTransaction()
    {
        $this->query('BEGIN');
    }

    public function commit()
    {
        $this->query('COMMIT');
    }

    public function rollback()
    {
        $this->query('ROLLBACK');
    }

    public function lastInsertId()
    {
        return $this->_conn->lastInsertRowid();
    }

    public function affectedRows()
    {
        if ($this->_conn === null)
        {
            throw new Exception("could not retrieve affected row count");
        }
    
        return $this->_conn->changes();
    }
}

