<?php

/*
 * This class attempts to avoid "dogpiling" effect. See:
 * http://www.socialtext.net/memcached/index.cgi?faq#how_to_prevent_clobbering_updates_stampeding_requests
 */

class Cache_Dogpile implements Cache
{
    private $_caching;
    private $_ttl;

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

        $this->_ttl = $config->caching->ttl;
    }

    public function get($key)
    {
        if (($value = eaccelerator_get($key)) !== null)
        {
            // split the expiration time and the value
            $time = strtok($value, ':');
            $value = str_replace("$time:", "", $value);

            // if value has expired
            if ($_SERVER['REQUEST_TIME'] > $time)
            {
                // set the value with a new expiration time so others won't
                // cache-miss and trigger a "dogpile"
                $expires = $_SERVER['REQUEST_TIME'] + $this->_ttl;
                eaccelerator_put($key, "$expires:$value", 3600); // 1 hour

                // meanwhile, return null so the caller thinks it's expired and
                // sets it again
                $value = null;
            }

            // else value is good
            $value = unserialize($value);
        }

        return $value;

    }

    public function set($key, $value, $ttl)
    {
        $expires = $_SERVER['REQUEST_TIME'] + $ttl;

        // prepend the application expiration time to the value and save
        // the value to cache. the "real" timeout is far in the future.
        eaccelerator_put($key, "$expires:$value", 3600); // 1 hour
    }

    public function delete($key)
    {
        eaccelerator_rm($key);
    }
}

