<?php


/**
 * borrowed heavily from wordpress object cache
 *
 * This class implements a factory for creating objects. Currently, the factory 
 * is concerned mainly with utilization of an object cache.  In the future, the 
 * factory may handle other things, and/or the cache may be split out to allow 
 * for different types of cache, etc.  Currently, the cache is implmented as a 
 * global (machine-wide) shared-memory hash provided by eaccelerator.
 *
 * The class offers simple get(), set(), rm() functions. If an object is changed 
 * during a request, i.e. either set or removed, the object is marked as dirty. 
 * However, it is not persisted to the cache until the *end of the request*.  
 * This is to minimize cache operations.
 */


class ObjectFactory
{
    private $_caching;
    private $_ttl;

    private $_keys;
    private $_cache;
    private $_dirty;
    private $_removed;

    private $_logger;

    public function __construct()
    {
        $config = Zend_Registry::get('config');
        $this->_caching = (bool) $config->application->caching->enabled;
        $this->_ttl = (int) $config->application->caching->ttl;

        // this class hard-coded for eaccelerator
        if ($this->_caching && ! extension_loaded('eAccelerator'))
        {
            throw new Exception("could not find eAccelerator extension");
        }

        // grab logger for convenience
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * This method checks if an object exists in the cache. If not, it 
     * instantiates a new object, and marks it for saving to the cache
     */

    public function get()
    {
        $args = func_get_args();

        // at least one arg is required: the class name
        if (empty($args))
        {
            throw new Exception("could not find class definition");
        }

        // if one arg given, return a new, empty instance of this class
        else if (sizeof($args) == 1)
        {
            $class = $args[0];
            $this->_logger->debug("new instance of $class");
            return new $class;
        }

        // if more than one arg given, the remaining args is used as args for 
        // the constructor
        else if (sizeof($args) > 1)
        {
            $class = array_shift($args);
        }

        // keys for objects will be the class name concatenated with the 
        // serialized form of the constructor args.
        $key = $class . serialize($args);

        // see if the object has been instantiated in this request already
        if (isset($this->_cache[$key]))
        {
            $this->_logger->debug("$key found in request cache");
            return $this->_cache[$key];
        }

        // else if this object has been removed in this request, do not create 
        // it again
        else if (isset($this->_removed[$key]))
        {
            $this->_logger->debug("$key already deleted - returning null");
            return null;
        }


        // if caching is off, or if object not found in cache, instantiate a new 
        // object of the class
        if (! $this->_caching || ($object = eaccelerator_get($key)) === null)
        {
            // taken from
            // http://us2.php.net/manual/en/function.call-user-func-array.php#74427

            $this->_logger->debug("new instance of $key");
            $reflector = new ReflectionClass($class);
            $object = $reflector->newInstanceArgs($args); 

            // mark the object as dirty - it will be saved to the cache at 
            // request's end
            $this->_dirty[] = $object;
        }

        // else return the object from the cache
        else
        {
            $this->_logger->debug("$key found in shm cache");
            $object = unserialize($object);
        }

        // save the object in this individual request's "cache"
        $this->_cache[$key] = $object;

        // save the object key - we'll need it later
        $this->_keys[spl_object_hash($object)] = $key;

        return $object;
    }

    public function rm($object)
    {
        // get the object's key
        $key = $this->_keys[spl_object_hash($object)];

        unset($this->_cache[$key]);
        $this->_dirty[] = $object;
        $this->_removed[$key] = true;
        $this->_logger->debug("invalidating $key");
    }

    public function put($object)
    {
        // get the object's key
        $key = $this->_keys[spl_object_hash($object)];

        $this->_cache[$key] = $object;
        $this->_dirty[] = $object;
        $this->_logger->debug("$key modified and marked dirty");
    }

    private function _save()
    {
        if (! $this->_caching || empty($this->_dirty))
        {
            return;
        }

        $this->_logger->debug("persisting cache to shm");

        // do we need locking here?

        foreach ($this->_dirty as $object)
        {
            // get the object's key
            $key = $this->_keys[spl_object_hash($object)];

            if (isset($this->_cache[$key]))
            {
                // save the object to cache
                eaccelerator_put($key, serialize($object), $this->_ttl);
            }
            else
            {
                // delete from cache
                eaccelerator_rm($key);
            }
        }
    }

    public function __destruct()
    {
        $this->_save();
    }
}

