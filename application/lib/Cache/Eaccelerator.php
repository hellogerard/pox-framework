<?php

class Cache_Eaccelerator implements Cache
{
    private $_caching;

    public function __construct()
    {
        // get caching enabled flag
        $config = Zend_Registry::get('config');
        $this->_caching = (bool) $config->caching->enabled;

        // this class hard-coded for eaccelerator
        if ($this->_caching && ! extension_loaded('eAccelerator'))
        {
            throw new Exception("could not find eAccelerator extension");
        }
    }

    public function get($key)
    {
        return eaccelerator_get($key);
    }

    public function set($key, $value, $ttl)
    {
        eaccelerator_put($key, $value, $ttl);
    }

    public function delete($key)
    {
        eaccelerator_rm($key);
    }
}

