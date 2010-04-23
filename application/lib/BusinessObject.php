<?php

/**
 * The BusinessObject class is the parent of all logical business entities. 
 * These entities usually correspond to a row in a DB table and have a primary 
 * key. This class uses the PHP magic methods to handle the setting and getting 
 * of data. By using a single wrapper for these methods, we can:
 *
 * 1) ensure the object is handled in the cache properly (more on that below)
 * 2) do lazy-loading - data is never loaded until is actually needed 
 *    (instantiating the object doesn't do anything really)
 */

abstract class BusinessObject
{
    protected $_data;
    protected $_dataTypes;
    protected $_typeHint;

    // for a list of business objects, these control the paging
    public $pageNo = 1;

    // using PHP_INT_MAX with array_slice() appears to be broken on 64-bit 
    // machines. using null with array_slice() is broken in PHP 5.2.3. just set 
    // this to some arbitrary high number.
    public $pageSize = 1000;


    public function __construct()
    {
        // You cannot store a resource, such as a database connection, in a 
        // cached object.  Because while the cache will persist the reference 
        // beyond the page request, the database connection will close at the 
        // end of the request.  This means that when the object is retrieved 
        // from cache and tries to use the resource, it will be closed. The log 
        // file is also a resource.  Must get these variables from the registry 
        // every time.
    }

    // convenience methods
    protected function _logger()
    {
        return Zend_Registry::get('logger');
    }

    protected function _factory()
    {
        return Zend_Registry::get('factory');
    }

    protected function _db()
    {
        return Zend_Registry::get('db');
    }

    // this function creates an array of business objects with type of given 
    // class name constrained by the given page
    private function _collect($pks, $class)
    {
        $start = ($this->pageNo - 1) * $this->pageSize;
        $page = array_slice($pks, $start, $this->pageSize);
        $items = array();

        // check if the primary key is multiple values
        $multiplePKs = (is_array($page[0])) ? true : false;

        foreach ($page as $pk)
        {
            // if primary key is an array
            if ($multiplePKs)
            {
                // push the classname onto it for call_user_func_array()
                array_unshift($pk, $class);
            }
            else
            {
                // create an array for call_user_func_array()
                $pk = array($class, $pk);
            }

            $items[] = call_user_func_array(array($this->_factory(), 'get'), $pk);
        }

        $this->_logger()->debug("creating collection of " . count($items) . " items of type $class");

        return $items;
    }

    // this function stores the class/type of data we are returning.
    // the class/type is required for the _collect function.
    protected function _hint($type)
    {
        $this->_typeHint = $type;
    }

    // this invalidates this object in the cache, setting it up for removal
    protected function _invalidate()
    {
        $this->_factory()->rm($this);
    }

    // this sets up the object for persisting to the cache any changes that have 
    // occurred during this request
    protected function _save()
    {
        $this->_factory()->put($this);
    }

    // since data will be set to the database, we need ability to  "hydrate"
    // this object so the saved data will be complete, even if most of it has
    // not changed.
    public function hydrate()
    {
        if (! $this->_data)
        {
            $this->_data = $this->load();
        }
        else
        {
            $this->_data = array_merge($this->_data, $this->load());
        }
    }

    /*
     * These magic functions are called whenever the following syntax is 
     * executed:
     *
     * $x = $object->variable; // will call __get()
     *
     * or
     *
     * $object->variable = $x; // will call __set()
     *
     * if and only if "variable" does not exist as either a method or a member.
     *
     * Thus, when a client needs a class variable, we can execute a function to 
     * provide that variable, and data can be lazy-loaded on demand.
     *
     * See: http://us3.php.net/manual/en/language.oop5.magic.php
     */

    public function __isset($var)
    {
        return ($this->_data[$var] !== null);
    }

    public function __unset($var)
    {
        $this->_data[$var] = null;
    }

    public function __set($var, $value)
    {
        $func = "set" . TextUtilities::underscoreToCamelCase($var);

        if (method_exists($this, $func))
        {
            $this->$func($value);
        }
        else
        {
            $this->_data[$var] = $value;
        }

        // since data will be set to the database, we need to invalidate this
        // object so that it can be refreshed by the next client.
        $this->_invalidate();
    }

    public function __get($var)
    {
        // subclasses may implement this method which loads a minimum of data 
        // from the database
        if (! $this->_data && method_exists($this, "load"))
        {
            $this->_data = $this->load();

            // if data has been fetched from the DB, the updated object must be 
            // saved to the cache
            $this->_save();
        }

        $func = "get" . $var;

        // if the value has not been loaded yet, and a specialized load method 
        // exists, load it
        if (! isset($this->_data[$var]) && method_exists($this, $func))
        {
            $this->_data[$var] = $this->$func();

            // if a type has been set, save it
            if (isset($this->_typeHint))
            {
                $this->_dataTypes[$var] = $this->_typeHint;
                $this->_typeHint = null; // magic methods will be used on an
                                         // object member that is unset. work
                                         // around by setting to null
            }

            // if data has been fetched from the DB, the updated object must be 
            // saved to the cache
            $this->_save();
        }

        // now, data to be returned was either retrieved by this business object, 
        // or was already cached in this object. return it.

        // if data is a list of business objects, use the _collect function for
        // lazy instantiation and paging
        if (is_array($this->_data[$var]) && isset($this->_dataTypes[$var]))
        {
            return $this->_collect($this->_data[$var], $this->_dataTypes[$var]);
        }

        // else just return the value
        else
        {
            return $this->_data[$var];
        }
    }
}

