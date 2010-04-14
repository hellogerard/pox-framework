<?php

/**
 * Let's add more comments.
 * This class authenticates a user using a username and password.
 */

class Auth implements Zend_Auth_Adapter_Interface
{
    private $_tableName;
    private $_identityColumn;
    private $_credentialColumn;

    private $_identity;
    private $_credential;

    public function __set($var, $value)
    {
        // let's use the magic method where possible to have as little code as 
        // possible
        $var = "_$var";
        $this->$var = $value;
    }

    public function authenticate()
    {
        $sql = 'SELECT '.$this->_identityColumn.'
                FROM '.$this->_tableName.'
                WHERE '.$this->_identityColumn.' = ? AND '.$this->_credentialColumn.' = ?';

        try
        {
            $bind = array($this->_identity, md5($this->_credential));
            // just use the global source
            $result = Zend_Registry::get('db')->getRows($sql, $bind);
        }
        catch (Exception $e)
        {
            throw new Zend_Auth_Adapter_Exception($e->getMessage());
        }

        $rows = count($result);

        if ($rows == 0)
        {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $message = 'Login failed.';
        }
        else if ($rows > 1)
        {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $message = 'Login failed.';
        }
        else
        {
            $code = Zend_Auth_Result::SUCCESS;
            $message = 'Login succesful.';
        }

        return new Zend_Auth_Result($code, $this->_identity, array($message));
    }
}
