<?php

class Cache_Memcache implements Cache
{
    private $_caching;
    private $_memcache;

    private $_config;
    private $_logger;

    public function __construct()
    {
        // get any and all memcache servers
        $this->_config = Zend_Registry::get('config');

        // get caching enabled flag
        $this->_caching = (bool) $this->_config->caching->enabled;

        // this class hard-coded for memcache
        if ($this->_caching && ! extension_loaded('memcache'))
        {
            throw new Exception("could not find memcache extension");
        }

        // grab logger for convenience
        $this->_logger = Zend_Registry::get('logger');

        $this->_memcache = new Memcache;
        $this->_connect();
    }

    private function _connect()
    {
        $i = $errs = 0;
        $server = "server$i";

        // connect to each server
        while (isset($this->_config->memcache->$server))
        {
            $host = $this->_config->memcache->$server;

            if (! $this->_memcache->addServer($host))
            {
                $this->_logger->warning("could not connect to memcache server $host");
                $errs++;
            }

            $server = "server" . ++$i;
        }

        // if all servers fail to connect, throw exception
        if ($errs == $i)
        {
            throw new Exception("could not connect to any memcache servers");
        }
    }

    public function get($key)
    {
        return $this->_memcache->get($key);
    }

    public function set($key, $value, $ttl)
    {
        $this->_memcache->set($key, $value, 0, $ttl);
    }

    public function delete($key)
    {
        $this->_memcache->delete($key);
    }
}

