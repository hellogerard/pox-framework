<?php

// only define __autoload if it has not already been defined (phing may define 
// it in its build script
if (! function_exists('__autoload'))
{
    // by using PEAR-type naming conventions, autoload will always know where to 
    // find class definitions
    function __autoload($class)
    {
        require(str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
    }
}



define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

set_include_path('.'
    . PATH_SEPARATOR . APP_ROOT . '/application/lib'
    . PATH_SEPARATOR . APP_ROOT . '/application/models'
    . PATH_SEPARATOR . APP_ROOT . '/application/controllers'
    . PATH_SEPARATOR . APP_ROOT . '/application/tests/lib'
    . PATH_SEPARATOR . APP_ROOT . '/application/tests/models'
    . PATH_SEPARATOR . APP_ROOT . '/application/tests/controllers'
    . PATH_SEPARATOR . get_include_path()
    . PATH_SEPARATOR . APP_ROOT . '/thirdpartylibs'
);


// get a new config and save it in registry
$config = new Zend_Config_Ini(APP_ROOT . '/config/config.ini', 'development');
Zend_Registry::set('config', $config);

// use a null logger for unit tests
$logger = new Mock_Logger();
Zend_Registry::set('logger', $logger);

// use a mock database for unit tests
$db = new Mock_Database();
Zend_Registry::set('db', $db);

