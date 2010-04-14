<?php

/**
 * SQLite database implementation. see:
 * - http://php.net/sqlite
 * - http://devzone.zend.com/node/view/id/760
 */

class TestDatabase
{
    private $_conn;

    public function __construct()
    {
        // this class hard-coded for SQLite
        if (! extension_loaded('SQLite'))
        {
            throw new Database_Exception("could not find SQLite extension");
        }

        $this->_conn = new SQLiteDatabase(":memory:");

        $this->_conn->createFunction('lpad', array($this, 'lpad'), 3);
    }

    public function lpad($value, $numchars, $char)
    {
        return str_pad($value, $numchars, $char, STR_PAD_LEFT);
    }

    private function _escape($sql, $bind)
    {
        // emulates a prepared statement by substituting all question marks 
        // (?'s) with the given, escaped parameter
        // - based on ADOdb emulated prepared statements

        if (is_array($bind))
        {
            $sqlpieces = explode('?',$sql);
            $sql = '';
            $i = 0;

            foreach ($bind as $param)
            {
                $sql .= $sqlpieces[$i];

                if (is_string($param))
                {
                    $sql .= "'" . sqlite_escape_string($param) . "'";
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
            else if ($i != sizeof($sqlpices))	
            {
                throw new Database_Exception("could not prepare statment due to parameter mismatch");
            }
		}

        return $sql;
    }

    /**
     * this method executes a read query i.e. a select query
     */

    private function _execute($sql, $bind)
    {
        if (! ($result = $this->_conn->query($this->_escape($sql, $bind))))
        {
            throw new Database_Exception("could not execute query: " . sqlite_error_string($this->_conn->lastError()));
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

    public function getOne($sql, $bind)
    {
        $result = $this->_execute($sql, $bind);

        return $result->fetchSingle();
    }

    /**
     * returns all results in an array of associative arrays
     */

    public function getRows($sql, $bind)
    {
        $result = $this->_execute($sql, $bind);

        return $result->fetchAll(SQLITE_ASSOC);
    }

    /**
     * executes a DML statemtent i.e. insert, update, or delete
     */

    public function query($sql, $bind = null)
    {
        // execute the DML statement
        if (! $this->_conn->queryExec($this->_escape($sql, $bind), $errorMsg))
        {
            throw new Database_Exception("could not execute query: " . $errorMsg);
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
}

?>
