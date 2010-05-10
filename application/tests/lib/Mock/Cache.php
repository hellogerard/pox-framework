<?php


class Mock_Cache
{
    private $_data;

    public function __construct()
    {
        $this->_data = array();
    }

    public function get($key)
    {
        $value = null;
        if (isset($this->_data[$key]))
        {
            $value = $this->_data[$key];
        }

        return $value;
    }

    public function set($key, $value, $ttl)
    {
        $this->_data[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->_data[$key]);
    }
}

